<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Request\Get;
use ZN\Inclusion\Project\View;
use Import;
use Session;
use Project\Libraries\JobQueue;
use Project\Libraries\JsonResponse;

/**
 * İş kuyruğu yönetim ekranı ve AJAX endpoint'leri.
 *
 * GET  /jobs           → kuyruk listesi (view)
 * GET  /jobs/status    → JSON: belirli işin durumu (?uuid=...)
 * GET  /jobs/active    → JSON: aktif iş sayısı + son N iş (sidebar widget)
 * POST /jobs/cancel    → İş iptali
 */
class Jobs extends Controller
{
    /** Kuyruk yönetim sayfası */
    public function main(): void
    {
        $user   = Session::select('admin_user');
        $isAdmin = ($user['role'] ?? '') === 'admin';

        $filter = Get::get('status') ?? '';
        $jobs   = $isAdmin
            ? JobQueue::listAll($filter, 200)
            : JobQueue::listForUser((int)$user['id'], 100);

        View::jobs($jobs);
        View::isAdmin($isAdmin);
        View::activeCount(JobQueue::activeCount());
    }

    /** AJAX: tek işin durumu */
    public function status(): void
    {
        $uuid = Get::get('uuid') ?? '';
        if (!$uuid) JsonResponse::error('UUID gerekli.', [], 400);

        $job = JobQueue::status($uuid);
        if (!$job) JsonResponse::error('İş bulunamadı.', [], 404);

        // Yetki: sadece kendi işini görebilir (admin hariç)
        $user = Session::select('admin_user');
        if (($user['role'] ?? '') !== 'admin' && (int)$job->user_id !== (int)$user['id']) {
            JsonResponse::error('Yetkisiz erişim.', [], 403);
        }

        JsonResponse::success('', [
            'uuid'         => $job->uuid,
            'type'         => $job->type,
            'status'       => $job->status,
            'result'       => $job->result ? \Json::decodeArray($job->result) : null,
            'created_at'   => $job->created_at,
            'started_at'   => $job->started_at,
            'completed_at' => $job->completed_at,
        ]);
    }

    /** AJAX: sidebar widget verisi — aktif + son 10 iş */
    public function active(): void
    {
        $user    = Session::select('admin_user');
        $isAdmin = ($user['role'] ?? '') === 'admin';

        $jobs = $isAdmin
            ? JobQueue::listAll('', 15)
            : JobQueue::listForUser((int)$user['id'], 15);

        $simplified = array_map(fn($j) => [
            'uuid'       => $j->uuid,
            'type'       => $j->type,
            'status'     => $j->status,
            'created_at' => $j->created_at,
        ], $jobs);

        JsonResponse::success('', [
            'active_count' => JobQueue::activeCount(),
            'jobs'         => $simplified,
        ]);
    }

    /**
     * AJAX GET: tablo satırlarını Import::usable ile wizard.php'den al.
     * JS tarafında innerHTML ile inject edilir.
     */
    public function refreshRows(): void
    {
        $user    = Session::select('admin_user');
        $isAdmin = ($user['role'] ?? '') === 'admin';
        $filter  = Get::get('status') ?? '';

        $jobs = $isAdmin
            ? JobQueue::listAll($filter, 200)
            : JobQueue::listForUser((int)$user['id'], 100);

        View::jobs($jobs);

        // Import::usable → wizard.php dosyasını yükle, HTML olarak döndür
        $html = Import::usable(true)->page('Jobs/rows');
        JsonResponse::html($html);
    }

    /** AJAX POST: iptal */
    public function cancel(): void
    {
        if (!Http::isRequestMethod('post')) {
            JsonResponse::error('POST gerekli.', [], 405);
        }
        $uuid = Post::get('uuid') ?? '';
        if (!$uuid) JsonResponse::error('UUID gerekli.', [], 400);

        $job  = JobQueue::status($uuid);
        $user = Session::select('admin_user');
        if (!$job || (($user['role'] ?? '') !== 'admin' && (int)$job->user_id !== (int)$user['id'])) {
            JsonResponse::error('İş bulunamadı veya yetkisiz.', [], 403);
        }

        if (JobQueue::cancel($uuid)) {
            JsonResponse::success('İş iptal edildi.');
        } else {
            JsonResponse::error('Çalışmakta olan iş iptal edilemez.');
        }
    }
}
