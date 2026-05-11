<?php namespace Project\Models;

use DB;

class ApiTokenModel
{
    private const TABLE = 'api_tokens';

    public function getByUser(int $userId): array
    {
        return DB::table(self::TABLE)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get()->result() ?: [];
    }

    public function findByHash(string $hash): mixed
    {
        return DB::table(self::TABLE)
            ->where('token_hash', $hash)
            ->where('status', 'active')
            ->get()
            ->row();
    }

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

    public function touch(int $tokenId): void
    {
        DB::table(self::TABLE)
            ->where('id', $tokenId)
            ->update(['last_used' => date('Y-m-d H:i:s')]);
    }

    public function revoke(int $tokenId, int $userId): bool
    {
        $affected = DB::table(self::TABLE)
            ->where('id', $tokenId)
            ->where('user_id', $userId)
            ->update(['status' => 'revoked']);
        return (bool) $affected;
    }

    public function purgeExpired(): void
    {
        DB::table(self::TABLE)
            ->where('expires_at IS NOT NULL', null, false)
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->update(['status' => 'revoked']);
    }
}
