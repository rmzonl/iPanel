# iPanel — Sistem Mimarisi Manifestosu

> Plesk / cPanel benzeri, ZN Framework Custom Edition üzerine inşa edilmiş açık kaynak sunucu yönetim paneli.

---

## 1. GENEL MİMARİ

```
┌─────────────────────────────────────────────┐
│                  iPanel Web UI              │
│         (ZN Framework + Tabler/BS5)         │
└──────────────────┬──────────────────────────┘
                   │ HTTP / Unix Socket
┌──────────────────▼──────────────────────────┐
│              iPanel Agent (Daemon)          │
│         PHP CLI / Root yetkisi              │
│    /usr/local/ipanel/agent/agent.php        │
└──┬──────┬──────┬──────┬──────┬──────┬──────┘
   │      │      │      │      │      │
  Web    DNS    Mail   FTP   Nginx  System
 Srvr   Bind   Postfix Pure  Conf   Tools
Apache   9    Dovecot  FTPd
```

**Temel prensip:** Web UI daima unprivileged bir kullanıcı olarak çalışır (`www-data`). Root gerektiren tüm işlemler **iPanel Agent** üzerinden yapılır.

---

## 2. ROOT YETKİSİ — AGENT MİMARİSİ

### 2.1 Agent Daemon

```
/usr/local/ipanel/
├── agent/
│   ├── agent.php          # Ana daemon (root olarak çalışır)
│   ├── modules/           # Her modülün komut işleyicisi
│   │   ├── NginxModule.php
│   │   ├── ApacheModule.php
│   │   ├── DnsModule.php
│   │   ├── MailModule.php
│   │   ├── PhpModule.php
│   │   └── ...
│   └── agent.sock         # Unix domain socket
├── web/                   # iPanel Web UI (www-data)
└── bin/
    └── ipanel             # CLI yönetim aracı
```

### 2.2 İletişim Protokolü

```
Web UI (www-data)
    │
    │  JSON-RPC over Unix Socket
    │  { "action": "nginx.reload", "params": {...}, "token": "..." }
    ▼
Agent Daemon (root)
    │
    │  Komutu validate et → çalıştır → yanıt dön
    ▼
{ "status": "ok", "output": "..." }
```

**Güvenlik katmanları:**
- Socket dosyası `chmod 660`, group = `ipanel`
- Web kullanıcısı `www-data` → `ipanel` grubuna eklenir
- Her istek için HMAC-SHA256 imzalı token (shared secret)
- Whitelist komut sistemi — agent sadece tanımlı aksiyonları çalıştırır
- Rate limiting — saniyede max N istek

### 2.3 Systemd Servisi

```ini
# /etc/systemd/system/ipanel-agent.service
[Unit]
Description=iPanel Agent Daemon
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/bin/php /usr/local/ipanel/agent/agent.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

---

## 3. KURULUM SİSTEMİ

### 3.1 Installer Script

```bash
curl -sL https://get.ipanel.io | bash
# veya
wget -O - https://get.ipanel.io | bash
```

**Installer akışı:**

```
1. OS Tespiti
   ├── Ubuntu 20.04 / 22.04 / 24.04
   ├── Debian 11 / 12
   ├── AlmaLinux / Rocky 8 / 9
   └── CentOS Stream 9

2. Bağımlılık Kurulumu
   ├── Nginx (veya Apache)
   ├── PHP 8.2 (default) + php-fpm
   ├── MySQL 8.0 / MariaDB 10.11
   ├── Postfix + Dovecot
   ├── BIND9 veya PowerDNS
   ├── PureFTPd
   ├── Redis (session/cache)
   └── Certbot (Let's Encrypt)

3. iPanel Dosyaları
   ├── /usr/local/ipanel/ kopyala
   ├── DB schema kur
   ├── Admin kullanıcı oluştur
   └── Secret key üret

4. Sistem Entegrasyonu
   ├── ipanel-agent.service etkinleştir
   ├── Nginx vhost oluştur (panel.domain.com)
   ├── SSL kur (Let's Encrypt)
   ├── Firewall kuralları (UFW/firewalld)
   └── Cron görevleri ekle

5. Post-install
   ├── Erişim bilgilerini göster
   └── Sağlık kontrolü yap
```

### 3.2 CLI Aracı

```bash
ipanel status              # Tüm servislerin durumu
ipanel update              # Panel güncelle
ipanel backup              # Panel yedeği al
ipanel passwd admin        # Admin şifresi değiştir
ipanel logs nginx          # Nginx logları izle
ipanel repair              # Bozuk yapılandırmaları onar
```

---

## 4. MODÜLLER

### 4.1 Site / Hosting Modülü

**Bir site oluşturulduğunda agent şunları yapar:**

```
1. Linux kullanıcısı oluştur
   useradd -m -s /bin/bash site_username
   passwd -l site_username   # login engelle, sadece FTP/SFTP

2. Dizin yapısı kur
   /home/site_username/
   ├── public_html/          # webroot
   ├── logs/                 # access + error log
   ├── tmp/
   ├── ssl/                  # sertifika dosyaları
   └── backups/

3. PHP-FPM pool oluştur
   /etc/php/8.2/fpm/pool.d/site_username.conf
   user = site_username
   group = site_username
   listen = /run/php/site_username.sock

4. Nginx vhost yaz
   /etc/nginx/sites-available/site_username.conf
   → sites-enabled symlink

5. Nginx reload
```

**Her site kendi Linux kullanıcısı = tam izolasyon.**

---

### 4.2 PHP Versiyon Yönetimi

**Çoklu PHP versiyonu — ondrej/php PPA (Ubuntu) veya Remi repo (RHEL):**

```
Kurulu versiyonlar:
/usr/bin/php7.4
/usr/bin/php8.0
/usr/bin/php8.1
/usr/bin/php8.2   ← default
/usr/bin/php8.3

Her versiyon için ayrı FPM:
/etc/php/7.4/fpm/pool.d/
/etc/php/8.2/fpm/pool.d/
```

**Site için PHP değiştirme akışı:**

```
Web UI → Agent
{action: "php.switch", site: "site_username", version: "8.1"}

Agent:
1. Eski FPM pool sil
2. Yeni versiyonun pool conf'unu yaz
3. Nginx vhost güncelle (fastcgi_pass)
4. php8.1-fpm reload
5. nginx reload
```

---

### 4.3 Node.js Versiyon Yönetimi

**NVM sistem geneli, PM2 ile süreç yönetimi:**

```
Site yapılandırması:
{
  "node_version": "20",
  "node_app_path": "/home/site_username/app/",
  "node_start_cmd": "npm start",
  "node_port": 3000,
  "proxy_enabled": true
}

Agent:
1. pm2 start ... --name site_username --user site_username
2. Nginx reverse proxy ekle
3. PM2 startup → sistem başlangıcında otomatik
```

---

### 4.4 SSL Modülü

**Let's Encrypt (Otomatik):**
```bash
certbot certonly --webroot \
  -w /home/site_username/public_html \
  -d domain.com -d www.domain.com \
  --non-interactive --agree-tos -m admin@domain.com
```

**Ücretli SSL (Manuel):**
```
UI → .crt + .key + .ca-bundle yükle
Agent → /home/site_username/ssl/ kopyala → nginx reload
```

**Desteklenen türler:** Let's Encrypt, Wildcard (DNS challenge), Ücretli, Self-signed

---

### 4.5 DNS Modülü

**Backend: BIND9 veya PowerDNS (kurulumda seçilir)**

```
BIND9:
/etc/bind/zones/domain.com.zone  ← agent yazar
named-checkzone → rndc reload

PowerDNS:
MySQL backend → zones/records tablolarına INSERT
pdnsutil rectify-zone domain.com
```

**Desteklenen kayıt tipleri:** A, AAAA, CNAME, MX, TXT, NS, SRV, CAA, PTR

---

### 4.6 Mail Modülü

**Stack: Postfix + Dovecot + SpamAssassin + ClamAV**

```
Özellikler:
- SMTP (25, 587 STARTTLS, 465 SSL)
- IMAP (143 STARTTLS, 993 SSL)
- POP3 (110, 995 SSL)
- Webmail (Roundcube entegrasyonu)
- Spam filtresi eşiği ayarlanabilir
- Forwarder, alias, catch-all
- Mailbox kota
- DKIM otomatik key üret + DNS'e ekle
- SPF, DMARC önerisi
```

---

### 4.7 FTP Modülü

**PureFTPd — Virtual User sistemi:**

```
pure-pw useradd ftpuser \
  -u site_username \
  -d /home/site_username/public_html \
  -m

Pasif port: 40000-50000
SFTP: OpenSSH üzerinden site Linux kullanıcısıyla
```

---

### 4.8 Veritabanı Modülü

```
MySQL/MariaDB:
- Her site için ayrı DB kullanıcısı
- Kullanıcı sadece kendi DB'lerine erişebilir
- DB boyut izleme
- Import/Export (büyük dosyalar CLI üzerinden)
- Remote access toggle (IP whitelist)
```

---

### 4.9 Backup Modülü

```
Backup türleri:
┌──────────────┬─────────────────────────────┐
│ Full Site    │ files + DB + config         │
│ Files Only   │ sadece public_html          │
│ DB Only      │ mysqldump                   │
│ Incremental  │ rsync delta                 │
└──────────────┴─────────────────────────────┘

Depolama hedefleri:
- Lokal (/var/backups/ipanel/)
- SFTP (uzak sunucu)
- S3 / Wasabi / Backblaze B2 (s3cmd)
- Google Drive / OneDrive (rclone)

Zamanlama:
- Cron tabanlı (günlük/haftalık/aylık)
- Retention policy
- Sıkıştırma: tar.gz / tar.zst
```

---

### 4.10 Firewall Modülü

```
Backend: UFW (Ubuntu) / firewalld (RHEL)

- Port aç/kapat
- IP whitelist / blacklist
- Country block (ipset)
- DDoS basic koruma (iptables rate limit)
- Fail2Ban entegrasyonu
```

---

### 4.11 Cron Modülü

```
Her site kullanıcısının crontab'ı yönetilir:
crontab -u site_username

Özellikler:
- Görsel cron builder
- Son çalışma zamanı + çıktı log
- Email bildirimi (hata durumunda)
```

---

## 5. ionCube / PHPKoru / PHP ENCODER ENTEGRASYONU

### 5.1 ionCube Loader

```
Kurulum:
ioncube_loader_lin_8.2.so → /usr/lib/php/ioncube/
/etc/php/8.2/mods-available/ioncube.ini:
  zend_extension=/usr/lib/php/ioncube/ioncube_loader_lin_8.2.so
phpenmod -v 8.2 ioncube
php8.2-fpm restart
```

### 5.2 Desteklenen Encoder'lar

```
┌─────────────────┬──────────────────────────────┐
│ ionCube         │ En yaygın, ücretsiz loader   │
│ SourceGuardian  │ sg_load.so                   │
│ Zend Guard      │ ZendGuard (eski PHP)         │
│ PHPKoru         │ phpkoru_loader.so            │
│ Phar            │ Yerleşik PHP, ek kurulum yok │
└─────────────────┴──────────────────────────────┘
```

### 5.3 Panel UI

```
Uzantılar sekmesi:
[✓] ionCube Loader   v13.0  [Kaldır]
[ ] SourceGuardian   v13.0  [Kur]
[✓] Redis Extension         [Kaldır]
[✓] ImageMagick             [Kaldır]

Her uzantı, hangi PHP versiyonunda aktif → toggle
```

---

## 6. HİYERARŞİK AYARLAR SİSTEMİ

```
SERVER (Global varsayılan)
  └── CLIENT (Override edebilir)
        └── SITE (Override edebilir)
```

**Veritabanı yapısı:**

```sql
settings tablosu:
| level  | level_id | key          | value |
|--------|----------|--------------|-------|
| server | NULL     | php_memory   | 128M  |
| client | 5        | php_memory   | 256M  |
| site   | 12       | php_memory   | 512M  |
```

**Değer okuma önceliği:** site → client → server

---

## 7. MONİTÖRİNG

```
Gerçek zamanlı:
- CPU / RAM / Disk / Network grafikleri
- Nginx active connections
- MySQL query rate
- PHP-FPM pool durumu

Site bazlı:
- Bandwidth kullanımı
- Request sayısı / response time
- Error rate (4xx/5xx)
- DB boyutu büyüme

Alertler:
- Disk %80 dolu → email
- CPU %90 üzeri > 5dk → email
- Site down → email + webhook
- SSL sertifikası 30 gün içinde dolacak → email
```

---

## 8. GÜVENLİK MİMARİSİ

```
Katman 1: Network
  - Firewall (UFW/firewalld)
  - Fail2Ban (brute force)
  - DDoS basic mitigation

Katman 2: OS
  - Her site = ayrı Linux kullanıcısı
  - PHP-FPM process isolation

Katman 3: Web
  - Nginx security headers
  - ModSecurity WAF (opsiyonel)
  - Rate limiting per-site

Katman 4: Panel
  - 2FA (TOTP)
  - IP whitelist panel erişimi
  - Session timeout
  - Tüm işlemler audit log'a yazılır

Katman 5: Agent
  - Unix socket (network'e kapalı)
  - Whitelist komut sistemi
  - HMAC doğrulama
```

---

## 9. TEKNOLOJİ YIĞINI

| Katman      | Teknoloji                          |
|-------------|-------------------------------------|
| Framework   | ZN Framework Custom Edition        |
| UI          | Bootstrap 5 + Tabler Theme         |
| Template    | ZN Wizard Engine                   |
| Web Server  | Nginx (default) / Apache           |
| PHP         | 7.4 – 8.3 (çoklu versiyon)        |
| Database    | MySQL 8.0 / MariaDB 10.11          |
| Mail        | Postfix + Dovecot                  |
| DNS         | BIND9 / PowerDNS                   |
| FTP         | PureFTPd                           |
| SSL         | Let's Encrypt (Certbot) / Manuel   |
| Process     | PM2 (Node.js uygulamaları)         |
| Cache       | Redis                              |
| Agent IPC   | Unix Socket (JSON-RPC)             |

---

## 10. YAYIM PLANI

```
v0.1 — Core (Mevcut)
  Web UI, Auth, DB schema, Masterpage

v0.2 — Hosting Engine
  Site/domain/PHP/SSL yönetimi çalışır

v0.3 — Mail + DNS
  Postfix/Dovecot + BIND9/PowerDNS

v0.4 — FTP + DB + Cron
  PureFTPd, MySQL yönetimi, Cron UI

v0.5 — Backup + Firewall
  Yedekleme sistemi, güvenlik modülleri

v0.6 — Monitoring
  Gerçek zamanlı grafik, alertler

v1.0 — Stable
  ionCube/encoder desteği, lisanslama,
  auto-update sistemi
```

---

## 11. VERİTABANI ŞEMASI (ÖZET)

| Tablo              | Açıklama                          |
|--------------------|-----------------------------------|
| `users`            | Panel admin kullanıcıları         |
| `clients`          | Müşteriler                        |
| `ip_addresses`     | Shared/Dedicated IP havuzu        |
| `sites`            | Hosting hesapları                 |
| `domains`          | Domain / subdomain kayıtları      |
| `ssl_certificates` | SSL sertifikaları                 |
| `email_accounts`   | E-posta hesapları                 |
| `dns_zones`        | DNS zone'ları                     |
| `dns_records`      | DNS kayıtları (A, MX, TXT...)     |
| `ftp_accounts`     | FTP kullanıcıları                 |
| `site_databases`   | MySQL veritabanları               |
| `backups`          | Yedek kayıtları                   |
| `cron_jobs`        | Zamanlanmış görevler              |
| `firewall_rules`   | Güvenlik duvarı kuralları         |
| `settings`         | Hiyerarşik ayarlar (srv>cli>site) |
