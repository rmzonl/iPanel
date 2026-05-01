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
 * PHP sürüm ve eklenti yönetimi — sadece admin.
 */
class PhpManager extends Controller
{
    public function main()
    {
        View::pageTitle('PHP Yönetimi');
        View::phpInfo($this->collectInfo());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    /** PHP sürümü kur */
    public function installVersion()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmanager/main'); return; }

        $version = Post::php_version();
        if (!preg_match('/^\d+\.\d+$/', $version)) {
            Session::insert('error', 'Geçersiz PHP sürümü formatı.');
            Redirect::action('phpmanager/main');
            return;
        }

        $escaped = escapeshellarg($version);
        $output  = [];
        $os      = $this->detectOs();

        if ($os === 'debian') {
            exec("add-apt-repository -y ppa:ondrej/php 2>&1 && apt-get update -qq 2>&1 && apt-get install -y php{$escaped}-fpm php{$escaped}-cli php{$escaped}-common 2>&1", $output, $rc);
        } else {
            $ver = str_replace('.', '', $version); // 8.2 → 82
            exec("dnf module enable -y php:remi-{$escaped} 2>&1 && dnf install -y php{$ver}-php-fpm php{$ver}-php-cli 2>&1", $output, $rc);
        }

        if ($rc === 0) {
            AuditLogger::log('php.install', 'system', null, "PHP $version kuruldu");
            Session::insert('success', "PHP $version başarıyla kuruldu.");
        } else {
            Session::insert('error', 'Kurulum hatası: ' . htmlspecialchars(implode("\n", array_slice($output, -5))));
        }

        Redirect::action('phpmanager/main');
    }

    /** PHP eklentisi kur/kaldır */
    public function toggleExtension()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmanager/main'); return; }

        $version   = Post::php_version();
        $extension = Post::extension();
        $action    = Post::action(); // install|remove

        if (!preg_match('/^\d+\.\d+$/', $version) || !preg_match('/^[a-zA-Z0-9_-]+$/', $extension)) {
            Session::insert('error', 'Geçersiz parametre.');
            Redirect::action('phpmanager/main');
            return;
        }

        $allowed = ['install', 'remove'];
        if (!in_array($action, $allowed, true)) {
            Session::insert('error', 'Geçersiz işlem.');
            Redirect::action('phpmanager/main');
            return;
        }

        $escVer = escapeshellarg($version);
        $escExt = escapeshellarg($extension);
        $output = [];
        $os     = $this->detectOs();

        if ($os === 'debian') {
            $pkg = "php{$escVer}-{$escExt}";
            $cmd = $action === 'install'
                ? "apt-get install -y $pkg 2>&1"
                : "apt-get remove -y $pkg 2>&1";
        } else {
            $ver = str_replace('.', '', $version);
            $pkg = "php{$ver}-php-{$escExt}";
            $cmd = $action === 'install'
                ? "dnf install -y $pkg 2>&1"
                : "dnf remove -y $pkg 2>&1";
        }

        exec($cmd, $output, $rc);

        if ($rc === 0) {
            AuditLogger::log("php.extension.$action", 'system', null, "PHP $version eklenti: $extension $action");
            Session::insert('success', "Eklenti $extension işlemi tamamlandı.");
        } else {
            Session::insert('error', 'İşlem hatası: ' . htmlspecialchars(implode("\n", array_slice($output, -5))));
        }

        Redirect::action('phpmanager/main');
    }

    /** PHP-FPM'i yeniden başlat */
    public function restartFpm()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmanager/main'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmanager/main'); return; }

        $version = Post::php_version();
        if (!preg_match('/^\d+\.\d+$/', $version)) {
            Session::insert('error', 'Geçersiz PHP sürümü.');
            Redirect::action('phpmanager/main');
            return;
        }

        $escVer  = escapeshellarg($version);
        $os      = $this->detectOs();
        $service = $os === 'debian' ? "php{$escVer}-fpm" : "php-fpm";

        exec("systemctl restart $service 2>&1", $output, $rc);

        if ($rc === 0) {
            AuditLogger::log('php.fpm_restart', 'system', null, "PHP-FPM $version yeniden başlatıldı");
            Session::insert('success', "PHP-FPM $version yeniden başlatıldı.");
        } else {
            Session::insert('error', 'Servis başlatma hatası: ' . htmlspecialchars(implode("\n", $output)));
        }

        Redirect::action('phpmanager/main');
    }

    /** Sistem bilgilerini topla */
    private function collectInfo(): array
    {
        $os = $this->detectOs();

        // Kurulu PHP sürümleri
        $versions = [];
        if ($os === 'debian') {
            $dirs = glob('/etc/php/*/fpm') ?: [];
            foreach ($dirs as $d) {
                $v = basename(dirname($d));
                if (preg_match('/^\d+\.\d+$/', $v)) {
                    $versions[] = $v;
                }
            }
        } else {
            // Remi: /etc/opt/remi/php82 vb.
            $dirs = glob('/etc/opt/remi/php*') ?: [];
            foreach ($dirs as $d) {
                $base = basename($d);
                if (preg_match('/^php(\d)(\d+)$/', $base, $m)) {
                    $versions[] = $m[1] . '.' . $m[2];
                }
            }
            // Standart paket
            if (empty($versions)) {
                exec('php -r "echo PHP_VERSION;" 2>/dev/null', $out);
                if (!empty($out[0])) {
                    preg_match('/^(\d+\.\d+)/', $out[0], $m);
                    if (!empty($m[1])) $versions[] = $m[1];
                }
            }
        }
        sort($versions);

        // Aktif PHP sürümü
        exec('php -r "echo PHP_VERSION;" 2>/dev/null', $activeOut);
        $active = $activeOut[0] ?? 'Bilinmiyor';

        // Yüklü eklentiler (aktif sürüm için)
        $extensions = get_loaded_extensions();
        sort($extensions);

        // FPM durum
        $fpmStatus = [];
        foreach ($versions as $v) {
            $svc = $os === 'debian' ? "php{$v}-fpm" : 'php-fpm';
            exec("systemctl is-active $svc 2>/dev/null", $out, $rc);
            $fpmStatus[$v] = trim($out[0] ?? 'unknown');
            $out = [];
        }

        return [
            'versions'   => $versions,
            'active'     => $active,
            'extensions' => $extensions,
            'fpm_status' => $fpmStatus,
            'os'         => $os,
        ];
    }

    /* ── Obfuscation / Koruma Yönetimi ── */

    /** ionCube Loader / PHPKoru / Zend Guard durumu ve yönetimi */
    public function obfuscation()
    {
        View::pageTitle('PHP Koruma Yönetimi');
        View::ioncubeStatus($this->detectIoncube());
        View::phpkoruStatus($this->detectPhpkoru());
        View::zendStatus($this->detectZendGuard());
        View::success(Session::select('success'));
        View::error(Session::select('error'));
        Session::delete('success');
        Session::delete('error');
    }

    /** ionCube Loader kur */
    public function installIoncube()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmanager/obfuscation'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmanager/obfuscation'); return; }

        $version = Post::php_version() ?: '';
        if (!preg_match('/^\d+\.\d+$/', $version)) {
            Session::insert('error', 'Geçersiz PHP sürümü.'); Redirect::action('phpmanager/obfuscation'); return;
        }

        // ionCube indirme ve kurma
        $arch    = php_uname('m') === 'x86_64' ? 'x86-64' : 'x86';
        $tmpDir  = '/tmp/ioncube_install_' . time();
        $tarball = "$tmpDir/ioncube_loader.tar.gz";
        $dlUrl   = "https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_{$arch}.tar.gz";

        @mkdir($tmpDir, 0700, true);
        $cmd = "curl -fsSL " . escapeshellarg($dlUrl) . " -o " . escapeshellarg($tarball)
             . " && tar -xzf " . escapeshellarg($tarball) . " -C " . escapeshellarg($tmpDir)
             . " 2>&1";
        exec($cmd, $out, $rc);

        if ($rc !== 0) {
            Session::insert('error', 'ionCube indirilemedi: ' . htmlspecialchars(implode("\n", array_slice($out, -3))));
            Redirect::action('phpmanager/obfuscation');
            return;
        }

        // Uygun .so dosyasını bul
        $ver      = str_replace('.', '_', $version);
        $pattern  = "$tmpDir/ioncube/ioncube_loader_lin_{$ver}.so";
        $soFiles  = glob($pattern) ?: [];
        if (empty($soFiles)) {
            Session::insert('error', "ionCube loader PHP $version için bulunamadı.");
            Redirect::action('phpmanager/obfuscation');
            return;
        }
        $soFile = $soFiles[0];

        // PHP eklenti dizinine kopyala
        exec("php$version -r 'echo ini_get(\"extension_dir\");' 2>/dev/null", $extDirOut);
        $extDir = trim($extDirOut[0] ?? '');
        if (!$extDir || !is_dir($extDir)) {
            Session::insert('error', 'PHP eklenti dizini bulunamadı.'); Redirect::action('phpmanager/obfuscation'); return;
        }

        exec("cp " . escapeshellarg($soFile) . " " . escapeshellarg($extDir . '/') . " 2>&1", $out, $rc);
        if ($rc !== 0) { Session::insert('error', 'Dosya kopyalanamadı.'); Redirect::action('phpmanager/obfuscation'); return; }

        // php.ini'ye ekle
        $phpIniDir = $this->detectOs() === 'debian' ? "/etc/php/$version/mods-available" : "/etc/php.d";
        @mkdir($phpIniDir, 0755, true);
        $iniContent = "zend_extension=" . basename($soFile) . "\n";
        file_put_contents("$phpIniDir/ioncube.ini", $iniContent);

        if ($this->detectOs() === 'debian') {
            exec("phpenmod -v $version ioncube 2>&1");
        }

        // Temizlik
        exec("rm -rf " . escapeshellarg($tmpDir));

        AuditLogger::log('php.ioncube_install', 'system', null, "ionCube PHP $version kuruldu");
        Session::insert('success', "ionCube Loader PHP $version için kuruldu.");
        Redirect::action('phpmanager/obfuscation');
    }

    /** ionCube kaldır */
    public function removeIoncube()
    {
        if (!Http::isRequestMethod('post')) { Redirect::action('phpmanager/obfuscation'); return; }
        if (!CsrfGuard::verify()) { Session::insert('error', 'Geçersiz form isteği.'); Redirect::action('phpmanager/obfuscation'); return; }

        $version = Post::php_version() ?: '';
        if (!preg_match('/^\d+\.\d+$/', $version)) {
            Session::insert('error', 'Geçersiz PHP sürümü.'); Redirect::action('phpmanager/obfuscation'); return;
        }

        if ($this->detectOs() === 'debian') {
            exec("phpdismod -v $version ioncube 2>&1");
        }

        $iniFiles = [
            "/etc/php/$version/mods-available/ioncube.ini",
            "/etc/php.d/ioncube.ini",
            "/etc/php/$version/cli/conf.d/00-ioncube.ini",
            "/etc/php/$version/fpm/conf.d/00-ioncube.ini",
        ];
        foreach ($iniFiles as $f) { @unlink($f); }

        // .so sil
        exec("php$version -r 'echo ini_get(\"extension_dir\");' 2>/dev/null", $extDirOut);
        $extDir = trim($extDirOut[0] ?? '');
        if ($extDir) {
            foreach (glob("$extDir/ioncube_loader_*.so") ?: [] as $f) { @unlink($f); }
        }

        AuditLogger::log('php.ioncube_remove', 'system', null, "ionCube PHP $version kaldırıldı");
        Session::insert('success', "ionCube Loader PHP $version kaldırıldı.");
        Redirect::action('phpmanager/obfuscation');
    }

    // ── Detect helpers ──

    private function detectIoncube(): array
    {
        $loaded = extension_loaded('ionCube Loader') || function_exists('ioncube_loader_iversion');
        return [
            'loaded'  => $loaded,
            'version' => $loaded ? (function_exists('ioncube_loader_version') ? ioncube_loader_version() : 'Bilinmiyor') : null,
        ];
    }

    private function detectPhpkoru(): array
    {
        // PHPKoru eklentisi çeşitli isimlerle yüklenebilir
        $loaded = extension_loaded('phpkoru') || extension_loaded('sg');
        return ['loaded' => $loaded];
    }

    private function detectZendGuard(): array
    {
        $loaded = extension_loaded('Zend Guard Loader') || extension_loaded('Zend OPcache');
        return ['loaded' => $loaded];
    }

    private function detectOs(): string
    {
        // open_basedir /etc'yi kapsamaz; PHP_OS sabitlerini ve uname çıktısını kullan
        $uname = strtolower(php_uname('v') . ' ' . php_uname('r'));
        return preg_match('/debian|ubuntu/i', $uname) ? 'debian' : 'rhel';
    }
}
