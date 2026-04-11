<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Get;
use Session;
use Project\Libraries\AgentClient;
use Project\Libraries\JsonResponse;

/**
 * Canlı sistem istatistikleri endpoint'leri.
 *
 * GET /stats/snapshot  → JSON anlık görüntü (tek seferlik)
 * GET /stats/stream    → SSE: tarayıcıya ~3sn'de bir veri iter
 * GET /stats/services  → JSON servis durumları
 */
class Stats extends Controller
{
    /** Tek seferlik JSON anlık görüntü */
    public function snapshot()
    {
        $data = $this->callAgent('Stats', 'snapshot', []);
        JsonResponse::success('', $data);
    }

    /** Servis durumları */
    public function services()
    {
        $data = $this->callAgent('Stats', 'services', []);
        JsonResponse::success('', $data);
    }

    /**
     * Server-Sent Events akışı.
     *
     * Tarayıcı:
     *   const es = new EventSource('/stats/stream');
     *   es.addEventListener('stats', e => { const d = JSON.parse(e.data); ... });
     *   es.addEventListener('error', e => { ... });
     */
    public function stream()
    {
        // Tüm output buffer seviyelerini kapat (ZN Framework birden fazla açar)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Session kilidini bırak — uzun süren SSE diğer istekleri bloklamasın
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-store');
        header('X-Accel-Buffering: no');   // nginx proxy buffering'i kapat
        header('Connection: keep-alive');

        // PHP zaman aşımı yok — nginx'te fastcgi_read_timeout ile sınırla
        set_time_limit(0);
        ignore_user_abort(false);

        $interval  = max(2, (int)(Get::get('interval') ?? 3)); // min 2 sn
        $maxCycles = 200;  // ~10 dakika sonra SSE kesilir, JS yeniden bağlanır
        $cycle     = 0;

        // İlk heartbeat
        echo 'event: connected' . "\n" . 'data: ' . \Json::encode(['ts' => time()]) . "\n\n";
        flush();

        while (!connection_aborted() && $cycle < $maxCycles) {
            $cycle++;
            $start = microtime(true);

            try {
                $data = $this->callAgent('Stats', 'snapshot', []);
                $json = \Json::encode(['cycle' => $cycle] + $data);
                echo "event: stats\ndata: {$json}\n\n";
            } catch (\Throwable $e) {
                $err = \Json::encode(['error' => $e->getMessage()]);
                echo "event: error\ndata: {$err}\n\n";
            }

            flush();

            $elapsed = microtime(true) - $start;
            $sleep   = max(0, $interval - $elapsed);
            if ($sleep > 0) usleep((int)($sleep * 1_000_000));
        }

        // SSE döngüsü bitti — JS otomatik yeniden bağlanır
        echo "event: end\ndata: {\"reason\":\"cycle_limit\"}\n\n";
        flush();
        // ZN Framework masterpage render'ını engelle (text/html header baskısı)
        exit(0);
    }

    // ────────────────────────────────────────
    private function callAgent(string $module, string $method, array $params): array
    {
        $config = require '/etc/ipanel/agent.conf.php';
        $client = new AgentClient('/run/ipanel/agent.sock', $config['secret_key']);
        return $client->call(strtolower($module) . '.' . $method, $params);
    }
}
