<?php namespace Project\Libraries;

use DB;

/**
 * Arka plan iş kuyruğu yöneticisi.
 *
 * Kullanım:
 *   JobQueue::push('sites.create', ['domain' => 'example.com', ...]);
 *   JobQueue::push('ssl.issue',    ['site_id' => 5], priority: 1);
 */
class JobQueue
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_RUNNING   = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Kuyruğa yeni bir iş ekle. UUID döndürür.
     *
     * @param string $type     İş türü ('sites.create', 'ssl.issue', vb.)
     * @param array  $payload  Parametre dizisi
     * @param int    $priority 1 (yüksek) … 9 (düşük), varsayılan 5
     * @param int    $userId   Kuyruğa ekleyen kullanıcı ID'si
     * @param string $username Kuyruğa ekleyen kullanıcı adı
     * @return string UUID
     */
    public static function push(
        string $type,
        array  $payload,
        int    $priority = 5,
        int    $userId   = 0,
        string $username = 'system'
    ): string {
        $uuid = self::generateUuid();
        DB::table('jobs')->insert([
            'uuid'        => $uuid,
            'type'        => $type,
            'payload'     => \Json::encode($payload),
            'status'      => self::STATUS_PENDING,
            'priority'    => max(1, min(9, $priority)),
            'user_id'     => $userId ?: null,
            'username'    => $username,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        return $uuid;
    }

    /** Belirli bir işin güncel durumunu döndür */
    public static function status(string $uuid): ?object
    {
        return DB::table('jobs')->where('uuid', $uuid)->get()->row() ?: null;
    }

    /** Kullanıcının işlerini listele */
    public static function listForUser(int $userId, int $limit = 50): array
    {
        return DB::table('jobs')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->result() ?: [];
    }

    /** Admin: tüm işleri listele */
    public static function listAll(string $status = '', int $limit = 100): array
    {
        $q = DB::table('jobs')->orderBy('created_at', 'DESC')->limit($limit);
        if ($status !== '') $q->where('status', $status);
        return $q->get()->result() ?: [];
    }

    /** Çalışan/bekleyen iş sayısı */
    public static function activeCount(): int
    {
        $row = DB::table('jobs')
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_RUNNING])
            ->selectCount('id', 'cnt')
            ->get()
            ->row();
        return (int)($row->cnt ?? 0);
    }

    /** İptal et */
    public static function cancel(string $uuid): bool
    {
        $job = DB::table('jobs')->where('uuid', $uuid)->get()->row();
        if (!$job || $job->status === self::STATUS_RUNNING) return false;
        DB::table('jobs')->where('uuid', $uuid)->update(['status' => self::STATUS_CANCELLED]);
        return true;
    }

    /** Tamamlanan/başarısız eski işleri temizle (30 günden eski) */
    public static function prune(int $days = 30): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        DB::table('jobs')
            ->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED])
            ->where('created_at <', $cutoff)
            ->delete();
        return DB::affectedRows();
    }

    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
