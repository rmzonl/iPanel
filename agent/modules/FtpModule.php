<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * PureFTPd virtual user management.
 */
class FtpModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['addUser', 'removeUser', 'setPassword', 'listUsers', 'reload'];
    }

    public function addUser(array $p): array
    {
        $this->validate($p, ['username', 'password', 'uid_user', 'homedir']);

        $pid = proc_open(
            'pure-pw useradd ' . escapeshellarg($p['username']) .
            ' -u ' . escapeshellarg($p['uid_user']) .
            ' -d ' . escapeshellarg($p['homedir']) . ' -m',
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        if (!is_resource($pid)) {
            throw new \RuntimeException('pure-pw exec failed');
        }
        fwrite($pipes[0], $p['password'] . "\n" . $p['password'] . "\n");
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        proc_close($pid);

        $this->run(['pure-pw', 'mkdb']);
        return ['user' => $p['username'], 'stdout' => $out, 'stderr' => $err];
    }

    public function removeUser(array $p): array
    {
        $this->validate($p, ['username']);
        $this->run(['pure-pw', 'userdel', $p['username']]);
        $this->run(['pure-pw', 'mkdb']);
        return ['removed' => $p['username']];
    }

    public function setPassword(array $p): array
    {
        $this->validate($p, ['username', 'password']);
        $pid = proc_open(
            'pure-pw passwd ' . escapeshellarg($p['username']) . ' -m',
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        fwrite($pipes[0], $p['password'] . "\n" . $p['password'] . "\n");
        fclose($pipes[0]);
        fclose($pipes[1]); fclose($pipes[2]);
        proc_close($pid);
        $this->run(['pure-pw', 'mkdb']);
        return ['updated' => $p['username']];
    }

    public function listUsers(array $p): array
    {
        return $this->run(['pure-pw', 'list']);
    }

    public function reload(array $p): array
    {
        return $this->run(['systemctl', 'reload', 'pure-ftpd']);
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
