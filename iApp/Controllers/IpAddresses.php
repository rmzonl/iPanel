<?php namespace Project\Controllers;

class IpAddresses extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\IpAddressModel();
    }

    public function main()
    {
        $this->pageTitle = 'IP Adresleri';
        $this->ips       = $this->model->getAll();
        $this->success   = Session::get('success');
        $this->error     = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle = 'Yeni IP Adresi';
        $clientModel     = new \Project\Models\ClientModel();
        $this->clients   = $clientModel->getAll();
    }

    public function store()
    {
        if (!Http::isPost()) {
            Redirect::to('ipaddresses/main');
        }

        $data = [
            'ip'        => Post::get('ip'),
            'netmask'   => Post::get('netmask'),
            'gateway'   => Post::get('gateway'),
            'type'      => Post::get('type') ?: 'shared',
            'client_id' => Post::get('client_id') ?: null,
            'status'    => Post::get('status') ?: 'active',
            'notes'     => Post::get('notes'),
        ];

        if (empty($data['ip'])) {
            Session::set('error', 'IP adresi zorunludur.');
            Redirect::to('ipaddresses/create');
            return;
        }

        $this->model->create($data);
        Session::set('success', 'IP adresi başarıyla eklendi.');
        Redirect::to('ipaddresses/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::set('success', 'IP adresi başarıyla silindi.');
        Redirect::to('ipaddresses/main');
    }
}
