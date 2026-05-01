<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * Sistem paket yöneticisi (dnf/apt) üzerinden yazılım kurulum/kaldırma.
 * Sadece izin listesindeki paketlere izin verilir.
 */
class PackageModule extends BaseModule
{
    private const ALLOWED_PACKAGES = [
        'phpMyAdmin', 'phpmyadmin',
        'nodejs', 'npm',
        'git', 'unzip', 'curl', 'wget',
        'epel-release',
    ];

    public function allowedMethods(): array
    {
        return ['install', 'remove', 'isInstalled'];
    }

    public function install(array $p): array
    {
        $this->validate($p, ['package']);
        $pkg = $p['package'];
        $this->assertAllowed($pkg);

        $cmd = $this->buildCmd('install', $pkg);
        $r   = $this->run($cmd, null, 300);

        if ($r['exit'] !== 0) {
            throw new \RuntimeException(substr($r['stderr'] ?: $r['stdout'], -1000));
        }

        return ['package' => $pkg, 'output' => substr($r['stdout'], -2000)];
    }

    public function remove(array $p): array
    {
        $this->validate($p, ['package']);
        $pkg = $p['package'];
        $this->assertAllowed($pkg);

        $cmd = $this->buildCmd('remove', $pkg);
        $r   = $this->run($cmd, null, 120);

        if ($r['exit'] !== 0) {
            throw new \RuntimeException(substr($r['stderr'] ?: $r['stdout'], -1000));
        }

        return ['package' => $pkg];
    }

    public function isInstalled(array $p): array
    {
        $this->validate($p, ['package']);
        $pkg = $p['package'];
        $this->assertAllowed($pkg);

        if ($this->isRhel()) {
            $r = $this->run(['rpm', '-q', $pkg]);
        } else {
            $r = $this->run(['dpkg', '-s', $pkg]);
        }

        return ['package' => $pkg, 'installed' => ($r['exit'] === 0)];
    }

    private function buildCmd(string $action, string $pkg): array
    {
        if ($this->isRhel()) {
            return ['dnf', $action, '-y', $pkg];
        }
        return ['env', 'DEBIAN_FRONTEND=noninteractive', 'apt-get', $action, '-y', $pkg];
    }

    private function isRhel(): bool
    {
        return !preg_match('/debian|ubuntu/i', php_uname('v'));
    }

    private function assertAllowed(string $pkg): void
    {
        if (!in_array($pkg, self::ALLOWED_PACKAGES, true)) {
            throw new \RuntimeException("package not in allowlist: $pkg");
        }
    }

    protected function validate(array $p, array $required): void
    {
        foreach ($required as $k) {
            if (empty($p[$k])) {
                throw new \InvalidArgumentException("missing param: $k");
            }
        }
    }
}
