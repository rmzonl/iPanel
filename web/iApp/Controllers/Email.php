<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use DB;
use Session;
use Redirect;
use Project\Libraries\Acl;
use Project\Libraries\CsrfGuard;
use Project\Libraries\AuditLogger;
use Project\Libraries\InputValidator;

class Email extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\EmailModel();
    }

    public function main(): void
    {
        $user   = Acl::user();
        $emails = ($user['role'] === 'admin')
            ? $this->model->getAll()
            : $this->model->getByReseller($user['id']);

        View::pageTitle('E-posta Hesapları');
        View::emails($emails);
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create(): void
    {
        $user      = Acl::user();
        $siteModel = new \Project\Models\SiteModel();
        $sites     = ($user['role'] === 'admin') ? $siteModel->getAll() : $siteModel->getByReseller($user['id']);

        View::pageTitle('Yeni E-posta Hesabı');
        View::sites($sites);
    }

    public function store(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('email/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('email/main'); return; }

        $siteId = (int) Post::site_id();
        Acl::requireOwnership(Acl::ownsSite($siteId));

        $username    = trim((string) Post::username());
        $domain      = trim((string) Post::domain());
        $rawPassword = (string) Post::password();

        $v = InputValidator::from(['username' => $username, 'domain' => $domain, 'site_id' => $siteId])
            ->required('site_id', 'Site')
            ->required('username', 'Kullanıcı adı')
            ->required('domain', 'Domain');

        if (empty($rawPassword)) {
            Session::insert('error', 'Şifre zorunludur.');
            Redirect::action('email/create');
            return;
        }

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('email/create');
            return;
        }

        $emailAddress = $username . '@' . $domain;

        $id = $this->model->create([
            'site_id'  => $siteId,
            'username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            'email'    => $emailAddress,
            'password' => password_hash($rawPassword, PASSWORD_BCRYPT),
            'quota'    => (int) (Post::quota() ?: 1024),
            'status'   => Post::status() === 'suspended' ? 'suspended' : 'active',
        ]);

        AuditLogger::log('email.create', 'email_account', $id, "E-posta hesabı oluşturuldu: $emailAddress");
        Session::insert('success', 'E-posta hesabı başarıyla oluşturuldu.');
        Redirect::action('email/main');
    }

    public function delete(int $id): void
    {
        Acl::requireOwnership(Acl::ownsSiteResource('email_accounts', $id));
        $account = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('email.delete', 'email_account', $id, 'E-posta hesabı silindi: ' . ($account->email ?? $id));
        Session::insert('success', 'E-posta hesabı başarıyla silindi.');
        Redirect::action('email/main');
    }
}
