<?php namespace Project\Controllers;
use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Request\Get;
use ZN\Inclusion\Project\Masterpage;
use DB;
use Session;
use Redirect;
use URL;


class Firewall extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\FirewallModel();
    }

    public function main()
    {
        $this->pageTitle = 'Güvenlik Duvarı';
        $this->rules     = $this->model->getAll();
        $this->success   = Session::select('success');
        $this->error     = Session::select('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni Kural Ekle';
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::to('firewall/main');
        }

        $data = [
            'name'      => Post::get('name'),
            'action'    => Post::get('action') ?: 'allow',
            'protocol'  => Post::get('protocol') ?: 'tcp',
            'direction' => Post::get('direction') ?: 'in',
            'source_ip' => Post::get('source_ip'),
            'dest_port' => Post::get('dest_port'),
            'priority'  => Post::get('priority') ?: 0,
            'status'    => Post::get('status') ?: 'active',
        ];

        if (empty($data['name'])) {
            Session::insert('error', 'Kural adı zorunludur.');
            Redirect::to('firewall/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'Güvenlik duvarı kuralı başarıyla eklendi.');
        Redirect::to('firewall/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'Kural başarıyla silindi.');
        Redirect::to('firewall/main');
    }
}
