<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use Session;
use Redirect;
use Project\Libraries\CsrfGuard;
use Project\Libraries\AuditLogger;

/**
 * Node.js sürüm yönetimi — sadece admin.
 * nvm (Node Version Manager) veya sistem paketi üzerinden çalışır.
 */
class NodeManager extends Controller
{
    private const NVM_DIR = '/usr/local/nvm';

    public function main(): void
    {
        View::pageTitle('Node.js Yönetimi');
        View::nodeInfo($this->collectInfo());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    /** nvm kur */
    public function installNvm(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $output = [];
        // nvm'i /usr/local/nvm'e kur (tüm kullanıcılar için)
        exec(
            'curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh 2>/dev/null | NVM_DIR=' . escapeshellarg(self::NVM_DIR) . ' bash 2>&1',
            $output, $rc
        );

        if ($rc === 0) {
            AuditLogger::log('node.nvm_install', 'system', null, 'nvm kuruldu');
            Session::insert('success', 'nvm başarıyla kuruldu.');
        } else {
            Session::insert('error', 'nvm kurulum hatası: ' . htmlspecialchars(implode("\n", array_slice($output, -5))));
        }

        Redirect::action('nodemanager/main');
    }

    /** Belirli bir Node.js sürümünü kur */
    public function installVersion(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $version = trim((string) Post::get('node_version'));
        // Geçerli format: 18, 20, 22, lts, latest veya v18.0.0
        if (!preg_match('/^(lts|latest|v?\d+(\.\d+){0,2})$/', $version)) {
            Session::insert('error', 'Geçersiz Node.js sürümü.');
            Redirect::action('nodemanager/main');
            return;
        }

        $escVer = escapeshellarg($version);
        $output = [];

        if ($this->nvmInstalled()) {
            $nvmDir = escapeshellarg(self::NVM_DIR);
            exec("bash -c 'export NVM_DIR=$nvmDir && source $nvmDir/nvm.sh && nvm install $escVer 2>&1'", $output, $rc);
        } else {
            // nvm yoksa sistem paketi (NodeSource)
            $os = file_exists('/etc/debian_version') ? 'debian' : 'rhel';
            if ($os === 'debian') {
                exec("curl -fsSL https://deb.nodesource.com/setup_{$escVer}.x | bash - 2>&1 && apt-get install -y nodejs 2>&1", $output, $rc);
            } else {
                exec("curl -fsSL https://rpm.nodesource.com/setup_{$escVer}.x | bash - 2>&1 && dnf install -y nodejs 2>&1", $output, $rc);
            }
        }

        if ($rc === 0) {
            AuditLogger::log('node.install', 'system', null, "Node.js $version kuruldu");
            Session::insert('success', "Node.js $version başarıyla kuruldu.");
        } else {
            Session::insert('error', 'Kurulum hatası: ' . htmlspecialchars(implode("\n", array_slice($output, -5))));
        }

        Redirect::action('nodemanager/main');
    }

    /** Varsayılan Node.js sürümünü değiştir (nvm use --default) */
    public function setDefault(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $version = trim((string) Post::get('node_version'));
        if (!preg_match('/^v?\d+(\.\d+){0,2}$/', $version)) {
            Session::insert('error', 'Geçersiz sürüm formatı.');
            Redirect::action('nodemanager/main');
            return;
        }

        $escVer = escapeshellarg($version);
        $nvmDir = escapeshellarg(self::NVM_DIR);
        $output = [];

        exec("bash -c 'export NVM_DIR=$nvmDir && source $nvmDir/nvm.sh && nvm alias default $escVer 2>&1'", $output, $rc);

        if ($rc === 0) {
            AuditLogger::log('node.set_default', 'system', null, "Node.js varsayılan: $version");
            Session::insert('success', "Node.js $version varsayılan olarak ayarlandı.");
        } else {
            Session::insert('error', 'Sürüm değiştirme hatası: ' . htmlspecialchars(implode("\n", $output)));
        }

        Redirect::action('nodemanager/main');
    }

    /** Node.js kaldır */
    public function remove(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $version = trim((string) Post::get('node_version'));
        if (!preg_match('/^v?\d+(\.\d+){0,2}$/', $version)) {
            Session::insert('error', 'Geçersiz sürüm formatı.');
            Redirect::action('nodemanager/main');
            return;
        }

        $escVer = escapeshellarg($version);
        $nvmDir = escapeshellarg(self::NVM_DIR);
        $output = [];

        if ($this->nvmInstalled()) {
            exec("bash -c 'export NVM_DIR=$nvmDir && source $nvmDir/nvm.sh && nvm uninstall $escVer 2>&1'", $output, $rc);
        } else {
            $os = file_exists('/etc/debian_version') ? 'debian' : 'rhel';
            $cmd = $os === 'debian' ? 'apt-get remove -y nodejs 2>&1' : 'dnf remove -y nodejs 2>&1';
            exec($cmd, $output, $rc);
        }

        if ($rc === 0) {
            AuditLogger::log('node.remove', 'system', null, "Node.js $version kaldırıldı");
            Session::insert('success', "Node.js $version kaldırıldı.");
        } else {
            Session::insert('error', 'Kaldırma hatası: ' . htmlspecialchars(implode("\n", $output)));
        }

        Redirect::action('nodemanager/main');
    }

    /** pm2 aracını yönet (start/stop global) */
    public function pm2(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('nodemanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('nodemanager/main'); return; }

        $action = Post::get('action');
        if (!in_array($action, ['install', 'list'], true)) {
            Session::insert('error', 'Geçersiz işlem.');
            Redirect::action('nodemanager/main');
            return;
        }

        $output = [];
        if ($action === 'install') {
            exec('npm install -g pm2 2>&1', $output, $rc);
            $msg = $rc === 0 ? 'pm2 başarıyla kuruldu.' : 'pm2 kurulum hatası: ' . implode(' ', array_slice($output, -3));
        } else {
            exec('pm2 list 2>&1', $output, $rc);
            $msg = implode("\n", $output);
        }

        if ($action === 'install' && $rc === 0) {
            AuditLogger::log('node.pm2_install', 'system', null, 'pm2 kuruldu');
            Session::insert('success', $msg);
        } elseif ($action === 'install') {
            Session::insert('error', htmlspecialchars($msg));
        } else {
            Session::insert('success', '<pre>' . htmlspecialchars($msg) . '</pre>');
        }

        Redirect::action('nodemanager/main');
    }

    private function collectInfo(): array
    {
        // Mevcut node versiyonu
        exec('node --version 2>/dev/null', $nodeOut);
        $nodeVersion = trim($nodeOut[0] ?? '');

        exec('npm --version 2>/dev/null', $npmOut);
        $npmVersion = trim($npmOut[0] ?? '');

        exec('which pm2 2>/dev/null', $pm2Out);
        $pm2Installed = !empty(trim($pm2Out[0] ?? ''));

        $nvmInstalled = $this->nvmInstalled();

        // nvm ile kurulu sürümler
        $nvmVersions = [];
        if ($nvmInstalled) {
            $nvmDir = escapeshellarg(self::NVM_DIR);
            exec("bash -c 'export NVM_DIR=$nvmDir && source $nvmDir/nvm.sh && nvm list 2>/dev/null'", $nvmList);
            foreach ($nvmList as $line) {
                if (preg_match('/(v\d+\.\d+\.\d+)/', $line, $m)) {
                    $nvmVersions[] = [
                        'version' => $m[1],
                        'default' => strpos($line, 'default') !== false,
                        'current' => strpos($line, '->') !== false || strpos($line, 'current') !== false,
                    ];
                }
            }
        }

        return [
            'node_version'  => $nodeVersion ?: null,
            'npm_version'   => $npmVersion  ?: null,
            'pm2_installed' => $pm2Installed,
            'nvm_installed' => $nvmInstalled,
            'nvm_versions'  => $nvmVersions,
        ];
    }

    private function nvmInstalled(): bool
    {
        return file_exists(self::NVM_DIR . '/nvm.sh');
    }
}
