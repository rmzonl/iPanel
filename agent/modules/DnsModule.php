<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * BIND9 backend. PowerDNS support is planned.
 */
class DnsModule extends BaseModule
{
    private string $zonesDir = '/etc/bind/zones';
    private string $namedConf = '/etc/bind/named.conf.local';

    public function allowedMethods(): array
    {
        return ['createZone', 'deleteZone', 'writeZoneFile', 'reload', 'check'];
    }

    public function createZone(array $p): array
    {
        $this->validate($p, ['domain', 'ns1', 'ns2', 'admin_email']);
        $domain = $this->sanitizeDomain($p['domain']);

        if (!is_dir($this->zonesDir)) {
            mkdir($this->zonesDir, 0755, true);
        }

        $serial = date('Ymd') . '01';
        $admin  = str_replace('@', '.', $p['admin_email']);
        $records = $p['records'] ?? [];

        $content  = ";; Managed by iPanel\n";
        $content .= "\$TTL 3600\n";
        $content .= "@\tIN\tSOA\t{$p['ns1']}.\t{$admin}. (\n";
        $content .= "\t\t\t{$serial}\t; serial\n";
        $content .= "\t\t\t3600\t; refresh\n";
        $content .= "\t\t\t1800\t; retry\n";
        $content .= "\t\t\t604800\t; expire\n";
        $content .= "\t\t\t600 )\t; minimum\n\n";
        $content .= "@\tIN\tNS\t{$p['ns1']}.\n";
        $content .= "@\tIN\tNS\t{$p['ns2']}.\n\n";

        foreach ($records as $r) {
            $name     = $this->sanitizeRRName($r['name']  ?? '@');
            $type     = $this->sanitizeRRType($r['type']  ?? 'A');
            $value    = $this->sanitizeRRValue($type, $r['value'] ?? '');
            $priority = ($type === 'MX' && isset($r['priority']))
                ? ((int)$r['priority']) . "\t"
                : '';
            $content .= sprintf("%s\tIN\t%s\t%s%s\n", $name, $type, $priority, $value);
        }

        $zoneFile = "{$this->zonesDir}/{$domain}.zone";
        $this->writeFile($zoneFile, $content);

        $check = $this->run(['named-checkzone', $domain, $zoneFile]);
        if ($check['exit'] !== 0) {
            @unlink($zoneFile);
            throw new \RuntimeException('zone check failed: ' . $check['stderr']);
        }

        $stanza = "\nzone \"{$domain}\" {\n    type master;\n    file \"{$zoneFile}\";\n};\n";
        if (strpos((string) @file_get_contents($this->namedConf), "zone \"{$domain}\"") === false) {
            file_put_contents($this->namedConf, $stanza, FILE_APPEND);
        }

        $this->reload([]);
        return ['zone' => $domain, 'file' => $zoneFile];
    }

    public function deleteZone(array $p): array
    {
        $this->validate($p, ['domain']);
        $domain = $this->sanitizeDomain($p['domain']);

        @unlink("{$this->zonesDir}/{$domain}.zone");

        $conf = (string) @file_get_contents($this->namedConf);
        $conf = preg_replace('/\nzone\s+"' . preg_quote($domain, '/') . '"\s*\{[^}]*\};/s', '', $conf);
        file_put_contents($this->namedConf, $conf);

        $this->reload([]);
        return ['deleted' => $domain];
    }

    public function writeZoneFile(array $p): array
    {
        return $this->createZone($p);
    }

    public function reload(array $p): array
    {
        return $this->run(['rndc', 'reload']);
    }

    public function check(array $p): array
    {
        return $this->run(['named-checkconf']);
    }

    private function sanitizeRRName(string $n): string
    {
        $n = trim($n);
        if ($n === '@' || $n === '*') return $n;
        if (!preg_match('/^[a-zA-Z0-9\*_\-\.]{1,63}$/', $n)) {
            throw new \InvalidArgumentException("invalid RR name: $n");
        }
        return $n;
    }

    private function sanitizeRRType(string $t): string
    {
        $allowed = ['A', 'AAAA', 'CNAME', 'MX', 'NS', 'TXT', 'SRV', 'PTR', 'CAA', 'DKIM', 'SPF'];
        $t = strtoupper(trim($t));
        if (!in_array($t, $allowed, true)) {
            throw new \InvalidArgumentException("invalid RR type: $t");
        }
        return $t;
    }

    private function sanitizeRRValue(string $type, string $v): string
    {
        $v = trim($v);
        if ($v === '') throw new \InvalidArgumentException("empty RR value");
        // Satır sonu ve BIND direktif karakterlerini temizle
        $v = preg_replace('/[\r\n\x00]/', '', $v);
        if (in_array($type, ['TXT', 'SPF', 'DKIM'], true)) {
            // TXT kayıtları tırnak içine al, iç tırnakları escape et
            $v = '"' . str_replace('"', '\\"', $v) . '"';
        }
        return $v;
    }

    private function sanitizeDomain(string $d): string
    {
        $d = strtolower(trim($d, " \t\n."));
        if (!preg_match('/^[a-z0-9][a-z0-9.-]{1,253}[a-z0-9]$/', $d)) {
            throw new \InvalidArgumentException('invalid domain');
        }
        return $d;
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
