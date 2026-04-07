<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

class NginxModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['reload', 'restart', 'test', 'status', 'createVhost', 'deleteVhost', 'enableVhost', 'disableVhost'];
    }

    public function reload(array $p): array
    {
        $test = $this->run(['nginx', '-t']);
        if ($test['exit'] !== 0) {
            throw new \RuntimeException('nginx config test failed: ' . $test['stderr']);
        }
        return $this->run(['systemctl', 'reload', 'nginx']);
    }

    public function restart(array $p): array
    {
        return $this->run(['systemctl', 'restart', 'nginx']);
    }

    public function test(array $p): array
    {
        return $this->run(['nginx', '-t']);
    }

    public function status(array $p): array
    {
        return $this->run(['systemctl', 'is-active', 'nginx']);
    }

    public function createVhost(array $p): array
    {
        $this->validate($p, ['site_username', 'domain', 'php_version']);
        $u = $p['site_username'];
        $d = $p['domain'];
        $v = $p['php_version'];

        $conf = $this->renderVhost($u, $d, $v, $p['aliases'] ?? []);
        $path = "/etc/nginx/sites-available/{$u}.conf";
        $this->writeFile($path, $conf);

        $link = "/etc/nginx/sites-enabled/{$u}.conf";
        if (!file_exists($link)) {
            symlink($path, $link);
        }

        $this->reload([]);
        return ['vhost' => $path, 'enabled' => true];
    }

    public function deleteVhost(array $p): array
    {
        $this->validate($p, ['site_username']);
        $u = $p['site_username'];
        @unlink("/etc/nginx/sites-enabled/{$u}.conf");
        @unlink("/etc/nginx/sites-available/{$u}.conf");
        $this->reload([]);
        return ['deleted' => $u];
    }

    public function enableVhost(array $p): array
    {
        $this->validate($p, ['site_username']);
        $u = $p['site_username'];
        $src = "/etc/nginx/sites-available/{$u}.conf";
        $dst = "/etc/nginx/sites-enabled/{$u}.conf";
        if (!file_exists($src)) {
            throw new \RuntimeException('vhost not found');
        }
        if (!file_exists($dst)) {
            symlink($src, $dst);
        }
        $this->reload([]);
        return ['enabled' => $u];
    }

    public function disableVhost(array $p): array
    {
        $this->validate($p, ['site_username']);
        @unlink("/etc/nginx/sites-enabled/{$p['site_username']}.conf");
        $this->reload([]);
        return ['disabled' => $p['site_username']];
    }

    private function renderVhost(string $user, string $domain, string $phpVer, array $aliases): string
    {
        $aliasList = empty($aliases) ? '' : ' ' . implode(' ', $aliases);
        $sock      = "/run/php/{$user}.sock";
        $root      = "/home/{$user}/public_html";
        $logs      = "/home/{$user}/logs";

        return <<<CONF
# Managed by iPanel — do not edit by hand
server {
    listen 80;
    listen [::]:80;
    server_name {$domain}{$aliasList};
    root {$root};
    index index.php index.html;

    access_log {$logs}/access.log;
    error_log  {$logs}/error.log warn;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include fastcgi_params;
        fastcgi_pass unix:{$sock};
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
CONF;
    }

    private function validate(array $p, array $required): void
    {
        foreach ($required as $k) {
            if (empty($p[$k])) {
                throw new \InvalidArgumentException("missing param: $k");
            }
        }
    }
}
