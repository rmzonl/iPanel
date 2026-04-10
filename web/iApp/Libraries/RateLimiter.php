<?php namespace Project\Libraries;

use DB;

/**
 * Login brute-force koruması — DB tabanlı
 */
class RateLimiter
{
    const MAX_ATTEMPTS    = 5;
    const WINDOW_SECONDS  = 900;  // 15 dakika
    const LOCKOUT_SECONDS = 1800; // 30 dakika

    /** Başarısız deneme kaydet. Blok varsa true döner. */
    public static function attempt(string $ip, string $username = ''): bool
    {
        // Eski kayıtları temizle
        static::cleanup();

        // Kaydet
        DB::insert('login_attempts', [
            'ip'           => $ip,
            'username'     => $username,
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);

        return static::isBlocked($ip);
    }

    /** IP şu an bloklu mu? */
    public static function isBlocked(string $ip): bool
    {
        $since = date('Y-m-d H:i:s', time() - self::WINDOW_SECONDS);

        $count = DB::table('login_attempts')
            ->where('ip', $ip)
            ->where('attempted_at >=', $since)
            ->get()
            ->totalRows();

        return $count >= self::MAX_ATTEMPTS;
    }

    /** Başarılı girişte IP'yi temizle */
    public static function clear(string $ip): void
    {
        DB::table('login_attempts')->where('ip', $ip)->delete();
    }

    /** Süresi geçmiş kayıtları sil */
    private static function cleanup(): void
    {
        $expire = date('Y-m-d H:i:s', time() - self::LOCKOUT_SECONDS);
        DB::table('login_attempts')->where('attempted_at <', $expire)->delete();
    }

    /** İstemci IP'sini güvenli şekilde al */
    public static function clientIp(): string
    {
        // Proxy arkasında gerçek IP
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
