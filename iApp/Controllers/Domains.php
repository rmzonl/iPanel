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
        $this->success   = Session::select('success');
        $this->error     = Session::select('error');
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
        if (!Http::isRequestMethod('post')) {
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
            Session::insert('error', 'Site ve domain adı zorunludur.');
            Redirect::to('domains/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'Domain başarıyla eklendi.');
        Redirect::to('domains/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'Domain başarıyla silindi.');
        Redirect::to('domains/main');
    }
}
