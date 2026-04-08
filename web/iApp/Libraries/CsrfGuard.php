<?php namespace Project\Libraries;

use Session;

/**
 * CSRF koruması — session tabanlı tek kullanımlık token
 */
class CsrfGuard
{
    private const SESSION_KEY = '_csrf_token';
    private const FIELD_NAME  = '_csrf';
    private const TOKEN_TTL   = 3600; // 1 saat

    /** Yeni token üret (yoksa) ve döndür */
    public static function token(): string
    {
        $stored = Session::select(self::SESSION_KEY);

        if (empty($stored) || (time() - ($stored['time'] ?? 0)) > self::TOKEN_TTL) {
            $token = bin2hex(random_bytes(32));
            Session::insert(self::SESSION_KEY, ['token' => $token, 'time' => time()]);
            return $token;
        }

        return $stored['token'];
    }

    /** HTML hidden field */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    /** POST isteğindeki tokeni doğrula */
    public static function verify(): bool
    {
        if (!isset($_POST[self::FIELD_NAME])) return false;

        $stored = Session::select(self::SESSION_KEY);
        if (empty($stored['token'])) return false;

        if ((time() - ($stored['time'] ?? 0)) > self::TOKEN_TTL) {
            Session::delete(self::SESSION_KEY);
            return false;
        }

        $ok = hash_equals($stored['token'], $_POST[self::FIELD_NAME]);

        // Token'ı yenile (sliding window)
        if ($ok) {
            $newToken = bin2hex(random_bytes(32));
            Session::insert(self::SESSION_KEY, ['token' => $newToken, 'time' => time()]);
        }

        return $ok;
    }

    /** Form adı (view'larda $_POST key için) */
    public static function fieldName(): string
    {
        return self::FIELD_NAME;
    }
}
