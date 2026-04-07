<?php
namespace iPanel\Agent\Common;

/**
 * Safe shell command runner. Uses exec() with escaped arguments.
 */
class Command
{
    public static function run(array $argv, ?string $stdin = null, int $timeout = 60): array
    {
        if (!$argv) {
            throw new \RuntimeException('empty command');
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $cmd = implode(' ', array_map('escapeshellarg', $argv));
        $proc = proc_open($cmd, $descriptorSpec, $pipes);
        if (!is_resource($proc)) {
            throw new \RuntimeException('proc_open failed');
        }

        if ($stdin !== null) {
            fwrite($pipes[0], $stdin);
        }
        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = $stderr = '';
        $start  = time();
        do {
            $stdout .= (string) stream_get_contents($pipes[1]);
            $stderr .= (string) stream_get_contents($pipes[2]);
            $status  = proc_get_status($proc);
            if (!$status['running']) {
                break;
            }
            if (time() - $start > $timeout) {
                proc_terminate($proc, SIGKILL);
                throw new \RuntimeException('command timeout');
            }
            usleep(50000);
        } while (true);

        $stdout .= (string) stream_get_contents($pipes[1]);
        $stderr .= (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($proc);

        return [
            'exit'   => $exitCode,
            'stdout' => trim($stdout),
            'stderr' => trim($stderr),
            'cmd'    => $cmd,
        ];
    }
}
