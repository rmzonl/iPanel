<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use DB;
use Session;
use Redirect;
use Project\Libraries\CsrfGuard;
use Project\Libraries\RateLimiter;
use Project\Libraries\AuditLogger;
use Project\Libraries\Totp;

class Auth extends Controller
{
    public function login()
    {
        if (Session::select('admin_user')) {
            Redirect::action('dashboard/main');
            return;
        }

        if (Http::isRequestMethod('post')) {
            $this->handleLogin();
            return;
        }

        View::csrfField(CsrfGuard::field());
    }

    private function handleLogin(): void
    {
        $ip       = RateLimiter::clientIp();
        $username = trim((string) Post::get('username'));
        $password = (string) Post::get('password');

        // 1. IP blok kontrolü
        if (RateLimiter::isBlocked($ip)) {
            View::error('Çok fazla başarısız deneme. Lütfen 30 dakika bekleyin.');
            View::csrfField(CsrfGuard::field());
            AuditLogger::log('auth.blocked', 'ip', null, "Bloklanmış IP giriş denemesi: $ip ($username)");
            return;
        }

        // 2. CSRF doğrulama
        if (!CsrfGuard::verify()) {
            View::error('Geçersiz form isteği. Lütfen tekrar deneyin.');
            View::csrfField(CsrfGuard::field());
            return;
        }

        // 3. Temel doğrulama
        if (empty($username) || empty($password)) {
            View::error('Kullanıcı adı ve şifre gereklidir.');
            View::csrfField(CsrfGuard::field());
            return;
        }

        // 4. Kullanıcıyı bul
        $user = DB::table('users')
            ->where('username', $username)
            ->whereOr('email', $username)
            ->get()
            ->row();

        // 5. Şifre doğrulama
        if (!$user || !password_verify($password, $user->password)) {
            RateLimiter::attempt($ip, $username);
            $remaining = max(0, RateLimiter::MAX_ATTEMPTS - $this->attemptCount($ip));
            View::error($remaining > 0
                ? "Geçersiz kullanıcı adı veya şifre. ($remaining deneme hakkınız kaldı)"
                : 'Çok fazla başarısız deneme. Lütfen 30 dakika bekleyin.'
            );
            View::csrfField(CsrfGuard::field());
            AuditLogger::log('auth.failed', 'user', null, "Başarısız giriş: $username ($ip)");
            return;
        }

        // 6. Hesap aktif mi?
        if ((int)$user->status === 0) {
            View::error('Hesabınız devre dışı bırakılmıştır. Yöneticinizle iletişime geçin.');
            View::csrfField(CsrfGuard::field());
            return;
        }

        // 7. 2FA etkin mi?
        if (!empty($user->totp_enabled) && !empty($user->totp_secret)) {
            // Şifre doğru ama 2FA gerekli — geçici session
            session_regenerate_id(true);
            Session::insert('auth_2fa_pending', [
                'user_id'  => (int) $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
                'secret'   => $user->totp_secret,
                'backup'   => $user->totp_backup,
                'expires'  => time() + 300, // 5 dakika
            ]);
            RateLimiter::clear($ip);
            Redirect::action('auth/verify2fa');
            return;
        }

        // 8. Başarılı giriş (2FA yok)
        $this->completeLogin($user, $ip);
    }

    /** 2FA doğrulama sayfası */
    public function verify2fa()
    {
        $pending = Session::select('auth_2fa_pending');

        if (empty($pending) || ($pending['expires'] ?? 0) < time()) {
            Session::delete('auth_2fa_pending');
            Session::insert('error', '2FA oturumu sona erdi. Lütfen tekrar giriş yapın.');
            Redirect::action('auth/login');
            return;
        }

        if (Http::isRequestMethod('post')) {
            $this->handle2fa($pending);
            return;
        }

        View::csrfField(CsrfGuard::field());
        View::username($pending['username']);
    }

    private function handle2fa(array $pending): void
    {
        if (!CsrfGuard::verify()) {
            View::error('Geçersiz form isteği.');
            View::csrfField(CsrfGuard::field());
            View::username($pending['username']);
            return;
        }

        $code   = trim((string) Post::get('code'));
        $secret = $pending['secret'];
        $valid  = false;
        $usedBackup = false;

        if (Totp::verify($secret, $code)) {
            $valid = true;
        } elseif (!empty($pending['backup'])) {
            // Yedek kod dene
            $newBackup = Totp::verifyBackupCode($code, $pending['backup']);
            if ($newBackup !== null) {
                $valid      = true;
                $usedBackup = true;
                // Kullanılan yedek kodu sil
                DB::where('id', $pending['user_id'])
                    ->update('users', ['totp_backup' => $newBackup]);
            }
        }

        if (!$valid) {
            View::error('Geçersiz doğrulama kodu.');
            View::csrfField(CsrfGuard::field());
            View::username($pending['username']);
            AuditLogger::log('auth.2fa_failed', 'user', $pending['user_id'],
                "2FA başarısız: {$pending['username']}"
            );
            return;
        }

        // 2FA başarılı — oturumu tamamla
        Session::delete('auth_2fa_pending');

        $user = DB::table('users')->where('id', $pending['user_id'])->get()->row();
        if (!$user) {
            Redirect::action('auth/login');
            return;
        }

        if ($usedBackup) {
            AuditLogger::log('auth.2fa_backup_used', 'user', $user->id,
                "Yedek kod kullanıldı: {$user->username}"
            );
        }

        $this->completeLogin($user, RateLimiter::clientIp());
    }

    /** Session'ı oluştur ve dashboard'a yönlendir */
    private function completeLogin(object $user, string $ip): void
    {
        session_regenerate_id(true);

        Session::insert('admin_user', [
            'id'       => (int) $user->id,
            'username' => $user->username,
            'email'    => $user->email,
            'role'     => $user->role,
        ]);

        DB::where('id', $user->id)->update('users', [
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        AuditLogger::log('auth.login', 'user', (int)$user->id,
            "Başarılı giriş: {$user->username} ($ip)"
        );

        Redirect::action('dashboard/main');
    }

    public function logout(): void
    {
        $user = Session::select('admin_user');
        if ($user) {
            AuditLogger::log('auth.logout', 'user', $user['id'] ?? null, "Çıkış: {$user['username']}");
        }

        Session::delete('admin_user');
        Session::delete('_csrf_token');
        Session::delete('auth_2fa_pending');

        session_unset();
        session_destroy();

        Redirect::action('auth/login');
    }

    private function attemptCount(string $ip): int
    {
        $since = date('Y-m-d H:i:s', time() - RateLimiter::WINDOW_SECONDS);
        return (int) DB::table('login_attempts')
            ->where('ip', $ip)
            ->where('attempted_at >=', $since)
            ->count();
    }
}
