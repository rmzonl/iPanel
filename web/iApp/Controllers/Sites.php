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


class Sites extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SiteModel();
    }

    public function main()
    {
        View::pageTitle('Siteler');
        View::sites($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('Yeni Site Ekle');
        $clientModel     = new \Project\Models\ClientModel();
        $ipModel         = new \Project\Models\IpAddressModel();
        View::clients($clientModel->getAll());
        View::ips($ipModel->getAllActive());
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('sites/main');
        }

        $data = [
            'client_id'       => Post::get('client_id'),
            'domain'          => Post::get('domain'),
            'ip_id'           => Post::get('ip_id') ?: null,
            'document_root'   => Post::get('document_root'),
            'php_version'     => Post::get('php_version') ?: '8.2',
            'status'          => Post::get('status') ?: 'active',
            'disk_quota'      => Post::get('disk_quota') ?: 0,
            'bandwidth_quota' => Post::get('bandwidth_quota') ?: 0,
        ];

        if (empty($data['client_id']) || empty($data['domain'])) {
            Session::insert('error', 'Müşteri ve domain alanları zorunludur.');
            Redirect::action('sites/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'Site başarıyla eklendi.');
        Redirect::action('sites/main');
    }

    public function edit($id)
    {
        View::pageTitle('Site Düzenle');
        View::site($this->model->getById($id));
        if (!View::site()) {
            Redirect::action('sites/main');
        }
        $clientModel   = new \Project\Models\ClientModel();
        $ipModel       = new \Project\Models\IpAddressModel();
        View::clients($clientModel->getAll());
        View::ips($ipModel->getAllActive());
    }

    public function update($id)
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('sites/main');
        }

        $data = [
            'client_id'       => Post::get('client_id'),
            'domain'          => Post::get('domain'),
            'ip_id'           => Post::get('ip_id') ?: null,
            'document_root'   => Post::get('document_root'),
            'php_version'     => Post::get('php_version') ?: '8.2',
            'status'          => Post::get('status') ?: 'active',
            'disk_quota'      => Post::get('disk_quota') ?: 0,
            'bandwidth_quota' => Post::get('bandwidth_quota') ?: 0,
        ];

        $this->model->update($id, $data);
        Session::insert('success', 'Site başarıyla güncellendi.');
        Redirect::action('sites/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'Site başarıyla silindi.');
        Redirect::action('sites/main');
    }
}
