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

class Sites extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SiteModel();
    }

    public function main()
    {
        View::pageTitle('Siteler');
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
        $clientModel = new \Project\Models\ClientModel();
        $ipModel     = new \Project\Models\IpAddressModel();

        $clients = ($user['role'] === 'admin')
            ? $clientModel->getAll()
            : $clientModel->getByReseller($user['id']);

        View::pageTitle('Yeni Site Ekle');
        View::clients($clients);
        View::ipAddresses($ipModel->getAllActive());
        View::phpVersionsSetting((new \Project\Models\SettingsModel())->getSettingValue('server', 'php_versions'));
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('sites/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('sites/main'); return; }

        $clientId = (int) Post::get('client_id');
        Acl::requireOwnership(Acl::ownsClient($clientId));

        $raw = InputValidator::sanitize([
            'client_id'       => $clientId,
            'domain'          => Post::get('domain'),
            'ip_id'           => Post::get('ip_id') ?: null,
            'document_root'   => Post::get('document_root'),
            'php_version'     => Post::get('php_version') ?: '8.2',
            'status'          => Post::get('status') ?: 'active',
            'disk_quota'      => Post::get('disk_quota') ?: 0,
            'bandwidth_quota' => Post::get('bandwidth_quota') ?: 0,
        ]);

        $v = InputValidator::from($raw)
            ->required('client_id', 'Müşteri')
            ->required('domain', 'Domain')
            ->maxLength('domain', 255, 'Domain')
            ->in('status', ['active', 'suspended', 'deleted'], 'Durum');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('sites/create');
            return;
        }

        $id = $this->model->create($raw);
        AuditLogger::log('sites.create', 'site', $id, "Site oluşturuldu: {$raw['domain']}");
        Session::insert('success', 'Site başarıyla eklendi.');
        Redirect::action('sites/main');
    }

    public function edit(int $id)
    {
        Acl::requireOwnership(Acl::ownsSite($id));
        $site = $this->model->getById($id);
        if (!$site) { Redirect::action('sites/main'); return; }

        $user        = Acl::user();
        $clientModel = new \Project\Models\ClientModel();
        $ipModel     = new \Project\Models\IpAddressModel();

        $clients = ($user['role'] === 'admin')
            ? $clientModel->getAll()
            : $clientModel->getByReseller($user['id']);

        View::pageTitle('Site Düzenle');
        View::site($site);
        View::clients($clients);
        View::ipAddresses($ipModel->getAllActive());
        View::phpVersionsSetting((new \Project\Models\SettingsModel())->getSettingValue('server', 'php_versions'));
    }

    public function update(int $id)
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('sites/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('sites/main'); return; }
        Acl::requireOwnership(Acl::ownsSite($id));

        $raw = InputValidator::sanitize([
            'client_id'       => (int) Post::get('client_id'),
            'domain'          => Post::get('domain'),
            'ip_id'           => Post::get('ip_id') ?: null,
            'document_root'   => Post::get('document_root'),
            'php_version'     => Post::get('php_version') ?: '8.2',
            'status'          => Post::get('status') ?: 'active',
            'disk_quota'      => Post::get('disk_quota') ?: 0,
            'bandwidth_quota' => Post::get('bandwidth_quota') ?: 0,
        ]);

        $this->model->update($id, $raw);
        AuditLogger::log('sites.update', 'site', $id, "Site güncellendi: {$raw['domain']}");
        Session::insert('success', 'Site başarıyla güncellendi.');
        Redirect::action('sites/main');
    }

    public function delete(int $id)
    {
        Acl::requireOwnership(Acl::ownsSite($id));
        $site = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('sites.delete', 'site', $id, "Site silindi: " . ($site->domain ?? $id));
        Session::insert('success', 'Site başarıyla silindi.');
        Redirect::action('sites/main');
    }
}
