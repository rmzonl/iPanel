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
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function rows()
    {
        $user = Acl::user();
        $data = ($user['role'] === 'admin')
            ? $this->model->getAll()
            : $this->model->getByReseller($user['id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['data' => $data]);
        exit;
    }

    public function create()
    {
        $user        = Acl::user();
        $siteModel   = new \Project\Models\SiteModel();
        $clientModel = new \Project\Models\ClientModel();

        $sites   = ($user['role'] === 'admin') ? $siteModel->getAll()   : $siteModel->getByReseller($user['id']);
        $clients = ($user['role'] === 'admin') ? $clientModel->getAll() : $clientModel->getByReseller($user['id']);

        View::pageTitle('Yeni Domain Ekle');
        View::sites($sites);
        View::clients($clients);
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('domains/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('domains/main'); return; }

        $siteId = (int) Post::get('site_id');
        Acl::requireOwnership(Acl::ownsSite($siteId));

        // client_id seçilen siteden türetilir (form göndermez)
        $site     = (new \Project\Models\SiteModel())->getById($siteId);
        $clientId = (int) ($site->client_id ?? 0);

        $raw = InputValidator::sanitize([
            'site_id'     => $siteId,
            'client_id'   => $clientId,
            'name'        => Post::get('name'),
            'type'        => Post::get('type') ?: 'addon',
            'redirect_to' => Post::get('redirect_to'),
            'status'      => Post::get('status') ?: 'active',
        ]);

        $v = InputValidator::from($raw)
            ->required('site_id', 'Site')
            ->required('name', 'Domain adı')
            ->maxLength('name', 255, 'Domain')
            ->in('type', ['main', 'addon', 'subdomain', 'alias'], 'Tür')
            ->in('status', ['active', 'inactive'], 'Durum');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('domains/create');
            return;
        }

        $id = $this->model->create($raw);
        AuditLogger::log('domains.create', 'domain', $id, "Domain oluşturuldu: {$raw['name']}");
        Session::insert('success', 'Domain başarıyla eklendi.');
        Redirect::action('domains/main');
    }

    public function delete(int $id)
    {
        Acl::requireOwnership(Acl::ownsDomain($id));
        $domain = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('domains.delete', 'domain', $id, 'Domain silindi: ' . ($domain->name ?? $id));
        Session::insert('success', 'Domain başarıyla silindi.');
        Redirect::action('domains/main');
    }
}
