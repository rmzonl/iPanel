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

class Backups extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\BackupModel();
    }

    public function main()
    {
        $user    = Acl::user();
        $backups = ($user['role'] === 'admin')
            ? $this->model->getAll()
            : $this->model->getByReseller($user['id']);

        $siteModel   = new \Project\Models\SiteModel();
        $clientModel = new \Project\Models\ClientModel();

        $sites   = ($user['role'] === 'admin') ? $siteModel->getAll()   : $siteModel->getByReseller($user['id']);
        $clients = ($user['role'] === 'admin') ? $clientModel->getAll() : $clientModel->getByReseller($user['id']);

        View::pageTitle('Yedeklemeler');
        View::backups($backups);
        View::sites($sites);
        View::clients($clients);
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('backups/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('backups/main'); return; }

        $siteId   = Post::get('site_id')   ? (int) Post::get('site_id')   : null;
        $clientId = Post::get('client_id') ? (int) Post::get('client_id') : null;

        // Sahiplik kontrolü
        if ($siteId)   Acl::requireOwnership(Acl::ownsSite($siteId));
        if ($clientId) Acl::requireOwnership(Acl::ownsClient($clientId));

        $type = InputValidator::sanitize(['type' => Post::get('type') ?: 'full'])['type'];

        $v = InputValidator::from(['type' => $type])
            ->in('type', ['full', 'database', 'files', 'email'], 'Yedek türü');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('backups/main');
            return;
        }

        $id = $this->model->create([
            'site_id'      => $siteId,
            'client_id'    => $clientId,
            'type'         => $type,
            'filename'     => 'backup_' . date('Ymd_His') . '.tar.gz',
            'status'       => 'pending',
            'storage_path' => '/var/backups/ipanel',
            'started_at'   => date('Y-m-d H:i:s'),
        ]);

        AuditLogger::log('backups.create', 'backup', $id, "Yedekleme başlatıldı: $type");
        Session::insert('success', 'Yedekleme işlemi başlatıldı.');
        Redirect::action('backups/main');
    }

    public function delete(int $id): void
    {
        Acl::requireOwnership(Acl::ownsBackup($id));
        $this->model->delete($id);
        AuditLogger::log('backups.delete', 'backup', $id, 'Yedekleme kaydı silindi.');
        Session::insert('success', 'Yedekleme kaydı silindi.');
        Redirect::action('backups/main');
    }
}
