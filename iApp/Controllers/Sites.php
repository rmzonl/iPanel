<?php namespace Project\Controllers;

class Sites extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SiteModel();
    }

    public function main()
    {
        $this->pageTitle = 'Siteler';
        $this->sites     = $this->model->getAll();
        $this->success   = Session::select('success');
        $this->error     = Session::select('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni Site Ekle';
        $clientModel     = new \Project\Models\ClientModel();
        $ipModel         = new \Project\Models\IpAddressModel();
        $this->clients   = $clientModel->getAll();
        $this->ips       = $ipModel->getAllActive();
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::to('sites/main');
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
            Redirect::to('sites/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'Site başarıyla eklendi.');
        Redirect::to('sites/main');
    }

    public function edit($id)
    {
        $this->pageTitle = 'Site Düzenle';
        $this->site      = $this->model->getById($id);
        if (!$this->site) {
            Redirect::to('sites/main');
        }
        $clientModel   = new \Project\Models\ClientModel();
        $ipModel       = new \Project\Models\IpAddressModel();
        $this->clients = $clientModel->getAll();
        $this->ips     = $ipModel->getAllActive();
    }

    public function update($id)
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::to('sites/main');
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
        Redirect::to('sites/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'Site başarıyla silindi.');
        Redirect::to('sites/main');
    }
}
