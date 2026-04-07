<?php
namespace iPanel\Agent\Common;

/**
 * JSON-RPC protocol with HMAC-SHA256 signature verification.
 *
 * Request format (raw line over Unix socket):
 *   { "action": "nginx.reload", "params": {...}, "ts": 123, "sig": "..." }
 *
 * Signature = HMAC-SHA256(secret, action + "|" + json(params) + "|" + ts)
 * Timestamp window: ±300 seconds
 */
class Protocol
{
    private string $secret;
    private Logger $logger;
    private array  $rateLimit = [];
    private int    $rateMax   = 30;
    private int    $rateWindow = 10;

    public function __construct(string $secret, Logger $logger)
    {
        $this->secret = $secret;
        $this->logger = $logger;
    }

    public function handle(string $raw, array $modules): array
    {
        $req = json_decode($raw, true);
        if (!is_array($req)) {
            return $this->err('invalid_json');
        }

        $action = $req['action'] ?? '';
        $params = $req['params'] ?? [];
        $ts     = (int) ($req['ts'] ?? 0);
        $sig    = $req['sig']    ?? '';

        if (!$action || !$sig) {
            return $this->err('missing_fields');
        }

        // Timestamp window
        if (abs(time() - $ts) > 300) {
            return $this->err('stale_request');
        }

        // HMAC verify
        $expected = hash_hmac(
            'sha256',
            $action . '|' . json_encode($params) . '|' . $ts,
            $this->secret
        );
        if (!hash_equals($expected, $sig)) {
            $this->logger->warn("HMAC mismatch for action=$action");
            return $this->err('invalid_signature');
        }

        // Rate limit
        if (!$this->checkRate($action)) {
            return $this->err('rate_limited');
        }

        // Dispatch: action = "module.method"
        if (strpos($action, '.') === false) {
            return $this->err('invalid_action_format');
        }
        [$moduleName, $method] = explode('.', $action, 2);

        if (!isset($modules[$moduleName])) {
            return $this->err('unknown_module');
        }

        $module = $modules[$moduleName];
        if (!$module->isAllowed($method)) {
            return $this->err('method_not_whitelisted');
        }

        try {
            $result = $module->$method($params);
            $this->logger->info("OK {$action}");
            return ['status' => 'ok', 'data' => $result];
        } catch (\Throwable $e) {
            $this->logger->error("{$action}: " . $e->getMessage());
            return $this->err('exception', $e->getMessage());
        }
    }

    private function checkRate(string $action): bool
    {
        $now = time();
        $this->rateLimit = array_filter(
            $this->rateLimit,
            fn($t) => $t > $now - $this->rateWindow
        );
        if (count($this->rateLimit) >= $this->rateMax) {
            $this->logger->warn("Rate limit hit on {$action}");
            return false;
        }
        $this->rateLimit[] = $now;
        return true;
    }

    private function err(string $code, string $detail = ''): array
    {
        return ['status' => 'error', 'code' => $code, 'detail' => $detail];
    }
}
