<?php namespace Project\Libraries;

use DB;
use Session;
use Project\Libraries\RateLimiter;

/**
 * Denetim logu — kim, ne zaman, ne yaptı
 */
class AuditLogger
{
    /**
     * @param string      $action       Örn: 'clients.create', 'sites.delete'
     * @param string|null $resourceType Örn: 'client', 'site', 'domain'
     * @param int|null    $resourceId   Kaynağın DB ID'si
     * @param string|null $description  İnsan okunur açıklama
     */
    public static function log(
        string  $action,
        ?string $resourceType = null,
        ?int    $resourceId   = null,
        ?string $description  = null
    ): void {
        try {
            $user = Session::select('admin_user');

            DB::table('audit_logs')->insert([
                'user_id'       => $user['id']       ?? null,
                'username'      => $user['username']  ?? 'system',
                'action'        => $action,
                'resource_type' => $resourceType,
                'resource_id'   => $resourceId,
                'description'   => $description,
                'ip'            => RateLimiter::clientIp(),
                'user_agent'    => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Loglama hatası asla ana akışı durdurmamalı
            error_log('AuditLogger error: ' . $e->getMessage());
        }
    }
}
