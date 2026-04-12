<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use DB;
use Session;
use Redirect;
use Project\Libraries\Acl;
use Project\Libraries\CsrfGuard;
use Project\Libraries\AuditLogger;
use Project\Libraries\InputValidator;

class Ssl extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SslModel();
    }

    public function main()
    {
        $user  = Acl::user();
        $certs = ($user['role'] === 'admin')
            ? $this->model->getAll()
            : $this->model->getByReseller($user['id']);

        View::pageTitle('SSL Sertifikaları');
        View::certs($certs);
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        $user        = Acl::user();
        $domainModel = new \Project\Models\DomainModel();
        $domains     = ($user['role'] === 'admin') ? $domainModel->getAll() : $domainModel->getByReseller($user['id']);

        View::pageTitle('SSL Sertifikası Ekle');
        View::domains($domains);
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('ssl/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('ssl/main'); return; }

        $domainId = (int) Post::domain_id();
        Acl::requireOwnership(Acl::ownsDomain($domainId));

        $raw = InputValidator::sanitize([
            'domain_id'  => $domainId,
            'type'       => Post::type() ?: 'letsencrypt',
            'cert_file'  => Post::cert_file(),
            'key_file'   => Post::key_file(),
            'chain_file' => Post::chain_file(),
            'issued_at'  => Post::issued_at() ?: null,
            'expires_at' => Post::expires_at() ?: null,
            'auto_renew' => Post::auto_renew() ? 1 : 0,
            'status'     => Post::status() ?: 'pending',
        ]);

        $v = InputValidator::from($raw)
            ->required('domain_id', 'Domain')
            ->in('type', ['letsencrypt', 'paid', 'self_signed'], 'Tür')
            ->in('status', ['active', 'expired', 'pending', 'failed'], 'Durum');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('ssl/create');
            return;
        }

        $id = $this->model->create($raw);
        AuditLogger::log('ssl.create', 'ssl_certificate', $id, "SSL sertifikası eklendi: domain #$domainId");
        Session::insert('success', 'SSL sertifikası başarıyla eklendi.');
        Redirect::action('ssl/main');
    }

    public function delete(int $id)
    {
        Acl::requireOwnership(Acl::ownsSslCert($id));
        $this->model->delete($id);
        AuditLogger::log('ssl.delete', 'ssl_certificate', $id, 'SSL sertifikası silindi.');
        Session::insert('success', 'SSL sertifikası başarıyla silindi.');
        Redirect::action('ssl/main');
    }
}
