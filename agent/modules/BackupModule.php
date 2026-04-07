<?php
namespace iPanel\Agent\Modules;

use iPanel\Agent\Common\BaseModule;

class BackupModule extends BaseModule
{
    public function allowedMethods(): array
    {
        return ['runFull', 'runFiles', 'runDatabase', 'restore', 'prune'];
    }

    public function runFull(array $p): array
    {
        $this->validate($p, ['site_username', 'target_dir']);
        $u = $p['site_username'];
        $target = rtrim($p['target_dir'], '/');
        if (!is_dir($target)) {
            mkdir($target, 0750, true);
        }
        $stamp = date('Ymd_His');
        $file  = "{$target}/{$u}_full_{$stamp}.tar.gz";
        $this->run([
            'tar', '-czf', $file,
            '--exclude=cache', '--exclude=tmp',
            '-C', '/home', $u,
        ], null, 1800);
        return ['file' => $file, 'size' => @filesize($file)];
    }

    public function runFiles(array $p): array
    {
        $this->validate($p, ['site_username', 'target_dir']);
        $u = $p['site_username'];
        $target = rtrim($p['target_dir'], '/');
        $stamp = date('Ymd_His');
        $file  = "{$target}/{$u}_files_{$stamp}.tar.gz";
        $this->run(['tar', '-czf', $file, '-C', "/home/{$u}", 'public_html'], null, 1800);
        return ['file' => $file, 'size' => @filesize($file)];
    }

    public function runDatabase(array $p): array
    {
        $this->validate($p, ['database', 'target_dir']);
        $target = rtrim($p['target_dir'], '/');
        $stamp  = date('Ymd_His');
        $file   = "{$target}/{$p['database']}_{$stamp}.sql.gz";
        $this->run(
            ['sh', '-c', 'mysqldump ' . escapeshellarg($p['database']) . ' | gzip -9 > ' . escapeshellarg($file)],
            null, 900
        );
        return ['file' => $file, 'size' => @filesize($file)];
    }

    public function restore(array $p): array
    {
        $this->validate($p, ['site_username', 'archive']);
        $u = $p['site_username'];
        return $this->run(['tar', '-xzf', $p['archive'], '-C', "/home/{$u}"], null, 1800);
    }

    public function prune(array $p): array
    {
        $this->validate($p, ['target_dir', 'days']);
        $dir  = $p['target_dir'];
        $days = (int) $p['days'];
        return $this->run(['find', $dir, '-type', 'f', '-mtime', "+{$days}", '-delete']);
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
