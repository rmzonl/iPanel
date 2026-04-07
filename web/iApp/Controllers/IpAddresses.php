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


class IpAddresses extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\IpAddressModel();
    }

    public function main()
    {
        View::pageTitle('IP Adresleri');
        View::ips($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('Yeni IP Adresi');
        $clientModel     = new \Project\Models\ClientModel();
        View::clients($clientModel->getAll());
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('ipaddresses/main');
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
            Session::insert('error', 'IP adresi zorunludur.');
            Redirect::action('ipaddresses/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'IP adresi başarıyla eklendi.');
        Redirect::action('ipaddresses/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'IP adresi başarıyla silindi.');
        Redirect::action('ipaddresses/main');
    }
}
