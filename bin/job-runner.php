#!/usr/bin/env php
<?php
/**
 * iPanel Job Runner — Arka Plan İş İşleyici
 *
 * Systemd tarafından yönetilen kalıcı daemon.
 * Kuyruktaki pending işleri sırayla çalıştırır.
 *
 * Çalışma şekli:
 *   - Her 3 saniyede bir pending işleri kontrol eder
 *   - Bir anda en fazla MAX_CONCURRENT iş çalıştırır
 *   - İş başarısız olursa MAX_ATTEMPTS kez yeniden dener (exponential backoff)
 *   - Her işi AgentClient üzerinden agent'a iletir
 */

declare(strict_types=1);

if (posix_geteuid() !== 0) {
    fwrite(STDERR, "job-runner: root olarak çalışmalı.\n");
    exit(1);
}

define('IPANEL_ROOT', dirname(__DIR__));
define('RUNNER_LOG',  '/var/log/ipanel/job-runner.log');
define('POLL_INTERVAL', 3);      // saniye
define('MAX_CONCURRENT', 3);     // eş zamanlı maksimum iş

// ZN Framework'ü bootstrap etmeden doğrudan DB bağlantısı
// (runner web stack'e bağlı değil)
require IPANEL_ROOT . '/web/Packages/autoload.php';

// ZN'yi minimal modda başlat
ZN\ZN::defines([
    'BUTCHERY_DIR'    => '',
    'CONTROLLERS_DIR' => 'iApp/Controllers/',
    'MODELS_DIR'      => 'iApp/Models/',
    'VIEWS_DIR'       => 'Views/',
    'ROUTES_DIR'      => 'iApp/Routes/',
    'CONFIG_DIR'      => 'iApp/Config/',
    'DATABASES_DIR'   => '',
    'STORAGE_DIR'     => 'iApp/Storage/',
    'COMMANDS_DIR'    => 'iApp/Commands/',
    'LANGUAGES_DIR'   => 'iApp/Languages/',
    'LIBRARIES_DIR'   => 'iApp/Libraries/',
    'AUTOLOAD_DIR'    => '',
    'FILES_DIR'       => 'iApp/Storage/Files/',
    'TEMPLATES_DIR'   => 'iApp/Templates/',
    'THEMES_DIR'      => 'iApp/Themes/',
    'PLUGINS_DIR'     => 'iApp/Plugins/',
    'UPLOADS_DIR'     => 'uploads/',
], IPANEL_ROOT . '/web');

// ────────────────────────────────────────────────
// Yardımcı fonksiyonlar
// ────────────────────────────────────────────────

function rlog(string $msg): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents(RUNNER_LOG, $line, FILE_APPEND);
    // Stdout'a da yaz (journald yakalar)
    echo $line;
}

function dbConn(): \PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dbenv = parse_ini_file('/etc/ipanel/db.env') ?: [];
        $pass  = $dbenv['DB_PASS'] ?? '';
        $pdo   = new \PDO('mysql:host=localhost;dbname=ipanel;charset=utf8', 'ipanel', $pass, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        ]);
    }
    return $pdo;
}

function claimJob(\PDO $db): ?object
{
    // MariaDB 10.5 compatible atomic claim — no SKIP LOCKED needed
    // Step 1: find the oldest pending job
    $stmt = $db->query(
        "SELECT id FROM jobs WHERE status='pending' AND attempts < max_attempts
          ORDER BY priority ASC, created_at ASC LIMIT 1"
    );
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    // Step 2: claim it by id+status check (safe against concurrent workers)
    $upd = $db->prepare(
        "UPDATE jobs SET status='running', started_at=NOW(), attempts=attempts+1
          WHERE id=? AND status='pending'"
    );
    $upd->execute([$row->id]);

    if ($upd->rowCount() === 0) {
        return null; // Another worker got it first
    }

    return $db->query("SELECT * FROM jobs WHERE id=" . (int)$row->id)->fetch() ?: null;
}

function markDone(\PDO $db, int $id, bool $ok, array $result): void
{
    $status = $ok ? 'completed' : 'failed';
    $db->prepare(
        "UPDATE jobs SET status=?, result=?, completed_at=NOW() WHERE id=?"
    )->execute([$status, json_encode($result, JSON_UNESCAPED_UNICODE), $id]);
}

function markRetry(\PDO $db, int $id): void
{
    // Max attempts aşıldıysa failed yap
    $db->prepare(
        "UPDATE jobs
            SET status = IF(attempts >= max_attempts, 'failed', 'pending'),
                completed_at = IF(attempts >= max_attempts, NOW(), NULL)
          WHERE id = ?"
    )->execute([$id]);
}

/**
 * İşi AgentClient üzerinden çalıştır.
 * Her job type'ı agent action'ına map eder.
 */
function executeJob(object $job): array
{
    require_once IPANEL_ROOT . '/web/iApp/Libraries/AgentClient.php';
    require_once IPANEL_ROOT . '/etc/ipanel/agent.conf.php';

    $payload = json_decode($job->payload, true) ?? [];

    // job type → agent action ('modul.metod' — agent modüllerindeki gerçek metod adları)
    $map = [
        'sites.create'    => 'nginx.createVhost',
        'sites.delete'    => 'nginx.deleteVhost',
        'ssl.issue'       => 'ssl.issueLetsEncrypt',
        'ssl.renew'       => 'ssl.renewAll',
        'database.create' => 'database.createDatabase',
        'database.delete' => 'database.dropDatabase',
        'dns.create'      => 'dns.createZone',
        'dns.delete'      => 'dns.deleteZone',
        'backup.create'   => 'backup.runFull',
        'backup.restore'  => 'backup.restore',
        'php.install'     => 'package.install',
        'php.remove'      => 'package.remove',
        'mail.create'     => 'mail.addAccount',
        'mail.delete'     => 'mail.removeAccount',
        'ftp.create'      => 'ftp.addUser',
        'ftp.delete'      => 'ftp.removeUser',
        'cron.create'     => 'cron.add',
        'cron.delete'     => 'cron.remove',
    ];

    if (!isset($map[$job->type])) {
        return ['error' => "Bilinmeyen iş türü: {$job->type}"];
    }

    $action = $map[$job->type];

    $config = require '/etc/ipanel/agent.conf.php';
    $client = new \Project\Libraries\AgentClient(
        '/run/ipanel/agent.sock',
        $config['secret_key']
    );

    try {
        return $client->call($action, $payload);
    } catch (\Throwable $e) {
        return ['error' => $e->getMessage()];
    }
}

// ────────────────────────────────────────────────
// Ana döngü
// ────────────────────────────────────────────────

rlog("iPanel Job Runner başlatıldı (PID: " . getmypid() . ")");

$running = [];  // fork'lanan child PID'leri

// SIGTERM handler
pcntl_signal(SIGTERM, function () use (&$running) {
    rlog("SIGTERM alındı, sonlanıyor...");
    foreach ($running as $pid => $_) {
        posix_kill($pid, SIGTERM);
    }
    exit(0);
});
pcntl_signal(SIGCHLD, SIG_DFL);

while (true) {
    pcntl_signal_dispatch();

    // Biten child'ları temizle
    foreach ($running as $pid => $jobId) {
        $res = pcntl_waitpid($pid, $status, WNOHANG);
        if ($res > 0) {
            unset($running[$pid]);
        }
    }

    if (count($running) >= MAX_CONCURRENT) {
        sleep(1);
        continue;
    }

    try {
        $db  = dbConn();
        $job = claimJob($db);
    } catch (\Throwable $e) {
        rlog("DB hatası: " . $e->getMessage());
        sleep(POLL_INTERVAL);
        continue;
    }

    if (!$job) {
        sleep(POLL_INTERVAL);
        continue;
    }

    rlog("İş başlatılıyor: [{$job->uuid}] {$job->type}");

    $pid = pcntl_fork();
    if ($pid === -1) {
        rlog("fork() başarısız, işi geri yüklüyor.");
        markRetry($db, $job->id);
        sleep(1);
        continue;
    }

    if ($pid === 0) {
        // Child process
        try {
            $result = executeJob($job);
            $ok     = !isset($result['error']);
            markDone(dbConn(), $job->id, $ok, $result);
            rlog(($ok ? 'Tamamlandı' : 'Başarısız') . ": [{$job->uuid}] " . json_encode($result));
        } catch (\Throwable $e) {
            markRetry(dbConn(), $job->id);
            rlog("İstisna [{$job->uuid}]: " . $e->getMessage());
        }
        exit(0);
    }

    // Parent
    $running[$pid] = $job->id;
}
