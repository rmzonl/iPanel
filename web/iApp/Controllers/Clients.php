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


class Clients extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\ClientModel();
    }

    public function main()
    {
        View::pageTitle('Müşteriler');
        View::clients($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('Yeni Müşteri Ekle');
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('clients/main');
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
            Session::insert('error', 'Ad, soyad ve e-posta alanları zorunludur.');
            Redirect::action('clients/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'Müşteri başarıyla eklendi.');
        Redirect::action('clients/main');
    }

    public function edit($id)
    {
        View::pageTitle('Müşteri Düzenle');
        View::client($this->model->getById($id));
        if (!View::client()) {
            Redirect::action('clients/main');
        }
    }

    public function update($id)
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('clients/main');
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
        Session::insert('success', 'Müşteri başarıyla güncellendi.');
        Redirect::action('clients/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'Müşteri başarıyla silindi.');
        Redirect::action('clients/main');
    }
}
