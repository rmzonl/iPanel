<?php
namespace iPanel\Agent\Common;

abstract class BaseModule implements ModuleInterface
{
    protected Logger $logger;
    protected array  $config;

    public function __construct(Logger $logger, array $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    public function isAllowed(string $method): bool
    {
        return in_array($method, $this->allowedMethods(), true);
    }

    protected function run(array $argv, ?string $stdin = null, int $timeout = 60): array
    {
        return Command::run($argv, $stdin, $timeout);
    }

    protected function writeFile(string $path, string $content, int $mode = 0644): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, $content);
        chmod($path, $mode);
    }
}
