<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * Postfix + Dovecot virtual mailbox management.
 */
class MailModule extends BaseModule
{
    private string $vmailbox  = '/etc/postfix/vmailbox';
    private string $valiases  = '/etc/postfix/virtual';
    private string $passwdDb  = '/etc/dovecot/users';

    public function allowedMethods(): array
    {
        return ['addAccount', 'removeAccount', 'setQuota', 'addAlias', 'removeAlias', 'reloadPostfix', 'reloadDovecot', 'generateDkim'];
    }

    public function addAccount(array $p): array
    {
        $this->validate($p, ['email', 'password']);
        $email = strtolower($p['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('invalid email');
        }
        [$local, $domain] = explode('@', $email);
        $maildir = "/var/vmail/{$domain}/{$local}/";

        // Postfix mailbox map
        file_put_contents($this->vmailbox, "{$email} {$domain}/{$local}/\n", FILE_APPEND);
        $this->run(['postmap', $this->vmailbox]);

        // Dovecot passwd-file (SHA512-CRYPT)
        $hash = crypt($p['password'], '$6$' . bin2hex(random_bytes(8)));
        $line = "{$email}:{{SHA512-CRYPT}}{$hash}::::::\n";
        file_put_contents($this->passwdDb, $line, FILE_APPEND);

        // Create maildir
        if (!is_dir($maildir)) {
            mkdir($maildir, 0770, true);
            $this->run(['chown', '-R', 'vmail:vmail', "/var/vmail/{$domain}"]);
        }

        $this->reloadPostfix([]);
        $this->reloadDovecot([]);
        return ['email' => $email, 'maildir' => $maildir];
    }

    public function removeAccount(array $p): array
    {
        $this->validate($p, ['email']);
        $email = strtolower($p['email']);

        foreach ([$this->vmailbox, $this->passwdDb] as $file) {
            if (!file_exists($file)) continue;
            $lines = file($file, FILE_IGNORE_NEW_LINES);
            $lines = array_filter($lines, fn($l) => stripos($l, $email) !== 0);
            file_put_contents($file, implode("\n", $lines) . "\n");
        }
        $this->run(['postmap', $this->vmailbox]);

        $this->reloadPostfix([]);
        $this->reloadDovecot([]);
        return ['removed' => $email];
    }

    public function setQuota(array $p): array
    {
        $this->validate($p, ['email', 'bytes']);
        // Dovecot quota-plugin syntax: appended to user line (userdb_quota_rule=*:bytes=XXXX)
        return ['note' => 'quota managed via dovecot quota plugin; implement per-deployment'];
    }

    public function addAlias(array $p): array
    {
        $this->validate($p, ['alias', 'destination']);
        file_put_contents($this->valiases, "{$p['alias']} {$p['destination']}\n", FILE_APPEND);
        $this->run(['postmap', $this->valiases]);
        $this->reloadPostfix([]);
        return ['alias' => $p['alias'], 'dest' => $p['destination']];
    }

    public function removeAlias(array $p): array
    {
        $this->validate($p, ['alias']);
        if (!file_exists($this->valiases)) {
            return ['removed' => false];
        }
        $lines = file($this->valiases, FILE_IGNORE_NEW_LINES);
        $lines = array_filter($lines, fn($l) => stripos($l, $p['alias'] . ' ') !== 0);
        file_put_contents($this->valiases, implode("\n", $lines) . "\n");
        $this->run(['postmap', $this->valiases]);
        $this->reloadPostfix([]);
        return ['removed' => $p['alias']];
    }

    public function reloadPostfix(array $p): array
    {
        return $this->run(['systemctl', 'reload', 'postfix']);
    }

    public function reloadDovecot(array $p): array
    {
        return $this->run(['systemctl', 'reload', 'dovecot']);
    }

    public function generateDkim(array $p): array
    {
        $this->validate($p, ['domain']);
        $domain = $p['domain'];
        $dir    = "/etc/opendkim/keys/{$domain}";
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $this->run(['opendkim-genkey', '-D', $dir, '-d', $domain, '-s', 'default']);
        $this->run(['chown', '-R', 'opendkim:opendkim', $dir]);
        return [
            'private' => "{$dir}/default.private",
            'txt'     => "{$dir}/default.txt",
        ];
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
