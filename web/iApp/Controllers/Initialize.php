<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Inclusion\Project\Masterpage;
use ZN\Inclusion\Project\View;
use Session;
use Redirect;
use Json;
use Project\Libraries\Acl;
use Project\Libraries\CsrfGuard;

/**
 * Korunan sayfalar için global middleware.
 * Auth / Errors / Api controller'larında çalışmaz (const exclude).
 * Auth sayfaları için InitializeAuth kullanılır.
 */
class Initialize extends Controller
{
    const exclude = ['Auth', 'Errors', 'Api'];

    private const JSON_CONTROLLERS = ['Stats', 'Jobs'];

    /** Controller/method kombinasyonları — Masterpage olmadan JSON döner */
    private const JSON_METHODS = [
        'Logs' => ['data'],
    ];

    private const SETTINGS_RESELLER_ALLOWED = ['twoFactor', 'setup2fa', 'enable2fa', 'disable2fa', 'backupCodes'];

    private const ADMIN_ONLY = ['IpAddresses', 'Firewall', 'Phpmyadmin', 'Phpmanager', 'Nodemanager', 'Filemanager', 'Logs'];

    public function main()
    {
        header_remove('X-Powered-By');

        $user       = Session::select('admin_user');
        $controller = CURRENT_CONTROLLER ?? '';
        $method     = CURRENT_CFUNCTION  ?? 'main';

        // JSON/SSE controller'lar için masterpage kurma — kendi header'larını yönetirler
        $isJson = in_array($controller, self::JSON_CONTROLLERS)
               || in_array($method, self::JSON_METHODS[$controller] ?? []);
        if (!$isJson) {
            Masterpage::headPage('layouts/head')->bodyPage('layouts/body');
        }

        if (empty($user)) {
            if ($isJson) {
                Http::response(401);
                header('Content-Type: application/json');
                echo Json::encode(['success' => false, 'message' => 'Oturum açılmamış.']);
                die;
            }
            Redirect::action('auth/login');
            die;
        }

        // Admin-only erişim kontrolü
        $isAdminOnlyAccess = in_array($controller, self::ADMIN_ONLY)
            || ($controller === 'Settings' && ! in_array($method, self::SETTINGS_RESELLER_ALLOWED));

        if ($isAdminOnlyAccess && ($user['role'] ?? '') !== 'admin') {
            Session::insert('error', 'Bu bölüme erişim yetkiniz yok.');
            Redirect::action('dashboard/main');
            die;
        }

        // ACL kontrolü
        $permission = Acl::permissionFor($controller, $method);
        if ($permission !== null && ! Acl::can($permission, $user)) {
            Session::insert('error', 'Bu işlem için yetkiniz yok.');
            Redirect::action('dashboard/main');
            die;
        }

        // Ortak view değişkenleri
        View::authUser($user);
        View::csrfField(CsrfGuard::field());
        View::csrfToken(CsrfGuard::token());
    }
}
