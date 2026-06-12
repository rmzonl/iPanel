<?php namespace Project\Controllers;

use ZN\Controller;
use DB;
use Project\Libraries\Acl;
use Project\Models\ApiTokenModel;
use Project\Models\ClientModel;
use Project\Models\SiteModel;

/**
 * REST API — Bearer token ile kimlik doğrulama.
 *
 * Tüm metodlar JSON döndürür.
 * Initialize middleware'den API controller'ı public olarak işaretlenmiştir,
 * kimlik doğrulamayı bu controller kendi yapar.
 */
class Api extends Controller
{
    private ?array $apiUser = null;

    public function __construct()
    {
        $this->authenticate();
    }

    /** Doğrulanmış kullanıcı yoksa 401 döndür */
    private function authenticate()
    {
        $token = $this->getBearerToken();

        if (empty($token)) {
            $this->json(['error' => 'Unauthorized', 'message' => 'Bearer token gerekli.'], 401);
            exit;
        }

        $hash = hash('sha256', $token);
        $model = new ApiTokenModel();
        $record = $model->findByHash($hash);

        if (!$record) {
            $this->json(['error' => 'Unauthorized', 'message' => 'Geçersiz veya iptal edilmiş token.'], 401);
            exit;
        }

        // Sona erme kontrolü
        if (!empty($record->expires_at) && strtotime($record->expires_at) < time()) {
            $this->json(['error' => 'Unauthorized', 'message' => 'Token süresi dolmuş.'], 401);
            exit;
        }

        // Kullanıcıyı yükle
        $user = DB::table('users')->where('id', $record->user_id)->where('status', 1)->get()->row();
        if (!$user) {
            $this->json(['error' => 'Unauthorized', 'message' => 'Kullanıcı bulunamadı.'], 401);
            exit;
        }

        $model->touch((int) $record->id);

        $this->apiUser = [
            'id'          => (int) $user->id,
            'username'    => $user->username,
            'role'        => $user->role,
            'permissions' => $record->permissions ? json_decode($record->permissions, true) : null,
        ];
    }

    /** GET /api/clients */
    public function clients()
    {
        $this->requirePermission('clients.view');

        $model = new ClientModel();

        if ($this->apiUser['role'] === 'admin') {
            $rows = $model->getAll();
        } else {
            $rows = $model->getByReseller((int) $this->apiUser['id']);
        }

        $data = $rows ? $rows->result() : [];
        $this->json(['data' => $data, 'count' => count($data)]);
    }

    /** GET /api/sites */
    public function sites()
    {
        $this->requirePermission('sites.view');

        $model = new SiteModel();

        if ($this->apiUser['role'] === 'admin') {
            $rows = $model->getAll();
        } else {
            $rows = $model->getByReseller((int) $this->apiUser['id']);
        }

        $data = $rows ? $rows->result() : [];
        $this->json(['data' => $data, 'count' => count($data)]);
    }

    /** GET /api/me */
    public function me()
    {
        $this->json([
            'id'       => $this->apiUser['id'],
            'username' => $this->apiUser['username'],
            'role'     => $this->apiUser['role'],
        ]);
    }

    /** GET /api/status */
    public function status()
    {
        $this->json([
            'status'    => 'ok',
            'timestamp' => date('c'),
            'version'   => '0.3.0',
        ]);
    }

    // ---------------------------------------------------------------
    // Yardımcılar
    // ---------------------------------------------------------------

    private function requirePermission(string $permission)
    {
        // Token izin listesi varsa onu kontrol et, yoksa rol bazlı ACL
        $perms = $this->apiUser['permissions'];
        if ($perms !== null) {
            if (!in_array($permission, $perms, true) && !in_array('*', $perms, true)) {
                $this->json(['error' => 'Forbidden', 'message' => "Bu işlem için yetkiniz yok: $permission"], 403);
                exit;
            }
        } else {
            // Rol bazlı kontrol
            if (!Acl::can($permission, $this->apiUser)) {
                $this->json(['error' => 'Forbidden', 'message' => "Bu işlem için yetkiniz yok: $permission"], 403);
                exit;
            }
        }
    }

    private function getBearerToken(): ?string
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function json(mixed $data, int $status = 200)
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
