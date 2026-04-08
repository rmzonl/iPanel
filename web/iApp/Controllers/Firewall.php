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
class Firewall extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\FirewallModel();
    }

    public function main(): void
    {
        View::pageTitle('Güvenlik Duvarı');
        View::rules($this->model->getAll());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create(): void
    {
        View::pageTitle('Yeni Kural Ekle');
    }

    public function store(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('firewall/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('firewall/main'); return; }

        $raw = InputValidator::sanitize([
            'name'      => Post::get('name'),
            'action'    => Post::get('action') ?: 'allow',
            'protocol'  => Post::get('protocol') ?: 'tcp',
            'direction' => Post::get('direction') ?: 'in',
            'source_ip' => Post::get('source_ip'),
            'dest_port' => Post::get('dest_port'),
            'priority'  => (int) (Post::get('priority') ?: 0),
            'status'    => Post::get('status') ?: 'active',
        ]);

        $v = InputValidator::from($raw)
            ->required('name', 'Kural adı')
            ->maxLength('name', 100, 'Kural adı')
            ->in('action',    ['allow', 'deny'],                'Aksiyon')
            ->in('protocol',  ['tcp', 'udp', 'icmp', 'all'],   'Protokol')
            ->in('direction', ['in', 'out', 'both'],            'Yön')
            ->in('status',    ['active', 'inactive'],           'Durum');

        if (!empty($raw['source_ip'])) {
            $v->ip('source_ip', 'Kaynak IP');
        }

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('firewall/create');
            return;
        }

        $id = $this->model->create($raw);
        AuditLogger::log('firewall.create', 'firewall_rule', $id, "Güvenlik duvarı kuralı oluşturuldu: {$raw['name']}");
        Session::insert('success', 'Güvenlik duvarı kuralı başarıyla eklendi.');
        Redirect::action('firewall/main');
    }

    public function delete(int $id): void
    {
        $rule = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('firewall.delete', 'firewall_rule', $id, 'Güvenlik duvarı kuralı silindi: ' . ($rule->name ?? $id));
        Session::insert('success', 'Kural başarıyla silindi.');
        Redirect::action('firewall/main');
    }
}
