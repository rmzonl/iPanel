<?php namespace Project\Controllers;

use ZN\Controller;
use ZN\Request\Http;
use ZN\Request\Post;
use ZN\Request\Get;
use ZN\Inclusion\Project\View;
use Import;
use Session;
use Project\Libraries\JsonResponse;
use Project\Libraries\AuditLogger;
use Project\Libraries\CsrfGuard;

/**
 * Sunucu dosya yöneticisi.
 *
 * Tüm AJAX yanıtlar JsonResponse veya Import::usable ile döner.
 * Dosya sistemi işlemleri root yetkisiyle çalışır — sadece admin erişebilir.
 *
 * ALLOWED_ROOTS: Browse edilebilecek kök dizinler (traversal saldırısı engeli).
 */
class FileManager extends Controller
{
    /** Browse edilebilecek kök dizinler */
    private const ALLOWED_ROOTS = [
        '/home', '/var/www', '/etc/ipanel', '/etc/nginx', '/var/log/ipanel',
        '/tmp', '/root', '/srv',
    ];

    /** Metin editöründe açılabilecek uzantılar */
    private const EDITABLE_EXT = [
        'txt','php','phtml','html','htm','css','scss','js','ts','json',
        'xml','yaml','yml','ini','conf','config','log','sh','bash',
        'sql','md','htaccess','env','toml',
    ];

    // ────────────────────────────────────────────────────────────
    // Ana sayfa
    // ────────────────────────────────────────────────────────────

    public function main()
    {
        $path = $this->safePath(Get::path() ?? '/home');
        View::pageTitle('Dosya Yöneticisi');
        View::currentPath($path);
        View::breadcrumb($this->buildBreadcrumb($path));
        View::files($this->listDir($path));
        View::editable(self::EDITABLE_EXT);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX: Dizin listele — Import::usable ile wizard.php döner
    // ────────────────────────────────────────────────────────────

    public function browse()
    {
        $path = $this->safePath(Get::path() ?? '/home');
        View::files($this->listDir($path));
        View::currentPath($path);
        View::breadcrumb($this->buildBreadcrumb($path));
        View::editable(self::EDITABLE_EXT);
        $html = Import::usable(true)->page('FileManager/list');
        JsonResponse::html($html, $path);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX: Dosya editörünü yükle — Import::usable
    // ────────────────────────────────────────────────────────────

    public function editor()
    {
        $path = $this->safePath(Get::path());
        if (!is_file($path)) JsonResponse::error('Dosya bulunamadı.', [], 404);

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, self::EDITABLE_EXT, true)) {
            JsonResponse::error('Bu dosya türü düzenlenemez.');
        }

        $content = @file_get_contents($path);
        if ($content === false) JsonResponse::error('Dosya okunamadı.');

        View::filePath($path);
        View::fileContent(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        View::fileExt($ext);

        $html = Import::usable(true)->page('FileManager/editor');
        JsonResponse::html($html, $path);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Dosya yükle (ZN Upload facade)
    // ────────────────────────────────────────────────────────────

    public function upload()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $path = $this->safePath(Post::path());
        if (!is_dir($path)) JsonResponse::error('Geçersiz dizin.', [], 400);

        if (empty($_FILES['files']['name'])) {
            JsonResponse::error('Dosya seçilmedi.');
        }

        $names    = (array)$_FILES['files']['name'];
        $tmpNames = (array)$_FILES['files']['tmp_name'];
        $errors   = (array)$_FILES['files']['error'];
        $uploaded = [];
        $failed   = [];

        foreach ($names as $i => $origName) {
            if ($errors[$i] !== UPLOAD_ERR_OK) {
                $failed[] = $origName;
                continue;
            }

            // Güvenli dosya adı: dizin ayracı yok, null byte yok
            $safeName = basename(str_replace("\0", '', $origName));
            $dest     = rtrim($path, '/') . '/' . $safeName;

            if (move_uploaded_file($tmpNames[$i], $dest)) {
                $uploaded[] = $safeName;
            } else {
                $failed[] = $safeName;
            }
        }

        AuditLogger::log('filemanager.upload', 'system', null,
            count($uploaded) . ' dosya yüklendi: ' . $path);

        if (empty($uploaded)) {
            JsonResponse::error('Yükleme başarısız: ' . implode(', ', $failed));
        }

        $msg = count($uploaded) . ' dosya yüklendi.';
        if ($failed) $msg .= ' Başarısız: ' . implode(', ', $failed);
        JsonResponse::success($msg, ['path' => $path]);
    }

    // ────────────────────────────────────────────────────────────
    // GET: Dosya indir
    // ────────────────────────────────────────────────────────────

    public function download()
    {
        $path = $this->safePath(Get::path());
        if (!is_file($path)) {
            http_response_code(404); echo 'Dosya bulunamadı.'; exit;
        }

        $name = basename($path);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($name) . '"');
        header('Content-Length: ' . filesize($path));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($path);
        exit;
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Dosya/dizin sil
    // ────────────────────────────────────────────────────────────

    public function delete()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $path = $this->safePath(Post::path());
        if (!file_exists($path)) JsonResponse::error('Dosya/dizin bulunamadı.', [], 404);

        // Kök dizini silmeye izin verme
        if (in_array(rtrim($path, '/'), self::ALLOWED_ROOTS, true)) {
            JsonResponse::error('Kök dizin silinemez.', [], 403);
        }

        if (is_dir($path)) {
            $ok = $this->deleteRecursive($path);
        } else {
            $ok = @unlink($path);
        }

        if (!$ok) JsonResponse::error('Silme işlemi başarısız.');

        AuditLogger::log('filemanager.delete', 'system', null, $path);
        JsonResponse::success(basename($path) . ' silindi.', ['path' => dirname($path)]);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Yeniden adlandır / taşı
    // ────────────────────────────────────────────────────────────

    public function rename()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $oldPath = $this->safePath(Post::path());
        $newName = basename(str_replace("\0", '', Post::name() ?? ''));

        if (!file_exists($oldPath)) JsonResponse::error('Kaynak bulunamadı.', [], 404);
        if (!$newName)              JsonResponse::error('Yeni isim gerekli.');

        $newPath = dirname($oldPath) . '/' . $newName;
        if (file_exists($newPath))  JsonResponse::error('Bu isimde dosya/dizin zaten mevcut.');

        if (!@rename($oldPath, $newPath)) JsonResponse::error('Yeniden adlandırma başarısız.');

        AuditLogger::log('filemanager.rename', 'system', null, "$oldPath → $newPath");
        JsonResponse::success('Yeniden adlandırıldı.', ['path' => dirname($newPath)]);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Kopyala
    // ────────────────────────────────────────────────────────────

    public function copy()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $src  = $this->safePath(Post::path());
        $dest = $this->safePath(Post::dest());

        if (!file_exists($src)) JsonResponse::error('Kaynak bulunamadı.', [], 404);
        if (!is_dir($dest))     JsonResponse::error('Hedef dizin geçersiz.', [], 400);

        $target = rtrim($dest, '/') . '/' . basename($src);
        if (file_exists($target)) JsonResponse::error('Hedefte aynı isimde dosya var.');

        $ok = is_dir($src) ? $this->copyRecursive($src, $target) : @copy($src, $target);
        if (!$ok) JsonResponse::error('Kopyalama başarısız.');

        AuditLogger::log('filemanager.copy', 'system', null, "$src → $target");
        JsonResponse::success('Kopyalandı.', ['path' => $dest]);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Dizin oluştur
    // ────────────────────────────────────────────────────────────

    public function mkdir()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $parentPath = $this->safePath(Post::path());
        $name       = basename(str_replace("\0", '', Post::name() ?? ''));

        if (!is_dir($parentPath)) JsonResponse::error('Dizin bulunamadı.', [], 400);
        if (!$name)               JsonResponse::error('Dizin adı gerekli.');

        $newDir = rtrim($parentPath, '/') . '/' . $name;
        if (file_exists($newDir))  JsonResponse::error('Bu isimde dosya/dizin zaten var.');

        if (!@mkdir($newDir, 0755, true)) JsonResponse::error('Dizin oluşturulamadı.');

        AuditLogger::log('filemanager.mkdir', 'system', null, $newDir);
        JsonResponse::success('Dizin oluşturuldu.', ['path' => $parentPath]);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Yeni dosya oluştur
    // ────────────────────────────────────────────────────────────

    public function newfile()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $parentPath = $this->safePath(Post::path());
        $name       = basename(str_replace("\0", '', Post::name() ?? ''));

        if (!is_dir($parentPath)) JsonResponse::error('Dizin bulunamadı.', [], 400);
        if (!$name)               JsonResponse::error('Dosya adı gerekli.');

        $filePath = rtrim($parentPath, '/') . '/' . $name;
        if (file_exists($filePath)) JsonResponse::error('Bu isimde dosya zaten var.');

        if (file_put_contents($filePath, '') === false) JsonResponse::error('Dosya oluşturulamadı.');

        AuditLogger::log('filemanager.newfile', 'system', null, $filePath);
        JsonResponse::success('Dosya oluşturuldu.', ['path' => $parentPath, 'file' => $filePath]);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Dosya kaydet (editörden)
    // ────────────────────────────────────────────────────────────

    public function save()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $path    = $this->safePath(Post::path());
        $content = Post::content() ?? '';

        if (!is_file($path)) JsonResponse::error('Dosya bulunamadı.', [], 404);

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, self::EDITABLE_EXT, true)) {
            JsonResponse::error('Bu dosya türü düzenlenemez.');
        }

        if (file_put_contents($path, $content) === false) {
            JsonResponse::error('Dosya kaydedilemedi. Yazma izni yok olabilir.');
        }

        AuditLogger::log('filemanager.save', 'system', null, $path);
        JsonResponse::success('Dosya kaydedildi.');
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: İzinleri değiştir (chmod)
    // ────────────────────────────────────────────────────────────

    public function chmod()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $path = $this->safePath(Post::path());
        $mode = Post::mode() ?? '';

        if (!file_exists($path)) JsonResponse::error('Dosya/dizin bulunamadı.', [], 404);

        // mode: 4 basamaklı oktal string (örn. 0755 veya 755)
        if (!preg_match('/^[0-7]{3,4}$/', $mode)) {
            JsonResponse::error('Geçersiz izin formatı. Örn: 755 veya 0755');
        }

        $octal = octdec($mode);
        if (!@chmod($path, $octal)) JsonResponse::error('chmod başarısız.');

        AuditLogger::log('filemanager.chmod', 'system', null, "$path → $mode");
        JsonResponse::success('İzinler güncellendi: ' . $mode);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Zip sıkıştır
    // ────────────────────────────────────────────────────────────

    public function compress()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $path   = $this->safePath(Post::path());
        $parent = dirname($path);

        if (!file_exists($path)) JsonResponse::error('Dosya/dizin bulunamadı.', [], 404);

        $zipName = basename($path) . '_' . date('Ymd_His') . '.zip';
        $zipPath = $parent . '/' . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            JsonResponse::error('Zip dosyası oluşturulamadı.');
        }

        $this->addToZip($zip, $path, basename($path));
        $zip->close();

        AuditLogger::log('filemanager.compress', 'system', null, "$path → $zipPath");
        JsonResponse::success("$zipName oluşturuldu.", ['path' => $parent]);
    }

    // ────────────────────────────────────────────────────────────
    // AJAX POST: Zip aç
    // ────────────────────────────────────────────────────────────

    public function extract()
    {
        if (!Http::isRequestMethod('post')) JsonResponse::error('POST gerekli.', [], 405);
        if (!CsrfGuard::verify())           JsonResponse::error('Geçersiz istek.', [], 403);

        $path   = $this->safePath(Post::path());
        $parent = dirname($path);

        if (!is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'zip') {
            JsonResponse::error('Geçerli bir zip dosyası değil.', [], 400);
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) JsonResponse::error('Zip açılamadı.');

        $extractTo = $parent . '/' . pathinfo($path, PATHINFO_FILENAME);
        @mkdir($extractTo, 0755, true);
        $zip->extractTo($extractTo);
        $zip->close();

        AuditLogger::log('filemanager.extract', 'system', null, "$path → $extractTo");
        JsonResponse::success('Zip açıldı.', ['path' => $parent]);
    }

    // ────────────────────────────────────────────────────────────
    // Private helpers
    // ────────────────────────────────────────────────────────────

    /** Güvenli, normalize edilmiş mutlak yol döndür */
    private function safePath(?string $path, string $default = '/home'): string
    {
        if (!$path) return $default;

        // Null byte temizle
        $path = str_replace("\0", '', $path);

        // realpath() open_basedir dışına çıkan yollarda exception fırlatır;
        // '..' segmentlerini manuel çöz
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') continue;
            if ($part === '..') { array_pop($parts); } else { $parts[] = $part; }
        }
        $real = '/' . implode('/', $parts);

        foreach (self::ALLOWED_ROOTS as $root) {
            // '/home' kökü '/homeevil' gibi yolları geçirmesin
            if ($real === $root || str_starts_with($real, rtrim($root, '/') . '/')) {
                return $real;
            }
        }

        return $default;
    }

    /** Dizin içeriğini listele, sıralı stdClass dizisi döndür */
    private function listDir(string $path): array
    {
        if (!is_dir($path)) return [];

        $items = @scandir($path);
        if (!$items) return [];

        $result = [];
        foreach ($items as $name) {
            if ($name === '.') continue;

            $full  = $path . '/' . $name;
            // '..' için is_dir()/stat() çağırma — /home/.. = / olur, open_basedir dışına çıkar
            $isDir = ($name === '..') ? true : is_dir($full);
            $stat  = ($name === '..') ? false : @stat($full);
            $mode  = $stat['mode'] ?? 0;
            $ext   = $isDir ? '' : strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $size  = $isDir ? null : ($stat['size'] ?? 0);

            $result[] = (object)[
                'name'        => $name,
                'path'        => $full,
                'is_dir'      => $isDir,
                'is_parent'   => ($name === '..'),
                'size'        => $size,
                'size_human'  => $size !== null ? $this->humanSize($size) : '—',
                'modified'    => $stat ? date('d.m.Y H:i', $stat['mtime']) : '—',
                'permissions' => substr(sprintf('%o', $mode), -4),
                'owner'       => $this->ownerName($stat['uid'] ?? 0),
                'ext'         => $ext,
                'icon'        => $this->fileIcon($isDir, $ext),
                'editable'    => !$isDir && in_array($ext, self::EDITABLE_EXT, true),
            ];
        }

        // Dizinler önce, .. her zaman en başta, sonra alfabetik
        usort($result, function ($a, $b) {
            if ($a->is_parent) return -1;
            if ($b->is_parent) return 1;
            if ($a->is_dir !== $b->is_dir) return $b->is_dir - $a->is_dir;
            return strcasecmp($a->name, $b->name);
        });

        return $result;
    }

    /** Yol parçacıklarından breadcrumb dizisi oluştur */
    private function buildBreadcrumb(string $path): array
    {
        $parts  = explode('/', trim($path, '/'));
        $crumbs = [['label' => '/', 'path' => '/']];
        $acc    = '';

        foreach ($parts as $part) {
            if ($part === '') continue;
            $acc .= '/' . $part;
            // Sadece allowed roots içinde olan kısımları göster
            $safe = $this->safePath($acc, '');
            if ($safe) {
                $crumbs[] = ['label' => $part, 'path' => $safe];
            }
        }

        return $crumbs;
    }

    /** Boyutu okunabilir formata çevir */
    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024)             return $bytes . ' B';
        if ($bytes < 1048576)          return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824)       return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    /** UID'den kullanıcı adı al */
    private function ownerName(int $uid): string
    {
        if (!function_exists('posix_getpwuid')) return (string)$uid;
        $pw = @posix_getpwuid($uid);
        return $pw['name'] ?? (string)$uid;
    }

    /** Dosya tipine göre Tabler icon adı */
    private function fileIcon(bool $isDir, string $ext): string
    {
        if ($isDir) return 'ti-folder text-yellow';

        return match($ext) {
            'php','phtml'      => 'ti-brand-php text-purple',
            'js','ts','jsx'    => 'ti-brand-javascript text-yellow',
            'html','htm'       => 'ti-brand-html5 text-orange',
            'css','scss'       => 'ti-brand-css3 text-blue',
            'json'             => 'ti-braces text-teal',
            'xml','yaml','yml' => 'ti-file-code text-teal',
            'sql'              => 'ti-database text-cyan',
            'sh','bash'        => 'ti-terminal text-green',
            'md'               => 'ti-markdown text-gray',
            'zip','gz','tar','bz2','xz','rar' => 'ti-file-zip text-orange',
            'jpg','jpeg','png','gif','webp','svg','ico' => 'ti-photo text-pink',
            'pdf'              => 'ti-file-type-pdf text-red',
            'log'              => 'ti-file-text text-gray',
            'conf','ini','env','config' => 'ti-settings text-muted',
            default            => 'ti-file text-muted',
        };
    }

    /** Dizini recursive sil */
    private function deleteRecursive(string $dir): bool
    {
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->deleteRecursive($path) : @unlink($path);
        }
        return @rmdir($dir);
    }

    /** Dizini recursive kopyala */
    private function copyRecursive(string $src, string $dst): bool
    {
        @mkdir($dst, 0755, true);
        foreach (scandir($src) as $item) {
            if ($item === '.' || $item === '..') continue;
            $s = $src . '/' . $item;
            $d = $dst . '/' . $item;
            is_dir($s) ? $this->copyRecursive($s, $d) : @copy($s, $d);
        }
        return true;
    }

    /** Zip'e dosya/dizin ekle */
    private function addToZip(\ZipArchive $zip, string $path, string $localName)
    {
        if (is_dir($path)) {
            $zip->addEmptyDir($localName);
            foreach (scandir($path) as $item) {
                if ($item === '.' || $item === '..') continue;
                $this->addToZip($zip, "$path/$item", "$localName/$item");
            }
        } else {
            $zip->addFile($path, $localName);
        }
    }
}
