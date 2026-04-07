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


class Ssl extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SslModel();
    }

    public function main()
    {
        View::pageTitle('SSL Sertifikaları');
        View::certs($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('SSL Sertifikası Ekle');
        $domainModel      = new \Project\Models\DomainModel();
        View::domains($domainModel->getAll());
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            Redirect::action('ssl/main');
        }

        $data = [
            'domain_id'   => Post::get('domain_id'),
            'type'        => Post::get('type') ?: 'letsencrypt',
            'cert_file'   => Post::get('cert_file'),
            'key_file'    => Post::get('key_file'),
            'chain_file'  => Post::get('chain_file'),
            'issued_at'   => Post::get('issued_at') ?: null,
            'expires_at'  => Post::get('expires_at') ?: null,
            'auto_renew'  => Post::get('auto_renew') ? 1 : 0,
            'status'      => Post::get('status') ?: 'pending',
        ];

        if (empty($data['domain_id'])) {
            Session::insert('error', 'Domain seçimi zorunludur.');
            Redirect::action('ssl/create');
            return;
        }

        $this->model->create($data);
        Session::insert('success', 'SSL sertifikası başarıyla eklendi.');
        Redirect::action('ssl/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::insert('success', 'SSL sertifikası başarıyla silindi.');
        Redirect::action('ssl/main');
    }
}
