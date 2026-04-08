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

// Admin-only — Initialize middleware bu controller'ı zaten kısıtlıyor
class IpAddresses extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\IpAddressModel();
    }

    public function main(): void
    {
        View::pageTitle('IP Adresleri');
        View::ips($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create(): void
    {
        $clientModel = new \Project\Models\ClientModel();
        View::pageTitle('Yeni IP Adresi');
        View::clients($clientModel->getAll());
    }

    public function store(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('ipaddresses/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('ipaddresses/main'); return; }

        $raw = InputValidator::sanitize([
            'ip'        => Post::get('ip'),
            'netmask'   => Post::get('netmask'),
            'gateway'   => Post::get('gateway'),
            'type'      => Post::get('type') ?: 'shared',
            'client_id' => Post::get('client_id') ? (int) Post::get('client_id') : null,
            'status'    => Post::get('status') ?: 'active',
            'notes'     => Post::get('notes'),
        ]);

        $v = InputValidator::from($raw)
            ->required('ip', 'IP adresi')
            ->ip('ip', 'IP adresi')
            ->in('type', ['server', 'shared', 'dedicated'], 'IP türü')
            ->in('status', ['active', 'inactive'], 'Durum');

        if (!empty($raw['netmask'])) $v->ip('netmask', 'Ağ maskesi');
        if (!empty($raw['gateway'])) $v->ip('gateway', 'Ağ geçidi');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('ipaddresses/create');
            return;
        }

        $id = $this->model->create($raw);
        AuditLogger::log('ipaddresses.create', 'ip_address', $id, "IP eklendi: {$raw['ip']}");
        Session::insert('success', 'IP adresi başarıyla eklendi.');
        Redirect::action('ipaddresses/main');
    }

    public function delete(int $id): void
    {
        $ip = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('ipaddresses.delete', 'ip_address', $id, 'IP silindi: ' . ($ip->ip ?? $id));
        Session::insert('success', 'IP adresi başarıyla silindi.');
        Redirect::action('ipaddresses/main');
    }
}
