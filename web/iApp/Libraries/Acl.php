<?php namespace Project\Libraries;

use DB;
use Session;
use Redirect;

/**
 * iPanel ACL — Rol tabanlı erişim kontrolü
 *
 * Roller: admin (tam yetki), reseller (kısıtlı + sadece kendi müşterileri)
 */
class Acl
{
    // Her controller::method için gereken izin
    private static array $map = [
        'dashboard' => ['*' => 'dashboard.view'],
        'clients'   => [
            'main'   => 'clients.view',
            'create' => 'clients.create',
            'store'  => 'clients.create',
            'edit'   => 'clients.edit',
            'update' => 'clients.edit',
            'delete' => 'clients.delete',
        ],
        'sites' => [
            'main'   => 'sites.view',
            'create' => 'sites.create',
            'store'  => 'sites.create',
            'edit'   => 'sites.edit',
            'update' => 'sites.edit',
            'delete' => 'sites.delete',
        ],
        'domains' => [
            'main'   => 'domains.view',
            'create' => 'domains.create',
            'store'  => 'domains.create',
            'delete' => 'domains.delete',
        ],
        'databases' => [
            'main'   => 'databases.view',
            'create' => 'databases.create',
            'store'  => 'databases.create',
            'delete' => 'databases.delete',
        ],
        'email' => [
            'main'   => 'email.view',
            'create' => 'email.create',
            'store'  => 'email.create',
            'delete' => 'email.delete',
        ],
        'ftp' => [
            'main'   => 'ftp.view',
            'create' => 'ftp.create',
            'store'  => 'ftp.create',
            'delete' => 'ftp.delete',
        ],
        'ssl' => [
            'main'   => 'ssl.view',
            'create' => 'ssl.create',
            'store'  => 'ssl.create',
        ],
        'dns' => [
            'main'         => 'dns.view',
            'records'      => 'dns.view',
            'createRecord' => 'dns.create',
            'storeRecord'  => 'dns.create',
            'deleteZone'   => 'dns.delete',
            'deleteRecord' => 'dns.delete',
        ],
        'cronjobs' => [
            'main'   => 'cronjobs.view',
            'create' => 'cronjobs.create',
            'store'  => 'cronjobs.create',
            'delete' => 'cronjobs.delete',
        ],
        'backups' => [
            'main'   => 'backups.view',
            'create' => 'backups.create',
        ],
        'ipaddresses' => [
            'main'   => 'ipaddresses.view',
            'create' => 'ipaddresses.create',
            'store'  => 'ipaddresses.create',
            'delete' => 'ipaddresses.delete',
        ],
        'firewall' => [
            'main'   => 'firewall.view',
            'create' => 'firewall.create',
            'store'  => 'firewall.create',
            'delete' => 'firewall.delete',
        ],
        'settings' => [
            'main' => 'settings.view',
            'save' => 'settings.edit',
        ],
    ];

    // Rol → izin seti
    private static array $rolePermissions = [
        'admin' => ['*'], // her şey
        'reseller' => [
            'dashboard.view',
            'clients.view',   'clients.create', 'clients.edit',   'clients.delete',
            'sites.view',     'sites.create',   'sites.edit',     'sites.delete',
            'domains.view',   'domains.create', 'domains.delete',
            'databases.view', 'databases.create','databases.delete',
            'email.view',     'email.create',   'email.delete',
            'ftp.view',       'ftp.create',      'ftp.delete',
            'ssl.view',       'ssl.create',
            'dns.view',       'dns.create',      'dns.delete',
            'cronjobs.view',  'cronjobs.create', 'cronjobs.delete',
            'backups.view',   'backups.create',
            // ipaddresses, firewall, settings → reseller erişemez
        ],
    ];

    /** Aktif kullanıcı session'dan gelir */
    public static function user(): ?array
    {
        return Session::select('admin_user') ?: null;
    }

    /** Kullanıcının belirtilen izni var mı? */
    public static function can(string $permission, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;

        $role = $user['role'] ?? 'reseller';
        $perms = static::$rolePermissions[$role] ?? [];

        return in_array('*', $perms) || in_array($permission, $perms);
    }

    /** Controller + metod için gerekli izni döndür */
    public static function permissionFor(string $controller, string $method): ?string
    {
        $c = strtolower($controller);
        $m = strtolower($method);

        if (!isset(static::$map[$c])) return null;
        return static::$map[$c][$m] ?? static::$map[$c]['*'] ?? null;
    }

    /**
     * Gerekli izin yoksa dashboard'a yönlendir.
     * Controller'larda: Acl::require('clients.delete');
     */
    public static function require(string $permission): void
    {
        if (!static::can($permission)) {
            Session::insert('error', 'Bu işlem için yetkiniz yok.');
            Redirect::action('dashboard/main');
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // Sahiplik kontrolleri (reseller izolasyonu)
    // -------------------------------------------------------------------------

    /** Reseller bu müşteriye sahip mi? Admin her zaman erişebilir. */
    public static function ownsClient(int $clientId, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;

        $row = DB::table('clients')
            ->where('id', $clientId)
            ->where('reseller_id', $user['id'])
            ->get()->row();

        return !empty($row);
    }

    /** Reseller bu siteye sahip mi? (client üzerinden kontrol) */
    public static function ownsSite(int $siteId, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;

        $row = DB::table('sites')
            ->join('clients', 'clients.id = sites.client_id')
            ->where('sites.id', $siteId)
            ->where('clients.reseller_id', $user['id'])
            ->get()->row();

        return !empty($row);
    }

    /** Reseller bu domain'e sahip mi? */
    public static function ownsDomain(int $domainId, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;

        $row = DB::table('domains')
            ->join('clients', 'clients.id = domains.client_id')
            ->where('domains.id', $domainId)
            ->where('clients.reseller_id', $user['id'])
            ->get()->row();

        return !empty($row);
    }

    /** Sahiplik yoksa 403 yönlendirmesi */
    public static function requireOwnership(bool $owns): void
    {
        if (!$owns) {
            Session::insert('error', 'Bu kaynağa erişim yetkiniz yok.');
            Redirect::action('dashboard/main');
            exit;
        }
    }

    /** Reseller için client listesini filtrele */
    public static function filterClientsQuery(?array $user = null)
    {
        $user ??= static::user();
        $q = DB::table('clients');
        if ($user && $user['role'] === 'reseller') {
            $q = $q->where('reseller_id', $user['id']);
        }
        return $q;
    }

    // -------------------------------------------------------------------------
    // Site üzerinden kaynak sahipliği (databases, email, ftp, ssl, cronjobs, backups)
    // -------------------------------------------------------------------------

    /**
     * $table: veritabanı tablo adı (örn: 'site_databases', 'email_accounts', 'ftp_accounts', 'cron_jobs')
     * Bu tablolar site_id kolonu içermelidir.
     */
    public static function ownsSiteResource(string $table, int $resourceId, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;

        $row = DB::table($table)
            ->join('sites',   "sites.id   = {$table}.site_id")
            ->join('clients', 'clients.id = sites.client_id')
            ->where("{$table}.id", $resourceId)
            ->where('clients.reseller_id', $user['id'])
            ->get()->row();

        return !empty($row);
    }

    /** ssl_certificates domain_id üzerinden kontrol eder */
    public static function ownsSslCert(int $certId, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;

        $row = DB::table('ssl_certificates')
            ->join('domains',  'domains.id  = ssl_certificates.domain_id')
            ->join('clients',  'clients.id  = domains.client_id')
            ->where('ssl_certificates.id', $certId)
            ->where('clients.reseller_id', $user['id'])
            ->get()->row();

        return !empty($row);
    }

    /** dns_zones: domain_id → client_id kontrolü */
    public static function ownsDnsZone(int $zoneId, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;

        $row = DB::table('dns_zones')
            ->join('domains',  'domains.id  = dns_zones.domain_id')
            ->join('clients',  'clients.id  = domains.client_id')
            ->where('dns_zones.id', $zoneId)
            ->where('clients.reseller_id', $user['id'])
            ->get()->row();

        return !empty($row);
    }

    /** dns_records: zone_id → domain_id → client_id kontrolü */
    public static function ownsDnsRecord(int $recordId, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;

        $row = DB::table('dns_records')
            ->join('dns_zones', 'dns_zones.id = dns_records.zone_id')
            ->join('domains',   'domains.id   = dns_zones.domain_id')
            ->join('clients',   'clients.id   = domains.client_id')
            ->where('dns_records.id', $recordId)
            ->where('clients.reseller_id', $user['id'])
            ->get()->row();

        return !empty($row);
    }

    /** backups: site_id VEYA client_id üzerinden kontrol */
    public static function ownsBackup(int $backupId, ?array $user = null): bool
    {
        $user ??= static::user();
        if (!$user) return false;
        if ($user['role'] === 'admin') return true;

        $backup = DB::table('backups')->where('id', $backupId)->get()->row();
        if (!$backup) return false;

        if (!empty($backup->site_id)) {
            return static::ownsSiteResource('backups', $backupId, $user);
        }
        if (!empty($backup->client_id)) {
            return static::ownsClient((int) $backup->client_id, $user);
        }
        return false;
    }
}
