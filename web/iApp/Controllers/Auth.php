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

class Auth extends Controller
{
    public function login(): void
    {
        // Zaten giriş yapıldıysa dashboard'a yönlendir
        if (Session::select('admin_user')) {
            Redirect::action('dashboard/main');
            return;
        }

        if (Http::isRequestMethod('post')) {
            $this->handleLogin();
            return;
        }

        // CSRF token'ı view'a gönder
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

        // 7. Başarılı giriş
        RateLimiter::clear($ip);

        // Session hijacking önlemi: yeni session ID
        session_regenerate_id(true);

        Session::insert('admin_user', [
            'id'       => (int) $user->id,
            'username' => $user->username,
            'email'    => $user->email,
            'role'     => $user->role,
        ]);

        DB::table('users')->where('id', $user->id)->update([
            'last_login' => date('Y-m-d H:i:s'),
        ]);

        AuditLogger::log('auth.login', 'user', (int)$user->id, "Başarılı giriş: {$user->username} ($ip)");

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

        // Tüm session'ı temizle ve yeniden başlat
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
