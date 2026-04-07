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


class Domains extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\DomainModel();
    }

    public function main()
    {
        View::pageTitle('Domain Yönetimi');
        View::domains($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('Yeni Domain Ekle');
        $siteModel       = new \Project\Models\SiteModel();
        $clientModel     = new \Project\Models\ClientModel();
        View::sites($siteModel->getAll());
        View::clients($clientModel->getAll());
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('domains/main');
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
            Redirect::action('domains/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'Domain başarıyla eklendi.');
        Redirect::action('domains/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'Domain başarıyla silindi.');
        Redirect::action('domains/main');
    }
}
