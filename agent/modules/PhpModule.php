<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

class PhpModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['listVersions', 'createPool', 'deletePool', 'switchVersion', 'reload', 'enableExtension', 'disableExtension'];
    }

    public function listVersions(array $p): array
    {
        $versions = [];
        foreach (glob('/etc/php/*/fpm') as $dir) {
            $v = basename(dirname($dir));
            if (preg_match('/^\d+\.\d+$/', $v)) {
                $versions[] = $v;
            }
        }
        sort($versions);
        return ['versions' => $versions];
    }

    public function createPool(array $p): array
    {
        $this->validate($p, ['site_username', 'version']);
        $u = $p['site_username'];
        $v = $p['version'];

        $poolDir = "/etc/php/{$v}/fpm/pool.d";
        if (!is_dir($poolDir)) {
            throw new \RuntimeException("php-{$v} not installed");
        }

        $memLimit    = $p['memory_limit']    ?? '128M';
        $maxExec     = $p['max_execution']   ?? 60;
        $uploadMax   = $p['upload_max']      ?? '32M';
        $postMax     = $p['post_max']        ?? '32M';

        $conf = <<<CONF
; Managed by iPanel
[{$u}]
user = {$u}
group = {$u}
listen = /run/php/{$u}.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = ondemand
pm.max_children = 10
pm.process_idle_timeout = 30s
pm.max_requests = 500
chdir = /home/{$u}/public_html

php_admin_value[memory_limit] = {$memLimit}
php_admin_value[max_execution_time] = {$maxExec}
php_admin_value[upload_max_filesize] = {$uploadMax}
php_admin_value[post_max_size] = {$postMax}
php_admin_value[open_basedir] = /home/{$u}/:/tmp/
CONF;

        $this->writeFile("{$poolDir}/{$u}.conf", $conf);
        $this->run(['systemctl', 'reload', "php{$v}-fpm"]);

        return ['pool' => "{$poolDir}/{$u}.conf"];
    }

    public function deletePool(array $p): array
    {
        $this->validate($p, ['site_username']);
        $u = $p['site_username'];
        foreach (glob("/etc/php/*/fpm/pool.d/{$u}.conf") as $f) {
            if (preg_match('#/etc/php/([\d.]+)/fpm#', $f, $m)) {
                @unlink($f);
                $this->run(['systemctl', 'reload', "php{$m[1]}-fpm"]);
            }
        }
        return ['deleted' => $u];
    }

    public function switchVersion(array $p): array
    {
        $this->validate($p, ['site_username', 'old_version', 'new_version']);
        $this->deletePool(['site_username' => $p['site_username']]);
        return $this->createPool([
            'site_username' => $p['site_username'],
            'version'       => $p['new_version'],
        ]);
    }

    public function reload(array $p): array
    {
        $v = $p['version'] ?? null;
        if ($v) {
            return $this->run(['systemctl', 'reload', "php{$v}-fpm"]);
        }
        $results = [];
        foreach (glob('/etc/php/*/fpm') as $dir) {
            $ver = basename(dirname($dir));
            $results[$ver] = $this->run(['systemctl', 'reload', "php{$ver}-fpm"]);
        }
        return $results;
    }

    public function enableExtension(array $p): array
    {
        $this->validate($p, ['extension', 'version']);
        return $this->run(['phpenmod', '-v', $p['version'], $p['extension']]);
    }

    public function disableExtension(array $p): array
    {
        $this->validate($p, ['extension', 'version']);
        return $this->run(['phpdismod', '-v', $p['version'], $p['extension']]);
    }

    private function validate(array $p, array $required): void
    {
        foreach ($required as $k) {
            if (!isset($p[$k]) || $p[$k] === '') {
                throw new \InvalidArgumentException("missing param: $k");
            }
        }
    }
}
