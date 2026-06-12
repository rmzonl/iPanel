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
        View::pageTitle('Müşteriler');
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
        View::pageTitle('Yeni Müşteri Ekle');
        // Validation hatası sonrası geri dönüşte hata ve eski değerleri göster
        $err = Session::select('error');
        $old = Session::select('old');
        Session::delete('error');
        Session::delete('old');
        if ($err) View::error($err);
        if ($old) View::old((array) $old);
    }

    public function store()
    {
        if (!Http::isRequestMethod('post')) {
            $this->isAjax() ? $this->jsonFail('Geçersiz istek.', 405) : Redirect::action('clients/main');
            return;
        }
        if (!CsrfGuard::verify()) {
            if ($this->isAjax()) {
                $this->jsonFail('Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.', 403);
            } else {
                Session::insert('error', 'Geçersiz form isteği.');
                Redirect::action('clients/main');
            }
            return;
        }

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
            if ($this->isAjax()) {
                $this->jsonFail($v->firstError());
            } else {
                Session::insert('error', $v->firstError());
                Session::insert('old', $raw);
                Redirect::action('clients/create');
            }
            return;
        }

        $user = Acl::user();
        $raw['reseller_id'] = ($user['role'] === 'reseller') ? $user['id'] : null;

        $id = $this->model->create($raw);
        AuditLogger::log('clients.create', 'client', $id, "Müşteri oluşturuldu: {$raw['email']}");

        if ($this->isAjax()) {
            $this->jsonOk('Müşteri başarıyla eklendi.', ['redirect' => $this->appUrl('clients/main')]);
        } else {
            Session::insert('success', 'Müşteri başarıyla eklendi.');
            Redirect::action('clients/main');
        }
    }

    public function edit(int $id)
    {
        Acl::requireOwnership(Acl::ownsClient($id));
        $client = $this->model->getById($id);
        if (!$client) { Redirect::action('clients/main'); return; }
        View::pageTitle('Müşteri Düzenle');
        View::client($client);
        $err = Session::select('error');
        Session::delete('error');
        if ($err) View::error($err);
    }

    public function update(int $id)
    {
        if (!Http::isRequestMethod('post')) {
            $this->isAjax() ? $this->jsonFail('Geçersiz istek.', 405) : Redirect::action('clients/main');
            return;
        }
        if (!CsrfGuard::verify()) {
            if ($this->isAjax()) {
                $this->jsonFail('Güvenlik doğrulaması başarısız. Sayfayı yenileyip tekrar deneyin.', 403);
            } else {
                Session::insert('error', 'Geçersiz form isteği.');
                Redirect::action('clients/main');
            }
            return;
        }
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
            if ($this->isAjax()) {
                $this->jsonFail($v->firstError());
            } else {
                Session::insert('error', $v->firstError());
                Redirect::action('clients/edit/' . $id);
            }
            return;
        }

        $this->model->update($id, $raw);
        AuditLogger::log('clients.update', 'client', $id, "Müşteri güncellendi: {$raw['email']}");

        if ($this->isAjax()) {
            $this->jsonOk('Müşteri başarıyla güncellendi.', ['redirect' => $this->appUrl('clients/main')]);
        } else {
            Session::insert('success', 'Müşteri başarıyla güncellendi.');
            Redirect::action('clients/main');
        }
    }

    public function delete(int $id)
    {
        if ($this->isAjax() && !CsrfGuard::verify()) {
            $this->jsonFail('Güvenlik doğrulaması başarısız.', 403);
            return;
        }
        Acl::requireOwnership(Acl::ownsClient($id));
        $client = $this->model->getById($id);
        $this->model->delete($id);
        AuditLogger::log('clients.delete', 'client', $id, "Müşteri silindi: " . ($client->email ?? $id));

        if ($this->isAjax()) {
            $this->jsonOk('Müşteri başarıyla silindi.');
        } else {
            Session::insert('success', 'Müşteri başarıyla silindi.');
            Redirect::action('clients/main');
        }
    }

    // -------------------------------------------------------

    private function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    private function appUrl(string $path): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . '/' . ltrim($path, '/');
    }

    private function jsonOk(string $message, array $extras = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'success' => true,
            'message' => $message,
            '_csrf'   => CsrfGuard::token(),
        ], $extras));
        exit;
    }

    private function jsonFail(string $message, int $status = 422): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
