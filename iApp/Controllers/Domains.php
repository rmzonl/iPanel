<?php namespace Project\Controllers;

class Domains extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\DomainModel();
    }

    public function main()
    {
        $this->pageTitle = 'Domain Yönetimi';
        $this->domains   = $this->model->getAll();
        $this->success   = Session::get('success');
        $this->error     = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni Domain Ekle';
        $siteModel       = new \Project\Models\SiteModel();
        $clientModel     = new \Project\Models\ClientModel();
        $this->sites     = $siteModel->getAll();
        $this->clients   = $clientModel->getAll();
    }

    public function store()
    {
        if (!Http::isPost()) {
            Redirect::to('domains/main');
        }

        $data = [
            'site_id'     => Post::get('site_id'),
            'client_id'   => Post::get('client_id'),
            'name'        => Post::get('name'),
            'type'        => Post::get('type') ?: 'addon',
            'redirect_to' => Post::get('redirect_to'),
            'status'      => Post::get('status') ?: 'active',
        ];

        if (empty($data['site_id']) || empty($data['name'])) {
            Session::set('error', 'Site ve domain adı zorunludur.');
            Redirect::to('domains/create');
            return;
        }

        $this->model->create($data);
        Session::set('success', 'Domain başarıyla eklendi.');
        Redirect::to('domains/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::set('success', 'Domain başarıyla silindi.');
        Redirect::to('domains/main');
    }
}
