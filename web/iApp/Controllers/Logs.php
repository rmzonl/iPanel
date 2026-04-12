<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Get;
use ZN\Inclusion\Project\View;
use Session;
use Project\Libraries\Acl;
use Project\Libraries\JsonResponse;

/**
 * Log izleme: ZN framework logları ve sistem logları.
 *
 * GET /logs          → HTML log izleme sayfası
 * GET /logs/data     → JSON: mevcut log dosyaları + içerik (AJAX)
 */
class Logs extends Controller
{
    private const ZN_LOG_DIR  = STORAGE_DIR . 'Logs/';
    private const SYS_LOG_DIR = '/var/log/ipanel/';
    private const MAX_LINES   = 500;

    public function main()
    {
        View::pageTitle('Log İzleme');
        View::znLogFiles($this->listLogFiles(self::ZN_LOG_DIR));
        View::sysLogFiles($this->listLogFiles(self::SYS_LOG_DIR));
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    /** JSON endpoint: log dosyası içeriği */
    public function data()
    {
        $source = Get::source() ?? 'zn';  // 'zn' veya 'sys'
        $file   = basename(Get::file() ?? '');

        if (empty($file)) {
            JsonResponse::error('Dosya adı gerekli.');
            return;
        }

        $dir     = ($source === 'sys') ? self::SYS_LOG_DIR : self::ZN_LOG_DIR;
        $logPath = $dir . $file;

        if (!preg_match('/^[\w\-\.]+\.log$/', $file) || !is_file($logPath)) {
            JsonResponse::error('Geçersiz log dosyası.');
            return;
        }

        $lines   = $this->tailFile($logPath, self::MAX_LINES);
        $entries = $this->parseLines($lines, $source);

        JsonResponse::success('', [
            'file'    => $file,
            'source'  => $source,
            'entries' => $entries,
            'total'   => count($entries),
        ]);
    }

    /** Dizindeki .log dosyalarını listele */
    private function listLogFiles(string $dir): array
    {
        if (!is_dir($dir)) return [];

        $files = [];
        foreach (glob($dir . '*.log') ?: [] as $path) {
            $files[] = [
                'name'    => basename($path),
                'size'    => filesize($path),
                'mtime'   => filemtime($path),
            ];
        }

        usort($files, fn($a, $b) => $b['mtime'] - $a['mtime']);
        return $files;
    }

    /** Dosyanın son N satırını oku */
    private function tailFile(string $path, int $n): array
    {
        $fp    = fopen($path, 'r');
        if (!$fp) return [];

        $buffer  = '';
        $pos     = fseek($fp, 0, SEEK_END);
        $size    = ftell($fp);
        $chunk   = 4096;
        $lines   = [];

        while (ftell($fp) > 0 && count($lines) <= $n) {
            $offset = min($chunk, ftell($fp));
            fseek($fp, -$offset, SEEK_CUR);
            $buffer = fread($fp, $offset) . $buffer;
            fseek($fp, -$offset, SEEK_CUR);
            $lines  = explode("\n", $buffer);
        }

        fclose($fp);

        $lines = array_filter(array_slice($lines, -$n), fn($l) => $l !== '');
        return array_values($lines);
    }

    /** ZN log satırlarını parse et (veya ham döndür) */
    private function parseLines(array $lines, string $source): array
    {
        if ($source === 'sys') {
            return array_map(fn($l) => ['raw' => $l], $lines);
        }

        // ZN format: "IP: x.x.x.x | Subject: X | Date: D.M.Y H:i:s | Message: ..."
        $entries = [];
        foreach ($lines as $line) {
            if (preg_match('/IP:\s*(\S+)\s*\|\s*Subject:\s*([^|]+)\|\s*Date:\s*([^|]+)\|\s*Message:\s*(.+)/', $line, $m)) {
                $entries[] = [
                    'ip'      => trim($m[1]),
                    'subject' => trim($m[2]),
                    'date'    => trim($m[3]),
                    'message' => trim($m[4]),
                    'raw'     => $line,
                ];
            } else {
                $entries[] = ['raw' => $line];
            }
        }
        return $entries;
    }
}
