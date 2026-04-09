<?php namespace Project\Libraries;

use ZN\Request\Http;
use Project\Libraries\CsrfGuard;

/**
 * AJAX / API için standart JSON yanıt yardımcısı.
 * ZN Framework'ün Json facade'ını kullanır (ZN\Protection\Json).
 *
 * Kullanım:
 *   JsonResponse::success('Site oluşturuldu.', ['site_id' => 5]);
 *   JsonResponse::error('Alan adı zaten var.');
 *   JsonResponse::job($uuid, 'Site oluşturma kuyruğa eklendi.');
 */
class JsonResponse
{
    /**
     * Başarılı yanıt gönder ve çık.
     *
     * @param string $message  Kullanıcıya gösterilecek mesaj
     * @param array  $data     Ek veri
     * @param string $redirect Yönlendirme URL'i (opsiyonel)
     */
    public static function success(string $message, array $data = [], string $redirect = ''): never
    {
        self::send(['success' => true, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /** Hata yanıtı gönder ve çık */
    public static function error(string $message, array $errors = [], int $httpCode = 422): never
    {
        Http::response($httpCode);
        self::send(['success' => false, 'message' => $message, 'errors' => $errors]);
    }

    /** Kuyruğa eklenen iş için yanıt */
    public static function job(string $uuid, string $message = 'İş kuyruğa eklendi.'): never
    {
        self::send(['success' => true, 'message' => $message, 'job_uuid' => $uuid, 'queued' => true]);
    }

    /** Sayfalanmış liste yanıtı */
    public static function list(array $items, int $total = 0, array $meta = []): never
    {
        self::send(['success' => true, 'items' => $items, 'total' => $total, 'meta' => $meta]);
    }

    /** İçerik HTML döndüren AJAX yanıtı (Import::usable ile hazırlanmış) */
    public static function html(string $html, string $message = ''): never
    {
        self::send(['success' => true, 'message' => $message, 'html' => $html]);
    }

    private static function send(array $payload): never
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }
        // Her yanıtta güncel CSRF token gönder (token rotasyonu için JS tarafında güncellenir)
        $payload['_csrf'] = CsrfGuard::token();
        echo \Json::encode($payload);
        exit;
    }
}
