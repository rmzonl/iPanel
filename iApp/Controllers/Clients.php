<?php namespace Project\Controllers;

class Clients extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\ClientModel();
    }

    public function main()
    {
        $this->pageTitle = 'Müşteriler';
        $this->clients   = $this->model->getAll();
        $this->success   = Session::get('success');
        $this->error     = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni Müşteri Ekle';
    }

    public function store()
    {
        if (!Http::isPost()) {
            Redirect::to('clients/main');
        }

        $data = [
            'company_name' => Post::get('company_name'),
            'first_name'   => Post::get('first_name'),
            'last_name'    => Post::get('last_name'),
            'email'        => Post::get('email'),
            'phone'        => Post::get('phone'),
            'address'      => Post::get('address'),
            'city'         => Post::get('city'),
            'country'      => Post::get('country') ?: 'TR',
            'status'       => Post::get('status') ?: 'active',
            'notes'        => Post::get('notes'),
        ];

        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            Session::set('error', 'Ad, soyad ve e-posta alanları zorunludur.');
            Redirect::to('clients/create');
            return;
        }

        $this->model->create($data);
        Session::set('success', 'Müşteri başarıyla eklendi.');
        Redirect::to('clients/main');
    }

    public function edit($id)
    {
        $this->pageTitle = 'Müşteri Düzenle';
        $this->client    = $this->model->getById($id);
        if (!$this->client) {
            Redirect::to('clients/main');
        }
    }

    public function update($id)
    {
        if (!Http::isPost()) {
            Redirect::to('clients/main');
        }

        $data = [
            'company_name' => Post::get('company_name'),
            'first_name'   => Post::get('first_name'),
            'last_name'    => Post::get('last_name'),
            'email'        => Post::get('email'),
            'phone'        => Post::get('phone'),
            'address'      => Post::get('address'),
            'city'         => Post::get('city'),
            'country'      => Post::get('country') ?: 'TR',
            'status'       => Post::get('status') ?: 'active',
            'notes'        => Post::get('notes'),
        ];

        $this->model->update($id, $data);
        Session::set('success', 'Müşteri başarıyla güncellendi.');
        Redirect::to('clients/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::set('success', 'Müşteri başarıyla silindi.');
        Redirect::to('clients/main');
    }
}
