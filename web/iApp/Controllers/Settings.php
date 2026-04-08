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
class Settings extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SettingsModel();
    }

    public function main(): void
    {
        View::pageTitle('Ayarlar');
        View::serverSettings($this->model->getServerSettings());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function save(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('settings/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('settings/main'); return; }

        $allowedKeys = [
            'panel_name', 'server_ip', 'php_versions', 'max_sites_per_client',
            'max_disk_per_client', 'max_bandwidth_per_client', 'default_php_version',
            'ssl_email', 'backup_path', 'webroot_base',
        ];

        $changes = [];
        foreach ($allowedKeys as $key) {
            $value = Post::get($key);
            if ($value === null) continue;

            // Tip bazlı doğrulama
            if ($key === 'server_ip') {
                if (!filter_var($value, FILTER_VALIDATE_IP)) {
                    Session::insert('error', 'Sunucu IP adresi geçersiz.');
                    Redirect::action('settings/main');
                    return;
                }
            }
            if ($key === 'ssl_email') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    Session::insert('error', 'SSL e-posta adresi geçersiz.');
                    Redirect::action('settings/main');
                    return;
                }
            }

            $sanitized = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            $this->model->set('server', $key, $sanitized);
            $changes[] = $key;
        }

        AuditLogger::log('settings.save', 'settings', null, 'Ayarlar güncellendi: ' . implode(', ', $changes));
        Session::insert('success', 'Ayarlar başarıyla kaydedildi.');
        Redirect::action('settings/main');
    }
}
