<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * Node.js / nvm yönetimi — root yetkisi gerektirir.
 */
class NodeModule extends BaseModule
{
    private const NVM_DIR = '/usr/local/nvm';

    public function allowedMethods(): array
    {
        return ['status', 'installNvm', 'installVersion', 'setDefault', 'removeVersion', 'installPm2'];
    }

    /** Mevcut node/npm/nvm/pm2 durumu */
    public function status(array $p): array
    {
        $env = $this->nvmEnv();

        $node = trim($this->runShell("$env node --version 2>/dev/null || true")['stdout']);
        $npm  = trim($this->runShell("$env npm --version 2>/dev/null || true")['stdout']);
        $pm2  = trim($this->runShell("$env which pm2 2>/dev/null || true")['stdout']);

        $nvmInstalled = file_exists(self::NVM_DIR . '/nvm.sh');
        $nvmVersions  = [];

        if ($nvmInstalled) {
            $r = $this->runShell($env . " nvm list 2>/dev/null || true");
            foreach (explode("\n", $r['stdout']) as $line) {
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
            'node_version'  => $node ?: null,
            'npm_version'   => $npm  ?: null,
            'pm2_installed' => !empty($pm2),
            'nvm_installed' => $nvmInstalled,
            'nvm_versions'  => $nvmVersions,
        ];
    }

    /** nvm'i /usr/local/nvm'e kur */
    public function installNvm(array $p): array
    {
        $nvmDir = self::NVM_DIR;
        $r = $this->runShell(
            "curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh 2>/dev/null" .
            " | NVM_DIR=" . escapeshellarg($nvmDir) . " bash 2>&1",
            300
        );

        if ($r['exit'] !== 0) {
            throw new \RuntimeException(substr($r['stderr'] ?: $r['stdout'], -1000));
        }

        return ['nvm_dir' => $nvmDir];
    }

    /** Belirli bir Node.js sürümünü kur */
    public function installVersion(array $p): array
    {
        $this->validate($p, ['version']);
        $ver = $this->sanitizeVersion($p['version']);

        if (file_exists(self::NVM_DIR . '/nvm.sh')) {
            $env = $this->nvmEnv();
            $r   = $this->runShell("$env nvm install " . escapeshellarg($ver) . " 2>&1", 300);
        } else {
            $r = $this->installViaPackageManager('nodejs');
        }

        if ($r['exit'] !== 0) {
            throw new \RuntimeException(substr($r['stderr'] ?: $r['stdout'], -1000));
        }

        return ['version' => $ver];
    }

    /** nvm varsayılan sürümünü değiştir */
    public function setDefault(array $p): array
    {
        $this->validate($p, ['version']);
        $ver = $this->sanitizeVersion($p['version']);

        $env = $this->nvmEnv();
        $r   = $this->runShell("$env nvm alias default " . escapeshellarg($ver) . " 2>&1");

        if ($r['exit'] !== 0) {
            throw new \RuntimeException(substr($r['stderr'] ?: $r['stdout'], -500));
        }

        return ['version' => $ver];
    }

    /** Belirli bir Node.js sürümünü kaldır */
    public function removeVersion(array $p): array
    {
        $this->validate($p, ['version']);
        $ver = $this->sanitizeVersion($p['version']);

        if (file_exists(self::NVM_DIR . '/nvm.sh')) {
            $env = $this->nvmEnv();
            $r   = $this->runShell("$env nvm uninstall " . escapeshellarg($ver) . " 2>&1");
        } else {
            $r = $this->removeViaPackageManager('nodejs');
        }

        if ($r['exit'] !== 0) {
            throw new \RuntimeException(substr($r['stderr'] ?: $r['stdout'], -500));
        }

        return ['version' => $ver];
    }

    /** pm2'yi global olarak kur */
    public function installPm2(array $p): array
    {
        $env = $this->nvmEnv();
        $r   = $this->runShell("$env npm install -g pm2 2>&1", 120);

        if ($r['exit'] !== 0) {
            throw new \RuntimeException(substr($r['stderr'] ?: $r['stdout'], -500));
        }

        return ['pm2' => 'installed'];
    }

    // ── Yardımcılar ──────────────────────────────────────────────────────────

    private function nvmEnv(): string
    {
        $dir = escapeshellarg(self::NVM_DIR);
        return "export NVM_DIR=$dir && source $dir/nvm.sh &&";
    }

    private function runShell(string $cmd, int $timeout = 60): array
    {
        return $this->run(['bash', '-c', $cmd], null, $timeout);
    }

    private function isRhel(): bool
    {
        return !preg_match('/debian|ubuntu/i', php_uname('v'));
    }

    private function installViaPackageManager(string $pkg): array
    {
        $cmd = $this->isRhel()
            ? ['dnf', 'install', '-y', $pkg]
            : ['env', 'DEBIAN_FRONTEND=noninteractive', 'apt-get', 'install', '-y', $pkg];
        return $this->run($cmd, null, 300);
    }

    private function removeViaPackageManager(string $pkg): array
    {
        $cmd = $this->isRhel()
            ? ['dnf', 'remove', '-y', $pkg]
            : ['env', 'DEBIAN_FRONTEND=noninteractive', 'apt-get', 'remove', '-y', $pkg];
        return $this->run($cmd, null, 120);
    }

    private function sanitizeVersion(string $ver): string
    {
        if (!preg_match('/^(lts|latest|v?\d+(\.\d+){0,2})$/', $ver)) {
            throw new \InvalidArgumentException("invalid version: $ver");
        }
        return $ver;
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
