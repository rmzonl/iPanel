<?php namespace Project\Models;

use DB;

class ApiTokenModel
{
    private const TABLE = 'api_tokens';

    /** Kullanıcının aktif token'larını listele */
    public function getByUser(int $userId): mixed
    {
        return DB::table(self::TABLE)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /** Token hash ile token kaydını bul */
    public function findByHash(string $hash): mixed
    {
        return DB::table(self::TABLE)
            ->where('token_hash', $hash)
            ->where('status', 'active')
            ->get()
            ->row();
    }

    /** Yeni token oluştur */
    public function create(int $userId, string $name, string $tokenHash, ?array $permissions, ?string $expiresAt): int
    {
        DB::table(self::TABLE)->insert([
            'user_id'     => $userId,
            'name'        => $name,
            'token_hash'  => $tokenHash,
            'permissions' => $permissions ? json_encode($permissions) : null,
            'expires_at'  => $expiresAt,
            'status'      => 'active',
        ]);
        return (int) DB::pdo()->lastInsertId();
    }

    /** Token son kullanım zamanını güncelle */
    public function touch(int $tokenId): void
    {
        DB::table(self::TABLE)
            ->where('id', $tokenId)
            ->update(['last_used' => date('Y-m-d H:i:s')]);
    }

    /** Token'ı iptal et */
    public function revoke(int $tokenId, int $userId): bool
    {
        $affected = DB::table(self::TABLE)
            ->where('id', $tokenId)
            ->where('user_id', $userId) // sahiplik kontrolü
            ->update(['status' => 'revoked']);
        return (bool) $affected;
    }

    /** Tüm süresi dolmuş token'ları temizle */
    public function purgeExpired(): void
    {
        DB::table(self::TABLE)
            ->where('expires_at IS NOT NULL', null, false)
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->update(['status' => 'revoked']);
    }
}
