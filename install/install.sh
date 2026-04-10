#!/usr/bin/env bash
#
# iPanel — tek komut kurulum betiği
# Kullanım:
#   curl -sSL https://raw.githubusercontent.com/rmzonl/iPanel/develop/install/install.sh | sudo bash
#   veya: sudo bash install/install.sh
#
set -euo pipefail

IPANEL_VERSION="0.6.1"
IPANEL_ROOT="/usr/local/ipanel"
IPANEL_REPO="${IPANEL_REPO:-https://github.com/rmzonl/iPanel.git}"
IPANEL_BRANCH="${IPANEL_BRANCH:-develop}"

C_RED='\033[0;31m'; C_GRN='\033[0;32m'; C_YEL='\033[1;33m'; C_BLU='\033[0;34m'; C_RST='\033[0m'
log()  { echo -e "${C_BLU}==>${C_RST} $*"; }
ok()   { echo -e "  ${C_GRN}✓${C_RST}  $*"; }
warn() { echo -e "  ${C_YEL}!${C_RST}  $*"; }
fail() { echo -e "${C_RED}HATA:${C_RST} $*"; exit 1; }

[[ $EUID -eq 0 ]] || fail "iPanel kurulumu root olarak çalışmalıdır."

# cPanel/WHM tespiti — desteklenmiyor
if [[ -d /usr/local/cpanel || -f /usr/local/cpanel/cpanel ]]; then
    echo -e "${C_RED}"
    echo "  ╔══════════════════════════════════════════════════════════╗"
    echo "  ║  HATA: cPanel/WHM tespit edildi                         ║"
    echo "  ╠══════════════════════════════════════════════════════════╣"
    echo "  ║  iPanel, cPanel kurulu bir sunucuya kurulamaz.           ║"
    echo "  ║                                                          ║"
    echo "  ║  Lütfen temiz bir sunucu kullanın:                       ║"
    echo "  ║    • AlmaLinux 9 (önerilen)                              ║"
    echo "  ║    • Rocky Linux 9                                       ║"
    echo "  ║    • Ubuntu 22.04 / 24.04                                ║"
    echo "  ║    • Debian 12                                           ║"
    echo "  ╚══════════════════════════════════════════════════════════╝"
    echo -e "${C_RST}"
    exit 1
fi

###############################################################################
# 1. OS tespiti
###############################################################################
detect_os() {
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release
        OS_ID="$ID"
        OS_VER="${VERSION_ID%%.*}"
    else
        fail "İşletim sistemi tespit edilemedi"
    fi

    case "$OS_ID" in
        ubuntu|debian)
            PKG_UPDATE="apt-get update -qq"
            PKG_INSTALL="DEBIAN_FRONTEND=noninteractive apt-get install -y -q"
            ;;
        almalinux|rocky|centos|rhel)
            PKG_UPDATE="dnf -q makecache"
            PKG_INSTALL="dnf install -y -q"
            ;;
        *)
            fail "Desteklenmeyen OS: $OS_ID $OS_VER (Ubuntu 20/22/24, Debian 11/12, AlmaLinux/Rocky 8/9 desteklenir)"
            ;;
    esac

    log "OS: $OS_ID $OS_VER"
}

###############################################################################
# 2. Bağımlılıkları kur
###############################################################################
install_deps() {
    log "Paket listesi güncelleniyor..."
    $PKG_UPDATE

    log "Gerekli paketler kuruluyor..."

    if [[ "$OS_ID" == "ubuntu" || "$OS_ID" == "debian" ]]; then
        # PHP PPA (Ubuntu)
        if [[ "$OS_ID" == "ubuntu" ]]; then
            $PKG_INSTALL software-properties-common
            add-apt-repository -y ppa:ondrej/php >/dev/null 2>&1
            apt-get update -qq
        fi

        $PKG_INSTALL \
            curl wget git ca-certificates gnupg lsb-release \
            nginx \
            php8.2-cli php8.2-fpm php8.2-mysql php8.2-mbstring \
            php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath \
            php8.2-redis php8.2-intl php8.2-posix \
            mariadb-server mariadb-client \
            postfix dovecot-core dovecot-imapd dovecot-pop3d dovecot-lmtpd \
            bind9 bind9utils dnsutils \
            pure-ftpd \
            redis-server \
            certbot python3-certbot-nginx \
            ufw fail2ban \
            tar gzip rsync openssl

        # headers-more modülü — isteğe bağlı
        $PKG_INSTALL libnginx-mod-http-headers-more-headers 2>/dev/null \
            || warn "nginx headers-more modülü bulunamadı — Server header gizlenemeyecek (kurulum devam ediyor)"
    else
        # Adım 1: temel araçlar + EPEL
        dnf install -y curl wget git ca-certificates gnupg tar gzip rsync openssl
        dnf install -y epel-release
        dnf makecache

        # Adım 2: Remi repo (PHP 8.2 için)
        local REMI_RPM="https://rpms.remirepo.net/enterprise/remi-release-${OS_VER}.rpm"
        if ! rpm -q remi-release >/dev/null 2>&1; then
            dnf install -y "$REMI_RPM" || fail "Remi repo kurulamadı: $REMI_RPM"
        fi

        # Adım 3: PHP 8.2 modülünü etkinleştir + cache yenile
        dnf module reset php -y
        dnf module enable php:remi-8.2 -y
        dnf makecache

        # Adım 4: kalan paketler
        dnf install -y \
            nginx \
            php-cli php-fpm php-mysqlnd php-mbstring \
            php-xml php-curl php-zip php-gd php-bcmath php-intl \
            php-process \
            mariadb-server mariadb \
            postfix dovecot \
            bind bind-utils \
            pure-ftpd \
            redis \
            certbot \
            firewalld fail2ban \
            policycoreutils-python-utils

        # headers-more modülü — isteğe bağlı, EPEL'de farklı isimlerle olabilir
        # Bulunamazsa install_nginx_panel() direktifi conf'tan siler
        dnf install -y nginx-mod-headers-more 2>/dev/null \
            || dnf install -y nginx-mod-http-headers-more 2>/dev/null \
            || warn "nginx headers-more modülü bulunamadı — Server header gizlenemeyecek (kurulum devam ediyor)"
    fi

    ok "Paketler kuruldu."
}

###############################################################################
# 3. iPanel kaynak kodunu çek
###############################################################################
fetch_sources() {
    if [[ -d "$IPANEL_ROOT/.git" ]]; then
        log "Mevcut iPanel kurulumu güncelleniyor..."
        git -C "$IPANEL_ROOT" fetch --quiet origin
        git -C "$IPANEL_ROOT" reset --hard "origin/$IPANEL_BRANCH" --quiet
    else
        log "iPanel kaynak kodu indiriliyor..."
        rm -rf "$IPANEL_ROOT"
        git clone --depth 1 --branch "$IPANEL_BRANCH" "$IPANEL_REPO" "$IPANEL_ROOT"
    fi
    # Framework önbelleğini temizle (git reset sonrası stale cache önlemek için)
    rm -rf "$IPANEL_ROOT/web/iApp/Storage/cache/"* 2>/dev/null || true
    ok "Kaynak kod: $IPANEL_ROOT"
}

###############################################################################
# 4. Sistem kullanıcıları / grupları
###############################################################################

# Web sunucusu kullanıcısını belirle (OS'a göre)
# Ubuntu/Debian → www-data | AlmaLinux/RHEL → nginx
detect_web_user() {
    if [[ "$OS_ID" == "ubuntu" || "$OS_ID" == "debian" ]]; then
        WEB_USER="www-data"
    else
        WEB_USER="nginx"
    fi
    export WEB_USER
}

create_users() {
    detect_web_user

    if ! getent group ipanel >/dev/null; then
        groupadd ipanel
        ok "Grup oluşturuldu: ipanel"
    fi

    usermod -aG ipanel "$WEB_USER" 2>/dev/null || true
    ok "$WEB_USER → ipanel grubuna eklendi"
}

###############################################################################
# 5. Yapılandırma + HMAC secret
###############################################################################
write_config() {
    mkdir -p /etc/ipanel /var/log/ipanel /var/backups/ipanel

    # Socket çalışma dizini (tmpfiles.d ile kalıcı hale getir)
    mkdir -p /run/ipanel
    chown root:ipanel /run/ipanel
    chmod 750 /run/ipanel

    cat > /etc/tmpfiles.d/ipanel.conf <<'EOF'
d /run/ipanel 0750 root ipanel -
EOF

    if [[ ! -f /etc/ipanel/agent.conf.php ]]; then
        SECRET=$(head -c 48 /dev/urandom | base64 | tr -d '\n/+=' | head -c 64)
        cat > /etc/ipanel/agent.conf.php <<PHP
<?php
return [
    'secret_key'  => '$SECRET',
    'rate_limit'  => 30,
    'rate_window' => 10,
];
PHP
        chmod 640 /etc/ipanel/agent.conf.php
        chown root:ipanel /etc/ipanel/agent.conf.php
        ok "Agent secret oluşturuldu: /etc/ipanel/agent.conf.php"
    else
        ok "Agent config zaten mevcut, atlanıyor."
    fi
}

###############################################################################
# 6. Veritabanı
###############################################################################
setup_database() {
    log "MariaDB yapılandırılıyor..."
    systemctl enable --now mariadb >/dev/null

    # MariaDB'nin hazır olmasını bekle
    local retries=10
    while ! mysql -e 'SELECT 1' >/dev/null 2>&1; do
        ((retries--))
        [[ $retries -le 0 ]] && fail "MariaDB başlatılamadı"
        sleep 1
    done

    # Eski veritabanı ve kullanıcıyı temizle (sıfırdan kurulum için)
    log "Eski veritabanı temizleniyor..."
    mysql -e "DROP DATABASE IF EXISTS ipanel;" 2>/dev/null || true
    mysql -e "DROP USER IF EXISTS 'ipanel'@'localhost';" 2>/dev/null || true
    mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || true

    DBPASS=$(head -c 24 /dev/urandom | base64 | tr -d '/+=' | head -c 24)

    mysql <<SQL
CREATE DATABASE ipanel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ipanel'@'localhost' IDENTIFIED BY '${DBPASS}';
GRANT ALL PRIVILEGES ON ipanel.* TO 'ipanel'@'localhost';
FLUSH PRIVILEGES;
SQL

    mysql -u ipanel -p"${DBPASS}" ipanel < "$IPANEL_ROOT/install/schema.sql"
    ok "Veritabanı oluşturuldu ve şema yüklendi."

    # Admin şifresini oluştur ve doğrudan PHP ile hash'le
    ADMIN_PASS=$(head -c 16 /dev/urandom | base64 | tr -d '/+=' | head -c 16)
    ADMIN_HASH=$(php -r "echo password_hash('${ADMIN_PASS}', PASSWORD_BCRYPT);")

    mysql -u ipanel -p"${DBPASS}" ipanel <<SQL
UPDATE users SET password='${ADMIN_HASH}', status=1 WHERE username='admin';
SQL

    # Kimlik bilgilerini kaydet
    {
        echo "DB_PASS=${DBPASS}"
        echo "ADMIN_PASS=${ADMIN_PASS}"
    } > /etc/ipanel/db.env
    chmod 600 /etc/ipanel/db.env
    ok "Admin şifresi oluşturuldu → /etc/ipanel/db.env"

    # Database.php bağlantı bilgilerini güncelle
    # PHP dosyasını doğrudan yaz — sed yerine, özel karakter sorunlarından kaçınmak için
    php -r "
\$file = '${IPANEL_ROOT}/web/iApp/Config/Database.php';
\$content = file_get_contents(\$file);
\$content = preg_replace(\"/('user'\\s*=>\\s*)'[^']*'/\", \"\\\\1'ipanel'\", \$content);
\$content = preg_replace(\"/('password'\\s*=>\\s*)'[^']*'/\", \"\\\\1'${DBPASS}'\", \$content);
file_put_contents(\$file, \$content);
echo 'Database.php guncellendi.' . PHP_EOL;
"
    ok "Database.php: bağlantı bilgileri güncellendi."

    # ZN Framework önbelleğini temizle (stale config önlemek için)
    rm -rf "${IPANEL_ROOT}/web/iApp/Storage/cache/"*  2>/dev/null || true
    ok "Framework cache temizlendi."
}

###############################################################################
# 7. PHP-FPM pool — panel için
###############################################################################
create_php_fpm_pool() {
    log "PHP-FPM pool oluşturuluyor (panel için)..."

    local POOL_FILE="" FPM_SVC=""

    if [[ "$OS_ID" == "ubuntu" || "$OS_ID" == "debian" ]]; then
        # Ubuntu/Debian: /etc/php/X.Y/fpm/pool.d/  +  php X.Y-fpm servis adı
        # Socket dizini: /run/php/ (Debian paketleri bu dizini oluşturur)
        local PHP_VER=""
        for v in 8.2 8.3 8.1 8.4 8.0; do
            if [[ -d "/etc/php/$v/fpm/pool.d" ]]; then
                PHP_VER="$v"; break
            fi
        done
        [[ -z "$PHP_VER" ]] && fail "PHP-FPM kurulumu bulunamadı (/etc/php/*/fpm/pool.d)"
        POOL_FILE="/etc/php/$PHP_VER/fpm/pool.d/ipanel.conf"
        FPM_SVC="php${PHP_VER}-fpm"
        PHP_SOCK="/run/php/ipanel.sock"
    else
        # RHEL/AlmaLinux/Rocky: /etc/php-fpm.d/  +  php-fpm servis adı
        # Socket dizini: /run/php-fpm/ (php-fpm paketi bu dizini oluşturur)
        [[ -d /etc/php-fpm.d ]] || fail "PHP-FPM kurulumu bulunamadı (/etc/php-fpm.d)"
        POOL_FILE="/etc/php-fpm.d/ipanel.conf"
        FPM_SVC="php-fpm"
        PHP_SOCK="/run/php-fpm/ipanel.sock"
    fi

    # PHP_SOCK'u global olarak export et (nginx + health_check'te kullanılır)
    export PHP_SOCK

    cat > "$POOL_FILE" <<EOF
; iPanel Web UI — otomatik oluşturuldu
[ipanel]
user = $WEB_USER
group = $WEB_USER
listen = $PHP_SOCK
listen.owner = $WEB_USER
listen.group = $WEB_USER
listen.mode = 0660
pm = ondemand
pm.max_children = 5
pm.process_idle_timeout = 10s
pm.max_requests = 500
chdir = $IPANEL_ROOT/web
php_admin_value[error_log] = /var/log/ipanel/php-fpm.log
php_admin_flag[log_errors] = on
php_admin_value[open_basedir] = $IPANEL_ROOT/web:/tmp:/var/log/ipanel
EOF

    systemctl enable --now "$FPM_SVC"
    systemctl restart "$FPM_SVC" || {
        warn "PHP-FPM başlatılamadı. Detay: journalctl -xeu $FPM_SVC"
        journalctl -u "$FPM_SVC" -n 20 --no-pager || true
        fail "PHP-FPM pool başlatılamadı"
    }
    ok "PHP-FPM pool: $FPM_SVC → $PHP_SOCK"
}

###############################################################################
# 8. Systemd servisleri
###############################################################################
install_systemd() {
    log "Systemd servisleri kuruluyor..."
    cp "$IPANEL_ROOT/install/systemd/ipanel-agent.service" /etc/systemd/system/
    cp "$IPANEL_ROOT/install/systemd/ipanel-cron.service"  /etc/systemd/system/
    cp "$IPANEL_ROOT/install/systemd/ipanel-cron.timer"    /etc/systemd/system/
    cp "$IPANEL_ROOT/install/systemd/ipanel-jobs.service"  /etc/systemd/system/

    # Servis dosyasındaki yolları güncelle
    sed -i "s|/usr/local/ipanel|$IPANEL_ROOT|g" \
        /etc/systemd/system/ipanel-agent.service \
        /etc/systemd/system/ipanel-jobs.service  \
        2>/dev/null || true

    systemctl daemon-reload
    systemctl enable --now ipanel-agent.service
    systemctl enable --now ipanel-cron.timer
    systemctl enable --now ipanel-jobs.service
    ok "Servisler başlatıldı: ipanel-agent, ipanel-cron, ipanel-jobs."
}

###############################################################################
# 9. Nginx — panel vhost
###############################################################################
install_nginx_panel() {
    log "Nginx vhost yapılandırılıyor..."
    install -d /etc/ipanel/ssl

    # Self-signed sertifika (sonradan certbot ile değiştirilebilir)
    if [[ ! -f /etc/ipanel/ssl/panel.crt ]]; then
        openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
            -subj "/CN=ipanel/O=iPanel/C=TR" \
            -keyout /etc/ipanel/ssl/panel.key \
            -out    /etc/ipanel/ssl/panel.crt \
            2>/dev/null
        chmod 600 /etc/ipanel/ssl/panel.key
        ok "Self-signed SSL sertifikası oluşturuldu."
    fi

    # sites-available dizini var mı?
    if [[ -d /etc/nginx/sites-available ]]; then
        cp "$IPANEL_ROOT/install/nginx/panel.conf" /etc/nginx/sites-available/ipanel.conf
        ln -sf /etc/nginx/sites-available/ipanel.conf /etc/nginx/sites-enabled/ipanel.conf
        # default site'i devre dışı bırak (varsa)
        rm -f /etc/nginx/sites-enabled/default
    else
        cp "$IPANEL_ROOT/install/nginx/panel.conf" /etc/nginx/conf.d/ipanel.conf
    fi

    # Rate limit direktiflerini http context'e kopyala (her iki OS'ta conf.d http context'te yüklenir)
    cp "$IPANEL_ROOT/install/nginx/panel-ratelimit.conf" /etc/nginx/conf.d/ipanel-ratelimit.conf

    # Nginx config içindeki IPANEL_ROOT yolunu ve PHP_SOCK placeholder'ını güncelle
    for NGINX_CONF in /etc/nginx/sites-available/ipanel.conf /etc/nginx/conf.d/ipanel.conf; do
        [[ -f "$NGINX_CONF" ]] || continue
        sed -i \
            -e "s|/usr/local/ipanel|$IPANEL_ROOT|g" \
            -e "s|IPANEL_PHP_SOCK|${PHP_SOCK}|g" \
            "$NGINX_CONF"
    done

    # PHP-FPM socket yolunu OS'a göre güncelle
    # (Debian → /run/php/ipanel.sock, RHEL → /run/php-fpm/ipanel.sock)
    local NGINX_CONF="/etc/nginx/conf.d/ipanel.conf"
    [[ -f /etc/nginx/sites-available/ipanel.conf ]] && NGINX_CONF="/etc/nginx/sites-available/ipanel.conf"
    sed -i "s|unix:/run/php/ipanel\.sock|unix:${PHP_SOCK}|g" "$NGINX_CONF" 2>/dev/null || true

    # headers-more modülü yüklü mü? — .so varlığı yeterli değil, load_module gerekli.
    # nginx -V statik derleme, grep ise load_module satırını kontrol eder.
    local HEADERS_MORE_LOADED=false
    if nginx -V 2>&1 | grep -qi 'headers.more\|headers_more'; then
        HEADERS_MORE_LOADED=true
    elif grep -r 'ngx_http_headers_more\|mod-http-headers-more' \
            /etc/nginx/modules/ /etc/nginx/conf.d/ /etc/nginx/nginx.conf \
            2>/dev/null | grep -q 'load_module'; then
        HEADERS_MORE_LOADED=true
    fi
    if ! $HEADERS_MORE_LOADED; then
        sed -i '/more_clear_headers/d' "$NGINX_CONF"
        warn "nginx headers-more modülü yüklü değil; more_clear_headers devre dışı (server_tokens off hâlâ aktif)"
    fi

    # SELinux — port 3333'ü nginx (http_port_t) için aç
    # AlmaLinux/RHEL'de SELinux varsayılan olarak aktiftir ve nginx'in
    # yalnızca bilinen portlara (80, 443, 8080…) bind olmasına izin verir.
    if command -v semanage >/dev/null 2>&1 && command -v getenforce >/dev/null 2>&1 \
            && [[ "$(getenforce 2>/dev/null)" != "Disabled" ]]; then
        semanage port -a -t http_port_t -p tcp 3333 2>/dev/null \
            || semanage port -m -t http_port_t -p tcp 3333 2>/dev/null \
            || warn "SELinux: port 3333 http_port_t eklenemedi (zaten mevcut olabilir)"
        ok "SELinux: port 3333 nginx'e açıldı (http_port_t)."
    fi

    # reload yerine restart — ilk kurulumda reload sessizce eski config'de kalabilir
    systemctl enable nginx
    nginx -t || fail "Nginx config hatası var (nginx -t)"
    systemctl restart nginx
    sleep 1

    # Port 3333'ün gerçekten açıldığını doğrula
    if ! ss -tlnp 2>/dev/null | grep -q ':3333 '; then
        warn "Nginx 3333'te dinlemiyor. Nginx error log:"
        tail -20 /var/log/nginx/error.log 2>/dev/null || true
        fail "Nginx 3333 portunda başlamadı. Kontrol: nginx -t && journalctl -u nginx -n 30"
    fi

    ok "Panel vhost etkinleştirildi (https://IP:3333)"
}

###############################################################################
# 10. Son ayarlar + izinler
###############################################################################
finalize() {
    log "Dosya izinleri ve son ayarlar yapılıyor..."

    # iApp/Storage — ZN Framework cache/session/DB + sistem dosyaları
    mkdir -p "$IPANEL_ROOT/web/iApp/Storage"/{cache,logs,session,database,Files}
    chown -R ${WEB_USER}:${WEB_USER} "$IPANEL_ROOT/web/iApp/Storage"
    chmod -R 775 "$IPANEL_ROOT/web/iApp/Storage"
    ok "Storage dizinleri hazırlandı."

    # Çoklu dil desteği dizinleri
    mkdir -p "$IPANEL_ROOT/web/iApp/Languages"/{tr,en}
    chown -R ${WEB_USER}:${WEB_USER} "$IPANEL_ROOT/web/iApp/Languages"
    chmod -R 755 "$IPANEL_ROOT/web/iApp/Languages"
    ok "Languages dizinleri hazırlandı (tr, en)."

    # Template dizini
    mkdir -p "$IPANEL_ROOT/web/iApp/Templates"
    chown ${WEB_USER}:${WEB_USER} "$IPANEL_ROOT/web/iApp/Templates"
    chmod 755 "$IPANEL_ROOT/web/iApp/Templates"
    ok "Templates dizini hazırlandı."

    # Plugin dizini
    mkdir -p "$IPANEL_ROOT/web/iApp/Plugins"
    chown ${WEB_USER}:${WEB_USER} "$IPANEL_ROOT/web/iApp/Plugins"
    chmod 755 "$IPANEL_ROOT/web/iApp/Plugins"
    ok "Plugins dizini hazırlandı."

    # Themes dizini
    mkdir -p "$IPANEL_ROOT/web/iApp/Themes"
    chown ${WEB_USER}:${WEB_USER} "$IPANEL_ROOT/web/iApp/Themes"
    chmod 755 "$IPANEL_ROOT/web/iApp/Themes"
    ok "Themes dizini hazırlandı."

    # uploads/ — web erişimli, kullanıcı dosyaları için
    mkdir -p "$IPANEL_ROOT/web/uploads"
    chown ${WEB_USER}:${WEB_USER} "$IPANEL_ROOT/web/uploads"
    chmod 775 "$IPANEL_ROOT/web/uploads"
    ok "uploads/ dizini hazırlandı (web-accessible)."

    # Web dizini sahipliği
    chown -R ${WEB_USER}:${WEB_USER} "$IPANEL_ROOT/web"
    chmod -R o-rwx "$IPANEL_ROOT/web/iApp/Config"

    # CLI aracı symlink
    ln -sf "$IPANEL_ROOT/bin/ipanel" /usr/local/bin/ipanel
    chmod +x "$IPANEL_ROOT/bin/ipanel" "$IPANEL_ROOT/agent/agent.php"
    ok "CLI: 'ipanel' komutu kullanılabilir."

    # Temel güvenlik duvarı kuralları
    if command -v ufw >/dev/null 2>&1; then
        ufw allow 22/tcp   >/dev/null
        ufw allow 80/tcp   >/dev/null
        ufw allow 443/tcp  >/dev/null
        ufw allow 3333/tcp >/dev/null
        echo "y" | ufw enable >/dev/null 2>&1 || true
        ok "UFW etkinleştirildi (22/80/443/3333)."
    elif command -v firewall-cmd >/dev/null 2>&1; then
        systemctl enable --now firewalld >/dev/null 2>&1 || true
        firewall-cmd --quiet --permanent --add-service=ssh
        firewall-cmd --quiet --permanent --add-service=http
        firewall-cmd --quiet --permanent --add-service=https
        firewall-cmd --quiet --permanent --add-port=3333/tcp
        firewall-cmd --quiet --reload
        ok "firewalld: 22/80/443/3333 açıldı."
    fi
}

###############################################################################
# 11. Sağlık kontrolü
###############################################################################
health_check() {
    log "Sağlık kontrolleri yapılıyor..."
    local ALL_OK=true

    check_service() {
        local svc="$1" label="$2"
        systemctl is-active --quiet "$svc" \
            && ok "$label çalışıyor" \
            || { warn "$label çalışmıyor! Kontrol: systemctl status $svc"; ALL_OK=false; }
    }

    check_service nginx        "Nginx"
    check_service ipanel-agent "iPanel Agent"

    # PHP-FPM — Ubuntu'da versiyonlu isim, RHEL'de php-fpm
    local FPM_OK=false
    for svc in php-fpm php8.2-fpm php8.3-fpm php8.1-fpm; do
        if systemctl is-active --quiet "$svc" 2>/dev/null; then
            ok "PHP-FPM çalışıyor ($svc)"
            FPM_OK=true; break
        fi
    done
    $FPM_OK || { warn "PHP-FPM servisi aktif değil"; ALL_OK=false; }

    [[ -S /run/ipanel/agent.sock ]] \
        && ok "Agent socket mevcut" \
        || { warn "Agent socket eksik: /run/ipanel/agent.sock"; ALL_OK=false; }

    local SOCK="${PHP_SOCK:-/run/php/ipanel.sock}"
    [[ -S "$SOCK" ]] \
        && ok "PHP-FPM socket mevcut ($SOCK)" \
        || { warn "PHP-FPM socket eksik: $SOCK"; ALL_OK=false; }

    if [[ -f /etc/ipanel/db.env ]]; then
        local DBPASS
        DBPASS=$(grep DB_PASS /etc/ipanel/db.env | cut -d= -f2)
        mysql -u ipanel -p"$DBPASS" ipanel -e "SELECT COUNT(*) FROM users;" >/dev/null 2>&1 \
            && ok "Veritabanı bağlantısı başarılı" \
            || { warn "Veritabanı bağlantısı başarısız"; ALL_OK=false; }
    fi

    if $ALL_OK; then
        ok "Tüm kontroller başarılı!"
    else
        warn "Bazı kontroller başarısız oldu. Detay için: journalctl -xe"
    fi
}

###############################################################################
# Ana akış
###############################################################################
echo ""
echo -e "${C_BLU}╔══════════════════════════════════════════╗"
echo -e "║   iPanel ${IPANEL_VERSION} — Kurulum Başlıyor       ║"
echo -e "╚══════════════════════════════════════════╝${C_RST}"
echo ""

detect_os
install_deps
fetch_sources
create_users
write_config
setup_database
create_php_fpm_pool
install_systemd
install_nginx_panel
finalize
health_check

SERVER_IP=$(hostname -I | awk '{print $1}')

cat <<EOF

${C_GRN}╔══════════════════════════════════════════════════════════╗
║          iPanel kurulumu tamamlandı!                     ║
╚══════════════════════════════════════════════════════════╝${C_RST}

  Panel URL   :  https://${SERVER_IP}:3333
  Kullanıcı   :  admin
  Şifre       :  $(grep ADMIN_PASS /etc/ipanel/db.env 2>/dev/null | cut -d= -f2 || echo 'bak: /etc/ipanel/db.env')

  Şifre değiştir:
    ipanel passwd admin

  Servis durumu:
    ipanel status

  Loglar:
    ipanel logs agent
    ipanel logs panel
    ipanel logs nginx

  Kaynak dizin:  $IPANEL_ROOT
  Yapılandırma:  /etc/ipanel/
  Loglar:        /var/log/ipanel/

EOF
