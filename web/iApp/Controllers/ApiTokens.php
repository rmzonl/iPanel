<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use Session;
use Redirect;
use Project\Libraries\Acl;
use Project\Libraries\CsrfGuard;
use Project\Libraries\AuditLogger;
use Project\Libraries\InputValidator;
use Project\Models\ApiTokenModel;

class ApiTokens extends Controller
{
    private ApiTokenModel $model;

    public function __construct()
    {
        $this->model = new ApiTokenModel();
    }

    public function main(): void
    {
        $this->model->purgeExpired();

        View::pageTitle('API Token\'ları');
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function rows(): void
    {
        $user = Acl::user();
        $data = $this->model->getByUser($user['id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $data]);
        exit;
    }

    public function create(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('apitokens/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('apitokens/main'); return; }

        $user = Acl::user();

        $v = InputValidator::from($_POST)
            ->required('name', 'Token Adı')
            ->maxLength('name', 100, 'Token Adı');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('apitokens/main');
            return;
        }

        $data    = InputValidator::sanitize($_POST);
        $name    = $data['name'] ?? '';
        $expDays = (int) ($data['expires_days'] ?? 0);

        $rawToken  = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);

        $expiresAt = $expDays > 0
            ? date('Y-m-d H:i:s', strtotime("+$expDays days"))
            : null;

        $perms = null;

        $id = $this->model->create($user['id'], $name, $tokenHash, $perms, $expiresAt);

        AuditLogger::log('apitoken.create', 'api_tokens', $id, "Yeni API token: $name");

        Session::insert('new_token', $rawToken);
        Session::insert('success', "Token oluşturuldu. Lütfen kopyalayın — bir daha gösterilmeyecek.");
        Redirect::action('apitokens/main');
    }

    public function revoke(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('apitokens/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('apitokens/main'); return; }

        $user = Acl::user();
        $id   = (int) Post::get('token_id');

        if ($id <= 0) { Session::insert('error', 'Geçersiz token.'); Redirect::action('apitokens/main'); return; }

        $ok = $this->model->revoke($id, $user['id']);

        if ($ok) {
            AuditLogger::log('apitoken.revoke', 'api_tokens', $id, "API token iptal edildi");
            Session::insert('success', 'Token iptal edildi.');
        } else {
            Session::insert('error', 'Token bulunamadı veya yetkiniz yok.');
        }

        Redirect::action('apitokens/main');
    }
}
