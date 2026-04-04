<?php namespace Project\Controllers;

class Backups extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\BackupModel();
    }

    public function main()
    {
        $this->pageTitle = 'Yedeklemeler';
        $this->backups   = $this->model->getAll();
        $this->success   = Session::get('success');
        $this->error     = Session::get('error');
        Session::delete('success');
        Session::delete('error');

        $siteModel     = new \Project\Models\SiteModel();
        $clientModel   = new \Project\Models\ClientModel();
        $this->sites   = $siteModel->getAll();
        $this->clients = $clientModel->getAll();
    }

    public function create()
    {
        if (!Http::isPost()) {
            Redirect::to('backups/main');
        }

        $data = [
            'site_id'      => Post::get('site_id') ?: null,
            'client_id'    => Post::get('client_id') ?: null,
            'type'         => Post::get('type') ?: 'full',
            'filename'     => 'backup_' . date('Ymd_His') . '.tar.gz',
            'status'       => 'pending',
            'storage_path' => '/var/backups/ipanel',
            'started_at'   => date('Y-m-d H:i:s'),
        ];

        $this->model->create($data);
        Session::set('success', 'Yedekleme işlemi başlatıldı.');
        Redirect::to('backups/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::set('success', 'Yedekleme kaydı silindi.');
        Redirect::to('backups/main');
    }
}
