<?php namespace Project\Controllers;
use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Request\Get;
use ZN\Inclusion\Project\Masterpage;
use ZN\Inclusion\Project\View;
use DB;
use Session;
use Redirect;
use URL;


class Backups extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\BackupModel();
    }

    public function main()
    {
        View::pageTitle('Yedeklemeler');
        View::backups($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');

        $siteModel     = new \Project\Models\SiteModel();
        $clientModel   = new \Project\Models\ClientModel();
        View::sites($siteModel->getAll());
        View::clients($clientModel->getAll());
    }

    public function create()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('backups/main');
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
        Session::insert('success', 'Yedekleme işlemi başlatıldı.');
        Redirect::action('backups/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'Yedekleme kaydı silindi.');
        Redirect::action('backups/main');
    }
}
