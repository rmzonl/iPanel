<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Inclusion\Project\Masterpage;
use ZN\Inclusion\Project\View;
use Session;
use Redirect;
use Project\Libraries\Acl;
use Project\Libraries\CsrfGuard;

/**
 * Her istekte çalışan global middleware (Starting.php constructors'da tanımlı).
 *
 * Sorumlulukları:
 *  1. Güvenlik HTTP başlıklarını gönder
 *  2. Auth/Errors dışındaki sayfalarda oturum zorunlu kıl
 *  3. Rol bazlı erişim kontrolü (ACL)
 *  4. View'a ortak değişkenleri enjekte et
 */
class Initialize extends Controller
{
    /** Kimlik doğrulama gerektirmeyen controller'lar */
    private const PUBLIC_CONTROLLERS = ['auth', 'errors'];

    /** Admin-only controller'lar (reseller erişemez) */
    private const ADMIN_ONLY = ['settings', 'ipaddresses', 'firewall'];

    public function main(): void
    {
        $this->sendSecurityHeaders();

        $controller = strtolower(CURRENT_CONTROLLER ?? '');
        $method     = strtolower(CURRENT_CFUNCTION  ?? 'main');

        // Genel sayfalara (auth, errors) kimlik doğrulaması gerekmez
        if (in_array($controller, self::PUBLIC_CONTROLLERS, true)) {
            if ($controller === 'auth') {
                Masterpage::bodyPage('layouts/auth-body');
            }
            return;
        }

        // Oturum kontrolü
        $user = Session::select('admin_user');
        if (empty($user)) {
            Redirect::action('auth/login');
            return;
        }

        // Admin-only erişim kontrolü
        if (in_array($controller, self::ADMIN_ONLY, true) && ($user['role'] ?? '') !== 'admin') {
            Session::insert('error', 'Bu bölüme erişim yetkiniz yok.');
            Redirect::action('dashboard/main');
            return;
        }

        // Controller→method bazlı ACL kontrolü
        $permission = Acl::permissionFor($controller, $method);
        if ($permission !== null && !Acl::can($permission, $user)) {
            Session::insert('error', 'Bu işlem için yetkiniz yok.');
            Redirect::action('dashboard/main');
            return;
        }

        // Ortak view değişkenleri
        View::authUser($user);
        View::csrfField(CsrfGuard::field());
        View::csrfToken(CsrfGuard::token());
    }

    /** PHP seviyesinde güvenlik başlıkları (nginx ikinci katman) */
    private function sendSecurityHeaders(): void
    {
        if (headers_sent()) return;

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // PHP/Apache sürüm bilgisini gizle
        header_remove('X-Powered-By');
    }
}
