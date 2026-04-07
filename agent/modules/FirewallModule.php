<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * UFW (Ubuntu/Debian) and firewalld (RHEL) abstraction.
 */
class FirewallModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['allow', 'deny', 'delete', 'status', 'enable', 'reload'];
    }

    public function allow(array $p): array
    {
        return $this->rule('allow', $p);
    }

    public function deny(array $p): array
    {
        return $this->rule('deny', $p);
    }

    public function delete(array $p): array
    {
        return $this->rule('delete', $p, true);
    }

    public function status(array $p): array
    {
        return $this->detect() === 'ufw'
            ? $this->run(['ufw', 'status', 'verbose'])
            : $this->run(['firewall-cmd', '--list-all']);
    }

    public function enable(array $p): array
    {
        return $this->detect() === 'ufw'
            ? $this->run(['ufw', '--force', 'enable'])
            : $this->run(['systemctl', 'enable', '--now', 'firewalld']);
    }

    public function reload(array $p): array
    {
        return $this->detect() === 'ufw'
            ? $this->run(['ufw', 'reload'])
            : $this->run(['firewall-cmd', '--reload']);
    }

    private function rule(string $op, array $p, bool $delete = false): array
    {
        $port     = $p['port']     ?? null;
        $proto    = $p['protocol'] ?? 'tcp';
        $source   = $p['source']   ?? null;

        if ($this->detect() === 'ufw') {
            $args = ['ufw'];
            if ($delete) {
                $args[] = 'delete';
                $args[] = $op === 'delete' ? 'allow' : $op;
            } else {
                $args[] = $op;
            }
            if ($source) {
                $args[] = 'from';
                $args[] = $source;
            }
            if ($port) {
                $args[] = 'to';
                $args[] = 'any';
                $args[] = 'port';
                $args[] = $port;
                $args[] = 'proto';
                $args[] = $proto;
            }
            return $this->run($args);
        }

        // firewalld
        $rich = sprintf(
            'rule family="ipv4"%s%s %s',
            $source ? " source address=\"{$source}\"" : '',
            $port   ? " port port=\"{$port}\" protocol=\"{$proto}\"" : '',
            $op === 'allow' ? 'accept' : 'drop'
        );
        $flag = $delete ? '--remove-rich-rule' : '--add-rich-rule';
        return $this->run(['firewall-cmd', '--permanent', $flag, $rich]);
    }

    private function detect(): string
    {
        static $backend = null;
        if ($backend !== null) {
            return $backend;
        }
        $r = $this->run(['which', 'ufw']);
        return $backend = ($r['exit'] === 0 && $r['stdout']) ? 'ufw' : 'firewalld';
    }
}
