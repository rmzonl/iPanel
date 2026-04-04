<?php namespace Project\Controllers;

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
        $this->success   = Session::get('success');
        $this->error     = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni Kural Ekle';
    }

    public function store()
    {
        if (!Http::isPost()) {
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
            Session::set('error', 'Kural adı zorunludur.');
            Redirect::to('firewall/create');
            return;
        }

        $this->model->create($data);
        Session::set('success', 'Güvenlik duvarı kuralı başarıyla eklendi.');
        Redirect::to('firewall/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::set('success', 'Kural başarıyla silindi.');
        Redirect::to('firewall/main');
    }
}
