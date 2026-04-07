<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * Site user management, disk usage, OS info, service control (allowed list).
 */
class SystemModule extends BaseModule
{
    /** Services the agent is allowed to start/stop/restart/reload. */
    private const ALLOWED_SERVICES = [
        'nginx', 'apache2', 'mysql', 'mariadb', 'postfix', 'dovecot',
        'bind9', 'named', 'pdns', 'pure-ftpd', 'redis-server', 'fail2ban',
        'php7.4-fpm', 'php8.0-fpm', 'php8.1-fpm', 'php8.2-fpm', 'php8.3-fpm',
    ];

    public function allowedMethods(): array
    {
        return [
            'createSiteUser', 'deleteSiteUser',
            'serviceControl', 'serviceStatus',
            'diskUsage', 'memInfo', 'loadAvg', 'osInfo', 'uptime',
        ];
    }

    public function createSiteUser(array $p): array
    {
        $this->validate($p, ['username']);
        $u = $this->sanitizeUsername($p['username']);
        $home = "/home/{$u}";

        if (posix_getpwnam($u) !== false) {
            throw new \RuntimeException("user exists: $u");
        }

        $this->run(['useradd', '-m', '-d', $home, '-s', '/usr/sbin/nologin', $u]);
        $this->run(['passwd', '-l', $u]); // lock password login

        foreach (['public_html', 'logs', 'tmp', 'ssl', 'backups'] as $sub) {
            $dir = "{$home}/{$sub}";
            if (!is_dir($dir)) {
                mkdir($dir, 0750, true);
            }
        }
        $this->run(['chown', '-R', "{$u}:{$u}", $home]);
        $this->run(['chmod', '750', $home]);

        return ['user' => $u, 'home' => $home];
    }

    public function deleteSiteUser(array $p): array
    {
        $this->validate($p, ['username']);
        $u = $this->sanitizeUsername($p['username']);
        if (posix_getpwnam($u) === false) {
            return ['removed' => false, 'reason' => 'not_found'];
        }
        $this->run(['userdel', '-r', $u]);
        return ['removed' => true, 'user' => $u];
    }

    public function serviceControl(array $p): array
    {
        $this->validate($p, ['service', 'op']);
        $svc = $p['service'];
        $op  = $p['op'];

        if (!in_array($svc, self::ALLOWED_SERVICES, true)) {
            throw new \RuntimeException("service not whitelisted: $svc");
        }
        if (!in_array($op, ['start', 'stop', 'restart', 'reload'], true)) {
            throw new \RuntimeException("op not allowed: $op");
        }

        return $this->run(['systemctl', $op, $svc]);
    }

    public function serviceStatus(array $p): array
    {
        $this->validate($p, ['service']);
        if (!in_array($p['service'], self::ALLOWED_SERVICES, true)) {
            throw new \RuntimeException("service not whitelisted");
        }
        $r = $this->run(['systemctl', 'is-active', $p['service']]);
        return ['active' => trim($r['stdout']) === 'active', 'raw' => $r['stdout']];
    }

    public function diskUsage(array $p): array
    {
        $path = $p['path'] ?? '/';
        $total = @disk_total_space($path);
        $free  = @disk_free_space($path);
        return [
            'path'  => $path,
            'total' => $total,
            'free'  => $free,
            'used'  => $total - $free,
        ];
    }

    public function memInfo(array $p): array
    {
        $info = [];
        foreach (explode("\n", (string) @file_get_contents('/proc/meminfo')) as $line) {
            if (preg_match('/^([^:]+):\s+(\d+)/', $line, $m)) {
                $info[$m[1]] = (int) $m[2] * 1024;
            }
        }
        return $info;
    }

    public function loadAvg(array $p): array
    {
        return sys_getloadavg() ?: [];
    }

    public function osInfo(array $p): array
    {
        $info = [];
        foreach (explode("\n", (string) @file_get_contents('/etc/os-release')) as $line) {
            if (preg_match('/^([A-Z_]+)=(.+)$/', $line, $m)) {
                $info[$m[1]] = trim($m[2], '"');
            }
        }
        $info['kernel'] = php_uname('r');
        $info['arch']   = php_uname('m');
        return $info;
    }

    public function uptime(array $p): array
    {
        return ['seconds' => (int) floatval((string) @file_get_contents('/proc/uptime'))];
    }

    private function sanitizeUsername(string $u): string
    {
        if (!preg_match('/^[a-z_][a-z0-9_-]{1,31}$/', $u)) {
            throw new \InvalidArgumentException('invalid username');
        }
        return $u;
    }

    private function validate(array $p, array $req): void
    {
        foreach ($req as $k) {
            if (empty($p[$k])) {
                throw new \InvalidArgumentException("missing param: $k");
            }
        }
    }
}
