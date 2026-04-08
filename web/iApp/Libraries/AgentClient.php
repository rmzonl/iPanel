<?php
namespace Project\Libraries;

/**
 * Web UI → iPanel Agent client.
 *
 * Sends signed JSON-RPC requests over the agent's Unix socket.
 * Used by controllers when they need to perform a privileged action.
 *
 * Usage:
 *   $agent = new AgentClient();
 *   $r = $agent->call('nginx.reload');
 *   $r = $agent->call('php.createPool', ['site_username' => 'foo', 'version' => '8.2']);
 */
class AgentClient
{
    private string $socket;
    private string $secret;
    private int    $timeout;

    public function __construct(?string $socket = null, ?string $secret = null, int $timeout = 10)
    {
        $this->socket  = $socket ?: '/run/ipanel/agent.sock';
        $this->secret  = $secret ?: $this->loadSecret();
        $this->timeout = $timeout;
    }

    public function call(string $action, array $params = []): array
    {
        $ts  = time();
        $sig = hash_hmac(
            'sha256',
            $action . '|' . json_encode($params) . '|' . $ts,
            $this->secret
        );

        $payload = json_encode([
            'action' => $action,
            'params' => $params,
            'ts'     => $ts,
            'sig'    => $sig,
        ]) . "\n";

        $sock = @stream_socket_client(
            'unix://' . $this->socket,
            $errno,
            $errstr,
            $this->timeout
        );
        if (!$sock) {
            throw new \RuntimeException("agent unreachable: $errstr");
        }
        stream_set_timeout($sock, $this->timeout);

        fwrite($sock, $payload);

        $resp = '';
        while (!feof($sock)) {
            $chunk = fread($sock, 8192);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $resp .= $chunk;
        }
        fclose($sock);

        $decoded = \Json::decode(trim($resp), true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('agent returned invalid JSON');
        }
        if (($decoded['status'] ?? '') !== 'ok') {
            $msg = $decoded['code'] ?? 'unknown_error';
            if (!empty($decoded['detail'])) {
                $msg .= ': ' . $decoded['detail'];
            }
            throw new \RuntimeException("agent error: $msg");
        }
        return $decoded['data'] ?? [];
    }

    public function ping(): bool
    {
        try {
            $this->call('system.uptime');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function loadSecret(): string
    {
        $path = '/etc/ipanel/agent.conf.php';
        if (!file_exists($path) || !is_readable($path)) {
            throw new \RuntimeException('cannot read agent config');
        }
        $cfg = require $path;
        return $cfg['secret_key'] ?? '';
    }
}
