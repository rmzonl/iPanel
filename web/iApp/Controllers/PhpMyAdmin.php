<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use Session;
use Redirect;
use Project\Libraries\CsrfGuard;
use Project\Libraries\AuditLogger;
use Project\Libraries\AgentClient;

/**
 * PHPMyAdmin yönetim sayfası — sadece admin erişebilir.
 * Sistemdeki phpMyAdmin kurulum durumunu yönetir.
 */
class PhpMyAdmin extends Controller
{
    /** Olası kurulum yolları */
    private const INSTALL_PATHS = [
        '/usr/share/phpmyadmin',
        '/var/www/phpmyadmin',
        '/usr/share/phpMyAdmin',
    ];

    public function main()
    {
        View::pageTitle('PHPMyAdmin Yönetimi');
        View::status($this->getStatus());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    /** PHPMyAdmin kur */
    public function install()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmyadmin/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmyadmin/main'); return; }

        $pkg = $this->detectOs() === 'debian' ? 'phpmyadmin' : 'phpMyAdmin';

        try {
            $agent = new AgentClient();
            $agent->call('package.install', ['package' => $pkg]);
            AuditLogger::log('phpmyadmin.install', 'system', null, 'PHPMyAdmin kuruldu');
            Session::insert('success', 'PHPMyAdmin başarıyla kuruldu.');
        } catch (\Throwable $e) {
            Session::insert('error', 'Kurulum hatası: ' . htmlspecialchars($e->getMessage()));
        }

        Redirect::action('phpmyadmin/main');
    }

    /** PHPMyAdmin kaldır */
    public function remove()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmyadmin/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmyadmin/main'); return; }

        $pkg = $this->detectOs() === 'debian' ? 'phpmyadmin' : 'phpMyAdmin';

        try {
            $agent = new AgentClient();
            $agent->call('package.remove', ['package' => $pkg]);
            AuditLogger::log('phpmyadmin.remove', 'system', null, 'PHPMyAdmin kaldırıldı');
            Session::insert('success', 'PHPMyAdmin kaldırıldı.');
        } catch (\Throwable $e) {
            Session::insert('error', 'Kaldırma hatası: ' . htmlspecialchars($e->getMessage()));
        }

        Redirect::action('phpmyadmin/main');
    }

    /**
     * Geçici güvenli erişim URL'si oluştur (30 dakika geçerli).
     * Bir tek kullanımlık token oluşturur.
     */
    public function generateAccess()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmyadmin/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmyadmin/main'); return; }

        $token     = bin2hex(random_bytes(16));
        $expiresAt = time() + 1800; // 30 dakika

        // Token'ı session'a kaydet
        Session::insert('pma_token', ['token' => $token, 'expires' => $expiresAt]);
        Session::insert('pma_access_url', $token);

        AuditLogger::log('phpmyadmin.access_generated', 'system', null, 'PHPMyAdmin geçici erişim oluşturuldu');
        Session::insert('success', 'Geçici erişim URL\'si oluşturuldu (30 dakika geçerli).');
        Redirect::action('phpmyadmin/main');
    }

    /** Kurulum durumu bilgilerini topla */
    private function getStatus(): array
    {
        $installed = false;
        $path      = null;
        $version   = null;

        foreach (self::INSTALL_PATHS as $p) {
            if (is_dir($p)) {
                $installed = true;
                $path      = $p;
                break;
            }
        }

        // Sürüm tespiti
        if ($installed) {
            $vFile = $path . '/libraries/classes/DatabaseInterface.php';
            if (!file_exists($vFile)) {
                $vFile = $path . '/ChangeLog';
            }
            // composer.json'dan versiyon oku
            $cj = $path . '/composer.json';
            if (file_exists($cj)) {
                $data = json_decode(file_get_contents($cj), true);
                $version = $data['version'] ?? 'Bilinmiyor';
            }
        }

        // Nginx/Apache config durumu
        $nginxConf = '/etc/nginx/conf.d/phpmyadmin.conf';
        $hasNginx  = file_exists($nginxConf);

        // Mevcut erişim token
        $pmaToken = Session::select('pma_token');
        $accessToken = null;
        if ($pmaToken && ($pmaToken['expires'] ?? 0) > time()) {
            $accessToken = $pmaToken['token'];
        } else {
            Session::delete('pma_token');
            Session::delete('pma_access_url');
        }

        return [
            'installed'    => $installed,
            'path'         => $path,
            'version'      => $version,
            'has_nginx'    => $hasNginx,
            'access_token' => $accessToken,
            'os'           => $this->detectOs(),
        ];
    }

    private function detectOs(): string
    {
        return preg_match('/debian|ubuntu/i', php_uname('v')) ? 'debian' : 'rhel';
    }
}
