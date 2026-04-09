<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Inclusion\Project\View;
use Session;
use Redirect;
use Project\Libraries\CsrfGuard;
use Project\Libraries\AuditLogger;

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
    public function install(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmyadmin/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmyadmin/main'); return; }

        $output = [];
        $os     = $this->detectOs();

        if ($os === 'debian') {
            exec('DEBIAN_FRONTEND=noninteractive apt-get install -y phpmyadmin 2>&1', $output, $rc);
        } else {
            // RHEL/AlmaLinux: phpMyAdmin EPEL'den gelir
            exec('dnf install -y phpMyAdmin 2>&1', $output, $rc);
        }

        if ($rc === 0) {
            AuditLogger::log('phpmyadmin.install', 'system', null, 'PHPMyAdmin kuruldu');
            Session::insert('success', 'PHPMyAdmin başarıyla kuruldu.');
        } else {
            Session::insert('error', 'Kurulum hatası: ' . htmlspecialchars(implode("\n", array_slice($output, -5))));
        }

        Redirect::action('phpmyadmin/main');
    }

    /** PHPMyAdmin kaldır */
    public function remove(): void
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmyadmin/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmyadmin/main'); return; }

        $output = [];
        $os     = $this->detectOs();

        if ($os === 'debian') {
            exec('DEBIAN_FRONTEND=noninteractive apt-get remove -y phpmyadmin 2>&1', $output, $rc);
        } else {
            exec('dnf remove -y phpMyAdmin 2>&1', $output, $rc);
        }

        if ($rc === 0) {
            AuditLogger::log('phpmyadmin.remove', 'system', null, 'PHPMyAdmin kaldırıldı');
            Session::insert('success', 'PHPMyAdmin kaldırıldı.');
        } else {
            Session::insert('error', 'Kaldırma hatası: ' . htmlspecialchars(implode("\n", array_slice($output, -5))));
        }

        Redirect::action('phpmyadmin/main');
    }

    /**
     * Geçici güvenli erişim URL'si oluştur (30 dakika geçerli).
     * Bir tek kullanımlık token oluşturur.
     */
    public function generateAccess(): void
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
        return file_exists('/etc/debian_version') ? 'debian' : 'rhel';
    }
}
