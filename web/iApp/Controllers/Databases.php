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

class Databases extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\DatabaseModel();
    }

    public function main()
    {
        $user = Acl::user();
        $dbs  = ($user['role'] === 'admin')
            ? $this->model->getAll()
            : $this->model->getByReseller($user['id']);

        View::pageTitle('Veritabanları');
        View::databases($dbs);
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $user      = Acl::user();
        $siteModel = new \Project\Models\SiteModel();
        $sites     = ($user['role'] === 'admin') ? $siteModel->getAll() : $siteModel->getByReseller($user['id']);

        View::pageTitle('Yeni Veritabanı');
        View::sites($sites);
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('databases/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('databases/main'); return; }

        $siteId = (int) Post::site_id();
        Acl::requireOwnership(Acl::ownsSite($siteId));

        $rawPassword = (string) Post::db_password();

        $raw = InputValidator::sanitize([
            'site_id'  => $siteId,
            'db_name'  => Post::db_name(),
            'db_user'  => Post::db_user(),
            'charset'  => Post::charset() ?: 'utf8mb4',
            'status'   => Post::status() ?: 'active',
        ]);

        $v = InputValidator::from($raw)
            ->required('site_id', 'Site')
            ->required('db_name', 'Veritabanı adı')
            ->required('db_user', 'Kullanıcı adı')
            ->maxLength('db_name', 64, 'Veritabanı adı')
            ->maxLength('db_user', 64, 'Kullanıcı adı')
            ->in('status', ['active', 'suspended'], 'Durum');

        if (empty($rawPassword)) {
            Session::insert('error', 'Şifre zorunludur.');
            Redirect::action('databases/create');
            return;
        }

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('databases/create');
            return;
        }

        $raw['db_password'] = password_hash($rawPassword, PASSWORD_BCRYPT);

        $id = $this->model->create($raw);
        AuditLogger::log('databases.create', 'database', $id, "Veritabanı oluşturuldu: {$raw['db_name']}");
        Session::insert('success', 'Veritabanı başarıyla oluşturuldu.');
        Redirect::action('databases/main');
    }

    public function delete(int $id)
    {
        Acl::requireOwnership(Acl::ownsSiteResource('site_databases', $id));
        $db = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('databases.delete', 'database', $id, 'Veritabanı silindi: ' . ($db?->db_name ?? $id));
        Session::insert('success', 'Veritabanı başarıyla silindi.');
        Redirect::action('databases/main');
    }
}
