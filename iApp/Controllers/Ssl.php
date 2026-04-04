<?php namespace Project\Controllers;

class Ssl extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SslModel();
    }

    public function main()
    {
        $this->pageTitle  = 'SSL Sertifikaları';
        $this->certs      = $this->model->getAll();
        $this->success    = Session::get('success');
        $this->error      = Session::get('error');
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $this->pageTitle  = 'SSL Sertifikası Ekle';
        $domainModel      = new \Project\Models\DomainModel();
        $this->domains    = $domainModel->getAll();
    }

    public function store()
    {
        if (!Http::isPost()) {
            Redirect::to('ssl/main');
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
            Session::set('error', 'Domain seçimi zorunludur.');
            Redirect::to('ssl/create');
            return;
        }

        $this->model->create($data);
        Session::set('success', 'SSL sertifikası başarıyla eklendi.');
        Redirect::to('ssl/main');
    }

    public function delete($id)
    {
        $this->model->delete($id);
        Session::set('success', 'SSL sertifikası başarıyla silindi.');
        Redirect::to('ssl/main');
    }
}
