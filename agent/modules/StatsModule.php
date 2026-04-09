<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

/**
 * Sistem kaynak istatistikleri — /proc ve /sys'den okur.
 * Dış araç gerektirmez; saf PHP ile çalışır.
 */
class StatsModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['snapshot', 'services', 'processes'];
    }

    /**
     * Tek çağrıda tüm sistem anlık görüntüsü.
     * Dashboard SSE endpoint'i bu metodu ~3 saniyede bir çağırır.
     */
    public function snapshot(array $p): array
    {
        return [
            'cpu'      => $this->cpuUsage(),
            'memory'   => $this->memoryInfo(),
            'disks'    => $this->diskInfo(),
            'network'  => $this->networkInfo(),
            'load'     => $this->loadAvg(),
            'uptime'   => $this->uptime(),
            'services' => $this->serviceStatus(),
            'ts'       => time(),
        ];
    }

    /** Servis durumları */
    public function services(array $p): array
    {
        return ['services' => $this->serviceStatus()];
    }

    /** En çok kaynak tüketen 10 process */
    public function processes(array $p): array
    {
        $limit = min((int)($p['limit'] ?? 10), 50);
        $procs = [];
        foreach (glob('/proc/[0-9]*/status') ?: [] as $f) {
            $s = @file_get_contents($f);
            if (!$s) continue;
            $fields = [];
            foreach (explode("\n", $s) as $line) {
                [$k, $v] = array_pad(explode(':', $line, 2), 2, '');
                $fields[trim($k)] = trim($v);
            }
            if (!isset($fields['Name'], $fields['VmRSS'])) continue;
            $rss = (int)filter_var($fields['VmRSS'], FILTER_SANITIZE_NUMBER_INT);
            $procs[] = [
                'pid'  => (int)basename(dirname($f)),
                'name' => $fields['Name'],
                'rss'  => $rss,
                'user' => $this->pidUser($fields['Uid'] ?? '0'),
            ];
        }
        usort($procs, fn($a, $b) => $b['rss'] <=> $a['rss']);
        return ['processes' => array_slice($procs, 0, $limit)];
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    private function cpuUsage(): array
    {
        // İki ölçüm arasındaki fark ile kullanım hesapla
        static $prev = null;
        $raw = @file('/proc/stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $cpus = [];
        foreach ($raw as $line) {
            if (!str_starts_with($line, 'cpu')) break;
            $parts = preg_split('/\s+/', trim($line));
            $label = array_shift($parts);
            $cpus[$label] = array_map('intval', $parts);
        }

        $result = [];
        foreach ($cpus as $label => $cur) {
            $p = $prev[$label] ?? null;
            if ($p !== null) {
                $idle    = $cur[3] - $p[3];
                $total   = array_sum($cur) - array_sum($p);
                $usage   = $total > 0 ? round((1 - $idle / $total) * 100, 1) : 0.0;
            } else {
                $total   = array_sum($cur) ?: 1;
                $idle    = $cur[3];
                $usage   = round((1 - $idle / $total) * 100, 1);
            }
            $result[] = ['core' => $label, 'usage' => $usage];
        }
        $prev = $cpus;
        return $result;
    }

    private function memoryInfo(): array
    {
        $info = [];
        foreach (@file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            [$k, $v] = array_pad(explode(':', $line, 2), 2, '0');
            $info[trim($k)] = (int)filter_var($v, FILTER_SANITIZE_NUMBER_INT);
        }
        $total    = $info['MemTotal']     ?? 0;
        $free     = $info['MemFree']      ?? 0;
        $buffers  = $info['Buffers']      ?? 0;
        $cached   = ($info['Cached']      ?? 0) + ($info['SReclaimable'] ?? 0);
        $used     = $total - $free - $buffers - $cached;
        $swap_tot = $info['SwapTotal']    ?? 0;
        $swap_free= $info['SwapFree']     ?? 0;

        return [
            'total_kb'     => $total,
            'used_kb'      => max(0, $used),
            'free_kb'      => $free,
            'cached_kb'    => $cached,
            'usage_pct'    => $total > 0 ? round($used / $total * 100, 1) : 0,
            'swap_total_kb'=> $swap_tot,
            'swap_used_kb' => max(0, $swap_tot - $swap_free),
        ];
    }

    private function diskInfo(): array
    {
        $disks = [];
        foreach (glob('/proc/mounts') ? file('/proc/mounts', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [] as $line) {
            $parts = preg_split('/\s+/', $line);
            $mp    = $parts[1] ?? '';
            $fs    = $parts[2] ?? '';
            if (!in_array($fs, ['ext4', 'xfs', 'btrfs', 'zfs', 'vfat', 'tmpfs'], true)) continue;
            if (!is_dir($mp) || str_starts_with($mp, '/proc') || str_starts_with($mp, '/sys')) continue;
            $total = @disk_total_space($mp);
            $free  = @disk_free_space($mp);
            if ($total === false) continue;
            $used  = $total - $free;
            $disks[] = [
                'mount'     => $mp,
                'fs'        => $fs,
                'total_kb'  => (int)($total / 1024),
                'used_kb'   => (int)($used  / 1024),
                'free_kb'   => (int)($free  / 1024),
                'usage_pct' => $total > 0 ? round($used / $total * 100, 1) : 0,
            ];
        }
        return $disks;
    }

    private function networkInfo(): array
    {
        static $prevNet = [];
        $lines = @file('/proc/net/dev', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $ifaces = [];
        foreach (array_slice($lines, 2) as $line) {
            [$iface, $rest] = array_pad(explode(':', $line, 2), 2, '');
            $iface = trim($iface);
            if (in_array($iface, ['lo'], true)) continue;
            $nums = preg_split('/\s+/', trim($rest));
            $rx = (int)($nums[0] ?? 0);  // bytes received
            $tx = (int)($nums[8] ?? 0);  // bytes transmitted
            $prx = $prevNet[$iface][0] ?? $rx;
            $ptx = $prevNet[$iface][1] ?? $tx;
            $ifaces[] = [
                'iface'  => $iface,
                'rx_b'   => $rx,
                'tx_b'   => $tx,
                'rx_rate'=> max(0, $rx - $prx),  // bytes/interval (per call)
                'tx_rate'=> max(0, $tx - $ptx),
            ];
            $prevNet[$iface] = [$rx, $tx];
        }
        return $ifaces;
    }

    private function loadAvg(): array
    {
        $raw = @file_get_contents('/proc/loadavg') ?: '0 0 0';
        $parts = explode(' ', $raw);
        return [
            '1m'  => (float)($parts[0] ?? 0),
            '5m'  => (float)($parts[1] ?? 0),
            '15m' => (float)($parts[2] ?? 0),
        ];
    }

    private function uptime(): int
    {
        $raw = @file_get_contents('/proc/uptime') ?: '0';
        return (int)explode(' ', $raw)[0];
    }

    private function serviceStatus(): array
    {
        $services = [
            'nginx'       => 'nginx',
            'php-fpm'     => 'php-fpm',
            'mariadb'     => 'mariadb',
            'redis'       => 'redis',
            'postfix'     => 'postfix',
            'dovecot'     => 'dovecot',
            'bind'        => 'named',
            'pure-ftpd'   => 'pure-ftpd',
            'ipanel-agent'=> 'ipanel-agent',
            'ipanel-jobs' => 'ipanel-jobs',
            'firewalld'   => 'firewalld',
            'fail2ban'    => 'fail2ban',
        ];
        $result = [];
        foreach ($services as $label => $unit) {
            $out = shell_exec("systemctl is-active " . escapeshellarg($unit) . " 2>/dev/null");
            $result[$label] = trim($out ?? '') === 'active';
        }
        return $result;
    }

    private function pidUser(string $uidLine): string
    {
        $uid = (int)preg_split('/\s+/', trim($uidLine))[0];
        return posix_getpwuid($uid)['name'] ?? (string)$uid;
    }
}
