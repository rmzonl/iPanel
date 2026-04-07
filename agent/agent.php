<?php
/**
 * iPanel Agent Daemon
 *
 * Root-privileged daemon that executes server management operations
 * on behalf of the unprivileged Web UI. Communication via Unix socket
 * with HMAC-SHA256 signed JSON-RPC messages.
 *
 * @package  iPanel\Agent
 * @license  MIT
 */

declare(strict_types=1);

// Require root
if (posix_geteuid() !== 0) {
    fwrite(STDERR, "iPanel Agent must run as root.\n");
    exit(1);
}

define('IPANEL_ROOT', '/usr/local/ipanel');
define('AGENT_ROOT', __DIR__);
define('AGENT_CONFIG', '/etc/ipanel/agent.conf.php');
define('AGENT_SOCKET', '/run/ipanel/agent.sock');
define('AGENT_LOG', '/var/log/ipanel/agent.log');

require __DIR__ . '/common/Logger.php';
require __DIR__ . '/common/Protocol.php';
require __DIR__ . '/common/Command.php';
require __DIR__ . '/common/ModuleInterface.php';
require __DIR__ . '/common/BaseModule.php';

use iPanel\Agent\Common\Logger;
use iPanel\Agent\Common\Protocol;

// Load config
if (!file_exists(AGENT_CONFIG)) {
    fwrite(STDERR, "Missing config: " . AGENT_CONFIG . "\n");
    exit(1);
}
$config = require AGENT_CONFIG;

$logger = new Logger(AGENT_LOG);
$logger->info('iPanel Agent starting');

// Auto-load modules
$modules = [];
foreach (glob(__DIR__ . '/modules/*Module.php') as $file) {
    require $file;
    $class = 'iPanel\\Agent\\Modules\\' . basename($file, '.php');
    if (class_exists($class)) {
        $instance = new $class($logger, $config);
        $name     = strtolower(str_replace('Module', '', basename($file, '.php')));
        $modules[$name] = $instance;
        $logger->info("Module loaded: $name");
    }
}

// Prepare socket directory
$socketDir = dirname(AGENT_SOCKET);
if (!is_dir($socketDir)) {
    mkdir($socketDir, 0755, true);
}
if (file_exists(AGENT_SOCKET)) {
    unlink(AGENT_SOCKET);
}

// Create Unix socket
$server = stream_socket_server('unix://' . AGENT_SOCKET, $errno, $errstr);
if (!$server) {
    $logger->error("Socket failed: $errstr ($errno)");
    exit(1);
}

// Set socket perms: group=ipanel, mode=660
chmod(AGENT_SOCKET, 0660);
$ipanelGroup = posix_getgrnam('ipanel');
if ($ipanelGroup !== false) {
    chown(AGENT_SOCKET, 'root');
    chgrp(AGENT_SOCKET, 'ipanel');
}

$logger->info('Listening on ' . AGENT_SOCKET);

$protocol = new Protocol($config['secret_key'] ?? '', $logger);

// Signal handlers
pcntl_async_signals(true);
$running = true;
pcntl_signal(SIGTERM, function () use (&$running, $logger) {
    $logger->info('SIGTERM received, shutting down');
    $running = false;
});
pcntl_signal(SIGINT, function () use (&$running, $logger) {
    $logger->info('SIGINT received, shutting down');
    $running = false;
});

// Main accept loop
while ($running) {
    $client = @stream_socket_accept($server, 1);
    if (!$client) {
        continue;
    }

    $raw = '';
    while (!feof($client)) {
        $chunk = fread($client, 8192);
        if ($chunk === false || $chunk === '') {
            break;
        }
        $raw .= $chunk;
        if (strpos($raw, "\n") !== false) {
            break;
        }
    }

    $response = $protocol->handle(trim($raw), $modules);
    fwrite($client, json_encode($response) . "\n");
    fclose($client);
}

@unlink(AGENT_SOCKET);
$logger->info('Agent stopped');
