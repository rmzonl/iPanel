<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * MySQL / MariaDB database and user management.
 *
 * Uses the root Unix socket (no password) — default on Debian/Ubuntu
 * with MariaDB. For MySQL 8, connect via ~/.my.cnf or /root/.my.cnf.
 */
class DatabaseModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['createDatabase', 'dropDatabase', 'createUser', 'dropUser', 'grant', 'listDatabases', 'dump', 'restore'];
    }

    public function createDatabase(array $p): array
    {
        $this->validate($p, ['name']);
        $name = $this->sanitizeIdent($p['name']);
        $charset = $p['charset'] ?? 'utf8mb4';
        $collation = $p['collation'] ?? 'utf8mb4_unicode_ci';

        $sql = "CREATE DATABASE `{$name}` CHARACTER SET {$charset} COLLATE {$collation};";
        return $this->mysql($sql);
    }

    public function dropDatabase(array $p): array
    {
        $this->validate($p, ['name']);
        $name = $this->sanitizeIdent($p['name']);
        return $this->mysql("DROP DATABASE IF EXISTS `{$name}`;");
    }

    public function createUser(array $p): array
    {
        $this->validate($p, ['user', 'password']);
        $user = $this->sanitizeIdent($p['user']);
        $host = $p['host'] ?? 'localhost';
        $pass = addslashes($p['password']);
        return $this->mysql("CREATE USER '{$user}'@'{$host}' IDENTIFIED BY '{$pass}';");
    }

    public function dropUser(array $p): array
    {
        $this->validate($p, ['user']);
        $user = $this->sanitizeIdent($p['user']);
        $host = $p['host'] ?? 'localhost';
        return $this->mysql("DROP USER IF EXISTS '{$user}'@'{$host}';");
    }

    public function grant(array $p): array
    {
        $this->validate($p, ['user', 'database']);
        $user = $this->sanitizeIdent($p['user']);
        $db   = $this->sanitizeIdent($p['database']);
        $host = $p['host']       ?? 'localhost';
        $priv = $p['privileges'] ?? 'ALL PRIVILEGES';
        $this->mysql("GRANT {$priv} ON `{$db}`.* TO '{$user}'@'{$host}';");
        return $this->mysql('FLUSH PRIVILEGES;');
    }

    public function listDatabases(array $p): array
    {
        return $this->mysql('SHOW DATABASES;');
    }

    public function dump(array $p): array
    {
        $this->validate($p, ['database', 'target']);
        $db  = $this->sanitizeIdent($p['database']);
        $tgt = $p['target'];
        $this->run(['sh', '-c', 'mysqldump ' . escapeshellarg($db) . ' | gzip -9 > ' . escapeshellarg($tgt)], null, 600);
        return ['dump' => $tgt, 'size' => @filesize($tgt)];
    }

    public function restore(array $p): array
    {
        $this->validate($p, ['database', 'source']);
        $db  = $this->sanitizeIdent($p['database']);
        $src = $p['source'];
        $cmd = (substr($src, -3) === '.gz')
            ? 'gunzip < ' . escapeshellarg($src) . ' | mysql ' . escapeshellarg($db)
            : 'mysql ' . escapeshellarg($db) . ' < ' . escapeshellarg($src);
        return $this->run(['sh', '-c', $cmd], null, 600);
    }

    private function mysql(string $sql): array
    {
        return $this->run(['mysql', '-e', $sql]);
    }

    private function sanitizeIdent(string $s): string
    {
        if (!preg_match('/^[a-zA-Z0-9_]{1,64}$/', $s)) {
            throw new \InvalidArgumentException('invalid identifier');
        }
        return $s;
    }

    private function validate(array $p, array $req): void
    {
        foreach ($req as $k) {
            if (!isset($p[$k]) || $p[$k] === '') {
                throw new \InvalidArgumentException("missing param: $k");
            }
        }
    }
}
