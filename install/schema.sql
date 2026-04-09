CREATE DATABASE IF NOT EXISTS ipanel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ipanel;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'reseller') DEFAULT 'admin',
    status TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    country VARCHAR(100) NULL DEFAULT 'TR',
    status ENUM('active', 'suspended', 'terminated') DEFAULT 'active',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE ip_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL UNIQUE,
    netmask VARCHAR(45) NULL,
    gateway VARCHAR(45) NULL,
    type ENUM('server', 'shared', 'dedicated') DEFAULT 'shared',
    client_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
);

CREATE TABLE sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    domain VARCHAR(255) NOT NULL UNIQUE,
    ip_id INT NULL,
    document_root VARCHAR(500) NULL,
    php_version VARCHAR(20) DEFAULT '8.2',
    status ENUM('active', 'suspended', 'deleted') DEFAULT 'active',
    disk_quota INT DEFAULT 0,
    bandwidth_quota INT DEFAULT 0,
    disk_used INT DEFAULT 0,
    bandwidth_used INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (ip_id) REFERENCES ip_addresses(id) ON DELETE SET NULL
);

CREATE TABLE domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    client_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('main', 'addon', 'subdomain', 'alias') DEFAULT 'main',
    redirect_to VARCHAR(255) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

CREATE TABLE ssl_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    type ENUM('letsencrypt', 'paid', 'self_signed') DEFAULT 'letsencrypt',
    cert_file TEXT NULL,
    key_file TEXT NULL,
    chain_file TEXT NULL,
    issued_at DATETIME NULL,
    expires_at DATETIME NULL,
    auto_renew TINYINT(1) DEFAULT 1,
    status ENUM('active', 'expired', 'pending', 'failed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

CREATE TABLE email_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    quota INT DEFAULT 1024,
    used INT DEFAULT 0,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

CREATE TABLE dns_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    soa_email VARCHAR(255) NULL,
    ttl INT DEFAULT 86400,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

CREATE TABLE dns_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone_id INT NOT NULL,
    type ENUM('A', 'AAAA', 'CNAME', 'MX', 'TXT', 'NS', 'SRV', 'CAA', 'PTR') NOT NULL,
    name VARCHAR(255) NOT NULL,
    value TEXT NOT NULL,
    priority INT DEFAULT 0,
    ttl INT DEFAULT 3600,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_id) REFERENCES dns_zones(id) ON DELETE CASCADE
);

CREATE TABLE ftp_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    home_dir VARCHAR(500) NULL,
    quota INT DEFAULT 0,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

CREATE TABLE site_databases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    db_name VARCHAR(100) NOT NULL,
    db_user VARCHAR(100) NOT NULL,
    db_password VARCHAR(255) NOT NULL,
    charset VARCHAR(50) DEFAULT 'utf8mb4',
    size_mb INT DEFAULT 0,
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

CREATE TABLE backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NULL,
    client_id INT NULL,
    type ENUM('full', 'database', 'files', 'email') DEFAULT 'full',
    filename VARCHAR(500) NULL,
    size_mb INT DEFAULT 0,
    storage_path VARCHAR(500) NULL,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL
);

CREATE TABLE cron_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    command TEXT NOT NULL,
    schedule VARCHAR(100) NOT NULL,
    last_run DATETIME NULL,
    next_run DATETIME NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scope ENUM('server', 'client', 'site') NOT NULL,
    scope_id INT NULL,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_setting (scope, scope_id, setting_key)
);

CREATE TABLE firewall_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    action ENUM('allow', 'deny') NOT NULL,
    protocol ENUM('tcp', 'udp', 'icmp', 'all') DEFAULT 'tcp',
    direction ENUM('in', 'out', 'both') DEFAULT 'in',
    source_ip VARCHAR(45) NULL,
    dest_port VARCHAR(20) NULL,
    priority INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Admin kullanıcısı — şifre kurulum betiği tarafından randomize edilir.
-- Schema yüklendiğinde geçici placeholder; install.sh bu satırı UPDATE ile değiştirir.
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@ipanel.local', '$2y$12$PLACEHOLDER_REPLACED_BY_INSTALLER_DO_NOT_USE', 'admin');

-- Default server settings
INSERT INTO settings (scope, scope_id, setting_key, setting_value) VALUES
('server', NULL, 'panel_name', 'iPanel'),
('server', NULL, 'server_ip', '127.0.0.1'),
('server', NULL, 'php_versions', '7.4,8.0,8.1,8.2,8.3'),
('server', NULL, 'max_sites_per_client', '0'),
('server', NULL, 'max_disk_per_client', '0'),
('server', NULL, 'max_bandwidth_per_client', '0'),
('server', NULL, 'default_php_version', '8.2'),
('server', NULL, 'ssl_email', 'admin@ipanel.local'),
('server', NULL, 'backup_path', '/var/backups/ipanel'),
('server', NULL, 'webroot_base', '/var/www');

-- ============================================================
-- Güvenlik tabloları — v0.2.0
-- ============================================================

-- Reseller izolasyonu: müşterinin hangi reseller'a ait olduğu
ALTER TABLE clients ADD COLUMN IF NOT EXISTS reseller_id INT NULL AFTER id;
ALTER TABLE clients ADD CONSTRAINT fk_clients_reseller
    FOREIGN KEY IF NOT EXISTS (reseller_id) REFERENCES users(id) ON DELETE SET NULL;

-- Login brute-force koruması
CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    ip           VARCHAR(45)  NOT NULL,
    username     VARCHAR(100) NULL,
    attempted_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time       (ip, attempted_at),
    INDEX idx_username_time (username, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Denetim logu
CREATE TABLE IF NOT EXISTS audit_logs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT          NULL,
    username      VARCHAR(100) NULL,
    action        VARCHAR(100) NOT NULL,
    resource_type VARCHAR(100) NULL,
    resource_id   INT          NULL,
    description   TEXT         NULL,
    ip            VARCHAR(45)  NULL,
    user_agent    TEXT         NULL,
    created_at    DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user     (user_id),
    INDEX idx_action   (action),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_created  (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================================
-- 2FA ve API Token tabloları — v0.3.0
-- ============================================================

-- Kullanıcıya TOTP 2FA desteği ekle
ALTER TABLE users ADD COLUMN IF NOT EXISTS totp_secret  VARCHAR(64)  NULL AFTER role;
ALTER TABLE users ADD COLUMN IF NOT EXISTS totp_enabled TINYINT(1)   DEFAULT 0 AFTER totp_secret;
ALTER TABLE users ADD COLUMN IF NOT EXISTS totp_backup  TEXT         NULL AFTER totp_enabled;

-- API erişim token'ları
CREATE TABLE IF NOT EXISTS api_tokens (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    name        VARCHAR(100) NOT NULL,
    token_hash  VARCHAR(64)  NOT NULL UNIQUE,
    permissions TEXT         NULL COMMENT 'JSON dizi: ["clients.view","sites.view"]',
    last_used   DATETIME     NULL,
    expires_at  DATETIME     NULL,
    status      ENUM('active','revoked') DEFAULT 'active',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_hash (token_hash),
    INDEX idx_user_id    (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- ============================================================
-- Arka plan iş kuyruğu — v0.4.0
-- ============================================================

CREATE TABLE IF NOT EXISTS jobs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    uuid         CHAR(36)      NOT NULL UNIQUE,
    type         VARCHAR(100)  NOT NULL COMMENT 'Örn: sites.create, ssl.issue',
    payload      JSON          NOT NULL COMMENT 'İş parametreleri',
    status       ENUM('pending','running','completed','failed','cancelled')
                               DEFAULT 'pending',
    result       JSON          NULL     COMMENT 'Çıktı / hata detayı',
    user_id      INT           NULL,
    username     VARCHAR(100)  NULL,
    priority     TINYINT       DEFAULT 5 COMMENT '1=yüksek 5=normal 9=düşük',
    attempts     TINYINT       DEFAULT 0,
    max_attempts TINYINT       DEFAULT 3,
    created_at   DATETIME      DEFAULT CURRENT_TIMESTAMP,
    started_at   DATETIME      NULL,
    completed_at DATETIME      NULL,
    INDEX idx_status_priority (status, priority, created_at),
    INDEX idx_user            (user_id),
    INDEX idx_type            (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


-- Admin: varsayılan şifreyi güvenli ile değiştir
-- Kurulum betiği tarafından oluşturulacak, burada placeholder
-- (install.sh bu INSERT'i yapar)
