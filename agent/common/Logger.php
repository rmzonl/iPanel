<?php
namespace iPanel\Agent\Common;

class Logger
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    public function info(string $msg): void    { $this->write('INFO',  $msg); }
    public function warn(string $msg): void    { $this->write('WARN',  $msg); }
    public function error(string $msg): void   { $this->write('ERROR', $msg); }
    public function debug(string $msg): void   { $this->write('DEBUG', $msg); }

    private function write(string $level, string $msg): void
    {
        $line = sprintf("[%s] %-5s %s\n", date('Y-m-d H:i:s'), $level, $msg);
        @file_put_contents($this->path, $line, FILE_APPEND | LOCK_EX);
    }
}
