<?php namespace Project\Libraries;

/**
 * RFC 6238 TOTP — Google Authenticator uyumlu 2FA
 *
 * Dış bağımlılık yok; saf PHP ile HMAC-SHA1 tabanlı TOTP.
 */
class Totp
{
    private const DIGITS    = 6;
    private const STEP      = 30;   // saniye
    private const TOLERANCE = 1;    // ±1 step (±30 sn)

    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    // ---------------------------------------------------------------
    // Public API
    // ---------------------------------------------------------------

    /** Yeni, cryptographic olarak rastgele bir Base32 secret üret */
    public static function generateSecret(int $length = 20): string
    {
        $random = random_bytes($length);
        return static::base32Encode($random);
    }

    /** Mevcut 6 haneli TOTP kodunu döndür */
    public static function getCode(string $secret): string
    {
        return static::compute($secret, (int) floor(time() / static::STEP));
    }

    /**
     * Kullanıcının girdiği kodu doğrula.
     * ±TOLERANCE time step toleransı uygulanır (ağ gecikmesi için).
     */
    public static function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (strlen($code) !== static::DIGITS || !ctype_digit($code)) {
            return false;
        }

        $step = (int) floor(time() / static::STEP);
        for ($i = -static::TOLERANCE; $i <= static::TOLERANCE; $i++) {
            if (hash_equals(static::compute($secret, $step + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * otpauth:// URI — QR kod oluşturmak için kullanılır.
     *
     * @param string $secret  Base32 secret
     * @param string $account Kullanıcı adı / e-posta
     * @param string $issuer  Uygulama adı
     */
    public static function otpUri(string $secret, string $account, string $issuer = 'iPanel'): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($account);
        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            $label,
            $secret,
            rawurlencode($issuer),
            static::DIGITS,
            static::STEP
        );
    }

    /**
     * 8 adet tek kullanımlık yedek kod üret.
     * Döndürülen dizi: ['XXXX-XXXX', ...] — DB'ye hash'lenmiş hali kaydedilmeli.
     */
    public static function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $raw    = bin2hex(random_bytes(4)); // 8 hex karakter
            $codes[] = strtoupper(substr($raw, 0, 4) . '-' . substr($raw, 4, 4));
        }
        return $codes;
    }

    /**
     * Yedek kodları DB'ye kaydetmek için JSON olarak hash'le.
     * Her kod bcrypt ile hash'lenir.
     */
    public static function hashBackupCodes(array $codes): string
    {
        $hashed = array_map(fn($c) => password_hash($c, PASSWORD_BCRYPT, ['cost' => 10]), $codes);
        return json_encode($hashed);
    }

    /**
     * Kullanıcının girdiği yedek kodu DB'deki hash listesiyle karşılaştır.
     * Doğru kodu listeden çıkarır (tek kullanım) ve güncellenmiş JSON döndürür.
     * Eşleşme yoksa null döndürür.
     */
    public static function verifyBackupCode(string $input, string $jsonHashes): ?string
    {
        $input  = strtoupper(preg_replace('/\s+/', '', $input));
        $hashes = json_decode($jsonHashes, true) ?? [];

        foreach ($hashes as $idx => $hash) {
            if (password_verify($input, $hash)) {
                unset($hashes[$idx]);
                return json_encode(array_values($hashes));
            }
        }
        return null;
    }

    // ---------------------------------------------------------------
    // Private helpers
    // ---------------------------------------------------------------

    /** RFC 6238 TOTP hesabı */
    private static function compute(string $secret, int $counter): string
    {
        $key     = static::base32Decode($secret);
        $msg     = pack('N*', 0) . pack('N*', $counter);
        $hash    = hash_hmac('sha1', $msg, $key, true);
        $offset  = ord($hash[strlen($hash) - 1]) & 0x0F;
        $otp     = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
            ( ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** static::DIGITS);

        return str_pad((string) $otp, static::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Base32 → binary */
    private static function base32Decode(string $base32): string
    {
        $base32  = strtoupper(rtrim($base32, '='));
        $buffer  = 0;
        $bits    = 0;
        $output  = '';

        foreach (str_split($base32) as $char) {
            $pos = strpos(static::BASE32_CHARS, $char);
            if ($pos === false) continue;
            $buffer = ($buffer << 5) | $pos;
            $bits  += 5;
            if ($bits >= 8) {
                $bits   -= 8;
                $output .= chr(($buffer >> $bits) & 0xFF);
            }
        }
        return $output;
    }

    /** binary → Base32 */
    private static function base32Encode(string $data): string
    {
        $output = '';
        $buffer = 0;
        $bits   = 0;

        foreach (str_split($data) as $byte) {
            $buffer = ($buffer << 8) | ord($byte);
            $bits  += 8;
            while ($bits >= 5) {
                $bits   -= 5;
                $output .= static::BASE32_CHARS[($buffer >> $bits) & 0x1F];
            }
        }
        if ($bits > 0) {
            $output .= static::BASE32_CHARS[($buffer << (5 - $bits)) & 0x1F];
        }
        return $output;
    }
}
