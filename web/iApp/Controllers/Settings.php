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
use Project\Libraries\Totp;

// Admin-only — Initialize middleware bu controller'ı zaten kısıtlıyor
class Settings extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new \Project\Models\SettingsModel();
    }

    public function main()
    {
        View::pageTitle('Ayarlar');
        View::serverSettings($this->model->getServerSettings());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    public function save()
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
            $value = Post::$key();
            if ($value === null) continue;

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

    // ---------------------------------------------------------------
    // 2FA Yönetimi
    // ---------------------------------------------------------------

    /** 2FA kurulum sayfası */
    public function twoFactor()
    {
        $user   = Acl::user();
        $dbUser = DB::table('users')->where('id', $user['id'])->get()->row();

        View::pageTitle('İki Faktörlü Doğrulama');
        View::totpEnabled(!empty($dbUser->totp_enabled));
        View::backupRemaining($this->countBackupCodes($dbUser->totp_backup ?? null));
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    /** 2FA etkinleştirme başlat: secret üret, QR göster */
    public function setup2fa()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('settings/twoFactor'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('settings/twoFactor'); return; }

        $user   = Acl::user();
        $dbUser = DB::table('users')->where('id', $user['id'])->get()->row();

        if (!empty($dbUser->totp_enabled)) {
            Session::insert('error', '2FA zaten etkin.');
            Redirect::action('settings/twoFactor');
            return;
        }

        // Yeni secret üret ve geçici olarak session'a kaydet
        $secret = Totp::generateSecret();
        Session::insert('totp_setup_secret', $secret);

        $otpUri = Totp::otpUri($secret, $user['email'] ?? $user['username'], 'iPanel');

        View::pageTitle('2FA Kurulumu');
        View::totpSecret($secret);
        View::otpUri($otpUri);
        View::csrfField(CsrfGuard::field());
    }

    /** 2FA etkinleştir: kullanıcının kodu doğru girdiğini teyit et */
    public function enable2fa()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('settings/twoFactor'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('settings/twoFactor'); return; }

        $user   = Acl::user();
        $secret = Session::select('totp_setup_secret');

        if (empty($secret)) {
            Session::insert('error', '2FA kurulum oturumu sona erdi. Lütfen tekrar deneyin.');
            Redirect::action('settings/twoFactor');
            return;
        }

        $code = trim((string) Post::code());
        if (!Totp::verify($secret, $code)) {
            Session::insert('error', 'Doğrulama kodu geçersiz. Lütfen uygulamanızdan güncel kodu girin.');
            Redirect::action('settings/setup2fa');
            return;
        }

        // Yedek kodlar üret
        $backupCodes = Totp::generateBackupCodes(8);
        $backupHash  = Totp::hashBackupCodes($backupCodes);

        DB::where('id', $user['id'])->update('users', [
            'totp_secret'  => $secret,
            'totp_enabled' => 1,
            'totp_backup'  => $backupHash,
        ]);

        Session::delete('totp_setup_secret');

        AuditLogger::log('auth.2fa_enabled', 'user', $user['id'], "2FA etkinleştirildi: {$user['username']}");

        // Yedek kodları göster
        Session::insert('totp_backup_codes', $backupCodes);
        Redirect::action('settings/backupCodes');
    }

    /** Yedek kodları göster */
    public function backupCodes()
    {
        $codes = Session::select('totp_backup_codes');
        if (empty($codes)) {
            Redirect::action('settings/twoFactor');
            return;
        }
        Session::delete('totp_backup_codes');

        View::pageTitle('Yedek Kodlar');
        View::backupCodes($codes);
    }

    /** 2FA devre dışı bırak */
    public function disable2fa()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('settings/twoFactor'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('settings/twoFactor'); return; }

        $user = Acl::user();

        // Şifreyi doğrula (ek güvence)
        $password = (string) Post::password();
        $dbUser   = DB::table('users')->where('id', $user['id'])->get()->row();

        if (!$dbUser || !password_verify($password, $dbUser->password)) {
            Session::insert('error', 'Şifreniz yanlış. 2FA devre dışı bırakılamadı.');
            Redirect::action('settings/twoFactor');
            return;
        }

        DB::where('id', $user['id'])->update('users', [
            'totp_secret'  => null,
            'totp_enabled' => 0,
            'totp_backup'  => null,
        ]);

        AuditLogger::log('auth.2fa_disabled', 'user', $user['id'], "2FA devre dışı: {$user['username']}");
        Session::insert('success', '2FA başarıyla devre dışı bırakıldı.');
        Redirect::action('settings/twoFactor');
    }

    private function countBackupCodes(?string $json): int
    {
        if (empty($json)) return 0;
        $codes = json_decode($json, true);
        return is_array($codes) ? count($codes) : 0;
    }
}
