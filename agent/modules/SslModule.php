<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * Certbot (Let's Encrypt) + manual certificate installation.
 */
class SslModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['issueLetsEncrypt', 'renewAll', 'installManual', 'remove', 'list'];
    }

    public function issueLetsEncrypt(array $p): array
    {
        $this->validate($p, ['site_username', 'domain', 'email']);
        $u      = $p['site_username'];
        $domain = $p['domain'];
        $email  = $p['email'];
        $aliases = $p['aliases'] ?? [];

        $args = [
            'certbot', 'certonly', '--webroot',
            '-w', "/home/{$u}/public_html",
            '-d', $domain,
        ];
        foreach ($aliases as $a) {
            $args[] = '-d';
            $args[] = $a;
        }
        $args[] = '--non-interactive';
        $args[] = '--agree-tos';
        $args[] = '-m';
        $args[] = $email;

        $res = $this->run($args, null, 180);
        if ($res['exit'] !== 0) {
            throw new \RuntimeException('certbot failed: ' . $res['stderr']);
        }

        return [
            'cert'    => "/etc/letsencrypt/live/{$domain}/fullchain.pem",
            'key'     => "/etc/letsencrypt/live/{$domain}/privkey.pem",
            'domains' => array_merge([$domain], $aliases),
        ];
    }

    public function renewAll(array $p): array
    {
        return $this->run(['certbot', 'renew', '--quiet'], null, 300);
    }

    public function installManual(array $p): array
    {
        $this->validate($p, ['site_username', 'cert', 'key']);
        $u    = $p['site_username'];
        $dir  = "/home/{$u}/ssl";
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $this->writeFile("{$dir}/cert.pem",  $p['cert'], 0640);
        $this->writeFile("{$dir}/key.pem",   $p['key'],  0600);
        if (!empty($p['ca_bundle'])) {
            $this->writeFile("{$dir}/chain.pem", $p['ca_bundle'], 0640);
        }
        $this->run(['chown', '-R', "{$u}:{$u}", $dir]);
        return ['dir' => $dir];
    }

    public function remove(array $p): array
    {
        $this->validate($p, ['domain']);
        return $this->run(['certbot', 'delete', '--cert-name', $p['domain'], '--non-interactive']);
    }

    public function list(array $p): array
    {
        return $this->run(['certbot', 'certificates']);
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
