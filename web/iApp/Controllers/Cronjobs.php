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

class Cronjobs extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\CronjobModel();
    }

    public function main()
    {
        $user     = Acl::user();
        $cronjobs = ($user['role'] === 'admin')
            ? $this->model->getAll()
            : $this->model->getByReseller($user['id']);

        View::pageTitle('Cron İşleri');
        View::cronjobs($cronjobs);
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

        View::pageTitle('Yeni Cron İşi');
        View::sites($sites);
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('cronjobs/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('cronjobs/main'); return; }

        $siteId = (int) Post::site_id();
        Acl::requireOwnership(Acl::ownsSite($siteId));

        $raw = InputValidator::sanitize([
            'site_id'  => $siteId,
            'title'    => Post::title(),
            'command'  => Post::command(),
            'schedule' => Post::schedule(),
            'status'   => Post::status() ?: 'active',
        ]);

        $v = InputValidator::from($raw)
            ->required('site_id',  'Site')
            ->required('title',    'Başlık')
            ->required('command',  'Komut')
            ->required('schedule', 'Zamanlama')
            ->maxLength('title', 255, 'Başlık')
            ->in('status', ['active', 'inactive'], 'Durum');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('cronjobs/create');
            return;
        }

        $id = $this->model->create($raw);
        AuditLogger::log('cronjobs.create', 'cron_job', $id, "Cron oluşturuldu: {$raw['title']}");
        Session::insert('success', 'Cron işi başarıyla oluşturuldu.');
        Redirect::action('cronjobs/main');
    }

    public function delete(int $id)
    {
        Acl::requireOwnership(Acl::ownsSiteResource('cron_jobs', $id));
        $job = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('cronjobs.delete', 'cron_job', $id, 'Cron işi silindi: ' . ($job->title ?? $id));
        Session::insert('success', 'Cron işi başarıyla silindi.');
        Redirect::action('cronjobs/main');
    }
}
