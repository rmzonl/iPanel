<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * Per-user crontab management.
 */
class CronModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['list', 'set', 'add', 'remove'];
    }

    public function list(array $p): array
    {
        $this->validate($p, ['user']);
        $user = $this->sanitizeUser($p['user']);
        $r = $this->run(['crontab', '-u', $user, '-l']);
        if ($r['exit'] !== 0 && strpos($r['stderr'], 'no crontab') === false) {
            throw new \RuntimeException($r['stderr']);
        }
        return ['lines' => array_values(array_filter(explode("\n", $r['stdout'])))];
    }

    /** Replace the entire crontab with the given content. */
    public function set(array $p): array
    {
        $this->validate($p, ['user', 'content']);
        $user = $this->sanitizeUser($p['user']);
        return $this->run(['crontab', '-u', $user, '-'], $p['content'] . "\n");
    }

    public function add(array $p): array
    {
        $this->validate($p, ['user', 'line']);
        $user = $this->sanitizeUser($p['user']);
        $existing = $this->list(['user' => $user])['lines'];
        $existing[] = $p['line'];
        return $this->set(['user' => $user, 'content' => implode("\n", $existing)]);
    }

    public function remove(array $p): array
    {
        $this->validate($p, ['user', 'line']);
        $user = $this->sanitizeUser($p['user']);
        $existing = $this->list(['user' => $user])['lines'];
        $existing = array_values(array_filter($existing, fn($l) => trim($l) !== trim($p['line'])));
        return $this->set(['user' => $user, 'content' => implode("\n", $existing)]);
    }

    private function sanitizeUser(string $u): string
    {
        if (!preg_match('/^[a-z_][a-z0-9_-]{0,31}$/', $u)) {
            throw new \InvalidArgumentException('invalid user');
        }
        return $u;
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
