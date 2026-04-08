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

class Ftp extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\FtpModel();
    }

    public function main(): void
    {
        $user        = Acl::user();
        $ftpAccounts = ($user['role'] === 'admin')
            ? $this->model->getAll()
            : $this->model->getByReseller($user['id']);

        View::pageTitle('FTP Hesapları');
        View::ftpAccounts($ftpAccounts);
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

        View::pageTitle('Yeni FTP Hesabı');
        View::sites($sites);
    }

    public function store(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('ftp/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('ftp/main'); return; }

        $siteId = (int) Post::get('site_id');
        Acl::requireOwnership(Acl::ownsSite($siteId));

        $username    = trim((string) Post::get('username'));
        $rawPassword = (string) Post::get('password');

        $v = InputValidator::from(['site_id' => $siteId, 'username' => $username])
            ->required('site_id', 'Site')
            ->required('username', 'Kullanıcı adı')
            ->maxLength('username', 100, 'Kullanıcı adı');

        if (empty($rawPassword)) {
            Session::insert('error', 'Şifre zorunludur.');
            Redirect::action('ftp/create');
            return;
        }

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('ftp/create');
            return;
        }

        $id = $this->model->create([
            'site_id'  => $siteId,
            'username' => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            'password' => password_hash($rawPassword, PASSWORD_BCRYPT),
            'home_dir' => htmlspecialchars(trim((string) Post::get('home_dir')), ENT_QUOTES, 'UTF-8'),
            'quota'    => (int) (Post::get('quota') ?: 0),
            'status'   => Post::get('status') === 'suspended' ? 'suspended' : 'active',
        ]);

        AuditLogger::log('ftp.create', 'ftp_account', $id, "FTP hesabı oluşturuldu: $username");
        Session::insert('success', 'FTP hesabı başarıyla oluşturuldu.');
        Redirect::action('ftp/main');
    }

    public function delete(int $id): void
    {
        Acl::requireOwnership(Acl::ownsSiteResource('ftp_accounts', $id));
        $account = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('ftp.delete', 'ftp_account', $id, 'FTP hesabı silindi: ' . ($account->username ?? $id));
        Session::insert('success', 'FTP hesabı başarıyla silindi.');
        Redirect::action('ftp/main');
    }
}
