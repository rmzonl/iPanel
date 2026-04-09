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

class Clients extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\ClientModel();
    }

    public function main()
    {
        $user    = Acl::user();
        $clients = ($user['role'] === 'admin')
            ? $this->model->getAll()
            : $this->model->getByReseller($user['id']);

        View::pageTitle('Müşteriler');
        View::clients($clients);
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function create()
    {
        View::pageTitle('Yeni Müşteri Ekle');
    }

    public function store(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('clients/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('clients/main'); return; }

        $raw = InputValidator::sanitize([
            'company_name' => Post::get('company_name'),
            'first_name'   => Post::get('first_name'),
            'last_name'    => Post::get('last_name'),
            'email'        => Post::get('email'),
            'phone'        => Post::get('phone'),
            'address'      => Post::get('address'),
            'city'         => Post::get('city'),
            'country'      => Post::get('country') ?: 'TR',
            'status'       => Post::get('status') ?: 'active',
            'notes'        => Post::get('notes'),
        ]);

        $v = InputValidator::from($raw)
            ->required('first_name', 'Ad')
            ->required('last_name', 'Soyad')
            ->required('email', 'E-posta')
            ->email('email')
            ->maxLength('email', 255, 'E-posta')
            ->in('status', ['active', 'suspended', 'terminated'], 'Durum');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('clients/create');
            return;
        }

        $user = Acl::user();
        $raw['reseller_id'] = ($user['role'] === 'reseller') ? $user['id'] : null;

        $id = $this->model->create($raw);
        AuditLogger::log('clients.create', 'client', $id, "Müşteri oluşturuldu: {$raw['email']}");
        Session::insert('success', 'Müşteri başarıyla eklendi.');
        Redirect::action('clients/main');
    }

    public function edit(int $id)
    {
        Acl::requireOwnership(Acl::ownsClient($id));
        View::pageTitle('Müşteri Düzenle');
        $client = $this->model->getById($id);
        if (!$client) { Redirect::action('clients/main'); return; }
        View::client($client);
    }

    public function update(int $id): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('clients/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('clients/main'); return; }
        Acl::requireOwnership(Acl::ownsClient($id));

        $raw = InputValidator::sanitize([
            'company_name' => Post::get('company_name'),
            'first_name'   => Post::get('first_name'),
            'last_name'    => Post::get('last_name'),
            'email'        => Post::get('email'),
            'phone'        => Post::get('phone'),
            'address'      => Post::get('address'),
            'city'         => Post::get('city'),
            'country'      => Post::get('country') ?: 'TR',
            'status'       => Post::get('status') ?: 'active',
            'notes'        => Post::get('notes'),
        ]);

        $v = InputValidator::from($raw)
            ->required('first_name', 'Ad')
            ->required('last_name', 'Soyad')
            ->required('email', 'E-posta')
            ->email('email')
            ->in('status', ['active', 'suspended', 'terminated'], 'Durum');

        if (!$v->passes()) {
            Session::insert('error', $v->firstError());
            Redirect::action('clients/edit/' . $id);
            return;
        }

        $this->model->update($id, $raw);
        AuditLogger::log('clients.update', 'client', $id, "Müşteri güncellendi: {$raw['email']}");
        Session::insert('success', 'Müşteri başarıyla güncellendi.');
        Redirect::action('clients/main');
    }

    public function delete(int $id): void
    {
        Acl::requireOwnership(Acl::ownsClient($id));
        $client = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('clients.delete', 'client', $id, "Müşteri silindi: " . ($client->email ?? $id));
        Session::insert('success', 'Müşteri başarıyla silindi.');
        Redirect::action('clients/main');
    }
}
