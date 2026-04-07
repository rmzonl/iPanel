#!/usr/bin/env bash
#
# iPanel — tek komut kurulum betiği
# Kullanım:
#   curl -sSL https://raw.githubusercontent.com/rmzonl/iPanel/develop/install/install.sh | sudo bash
#   veya: sudo bash install/install.sh
#
set -euo pipefail

IPANEL_VERSION="0.1.0"
IPANEL_ROOT="/usr/local/ipanel"
IPANEL_REPO="${IPANEL_REPO:-https://github.com/rmzonl/iPanel.git}"
IPANEL_BRANCH="${IPANEL_BRANCH:-develop}"

C_RED='\033[0;31m'; C_GRN='\033[0;32m'; C_YEL='\033[1;33m'; C_BLU='\033[0;34m'; C_RST='\033[0m'
log()  { echo -e "${C_BLU}==>${C_RST} $*"; }
ok()   { echo -e "  ${C_GRN}✓${C_RST}  $*"; }
warn() { echo -e "  ${C_YEL}!${C_RST}  $*"; }
fail() { echo -e "${C_RED}HATA:${C_RST} $*"; exit 1; }

[[ $EUID -eq 0 ]] || fail "iPanel kurulumu root olarak çalışmalıdır."

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
            php8.2-redis php8.2-intl \
            mariadb-server mariadb-client \
            postfix dovecot-core dovecot-imapd dovecot-pop3d dovecot-lmtpd \
            bind9 bind9utils dnsutils \
            pure-ftpd \
            redis-server \
            certbot python3-certbot-nginx \
            ufw fail2ban \
            tar gzip rsync openssl
    else
        $PKG_INSTALL epel-release
        $PKG_INSTALL \
            curl wget git ca-certificates gnupg \
            nginx \
            php-cli php-fpm php-mysqlnd php-mbstring \
            php-xml php-curl php-zip php-gd php-bcmath php-intl \
            mariadb-server mariadb \
            postfix dovecot \
            bind bind-utils \
            pure-ftpd \
            redis \
            certbot \
            firewalld fail2ban \
            tar gzip rsync openssl
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
        git clone --depth 1 --branch "$IPANEL_BRANCH" "$IPANEL_REPO" "$IPANEL_ROOT"
    fi
    ok "Kaynak kod: $IPANEL_ROOT"
}

###############################################################################
# 4. Sistem kullanıcıları / grupları
###############################################################################
create_users() {
    if ! getent group ipanel >/dev/null; then
        groupadd ipanel
        ok "Grup oluşturuldu: ipanel"
    fi

    # www-data (Ubuntu/Debian) veya nginx (RHEL)
    for WEB_USER in www-data nginx; do
        if id "$WEB_USER" >/dev/null 2>&1; then
            usermod -aG ipanel "$WEB_USER"
            ok "$WEB_USER → ipanel grubuna eklendi"
            break
        fi
    done
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

    if ! mysql -e 'SHOW DATABASES' | grep -q '^ipanel$'; then
        DBPASS=$(head -c 24 /dev/urandom | base64 | tr -d '/+=' | head -c 24)

        mysql <<SQL
CREATE DATABASE ipanel CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER 'ipanel'@'localhost' IDENTIFIED BY '$DBPASS';
GRANT ALL PRIVILEGES ON ipanel.* TO 'ipanel'@'localhost';
FLUSH PRIVILEGES;
SQL
        mysql ipanel < "$IPANEL_ROOT/install/schema.sql"
        echo "DB_PASS=$DBPASS" > /etc/ipanel/db.env
        chmod 600 /etc/ipanel/db.env
        ok "Veritabanı oluşturuldu ve şema yüklendi."

        # Database.php içindeki bağlantı bilgilerini güncelle
        sed -i \
            -e "s/'user'\s*=>\s*'[^']*'/'user' => 'ipanel'/" \
            -e "s/'password'\s*=>\s*'[^']*'/'password' => '$DBPASS'/" \
            "$IPANEL_ROOT/web/iApp/Config/Database.php" || warn "Database.php sed başarısız"
    else
        warn "Veritabanı 'ipanel' zaten mevcut, şema yüklemesi atlandı."
        DBPASS=$(grep DB_PASS /etc/ipanel/db.env 2>/dev/null | cut -d= -f2 || echo "")
    fi
}

###############################################################################
# 7. PHP-FPM pool — panel için
###############################################################################
create_php_fpm_pool() {
    log "PHP-FPM pool oluşturuluyor (panel için)..."

    local PHP_VER=""
    for v in 8.2 8.3 8.1 8.4 8.0; do
        if [[ -d "/etc/php/$v/fpm/pool.d" ]]; then
            PHP_VER="$v"
            break
        fi
    done
    [[ -z "$PHP_VER" ]] && fail "PHP-FPM kurulumu bulunamadı (/etc/php/*/fpm/pool.d)"

    cat > "/etc/php/$PHP_VER/fpm/pool.d/ipanel.conf" <<EOF
; iPanel Web UI — otomatik oluşturuldu
[ipanel]
user = www-data
group = www-data
listen = /run/php/ipanel.sock
listen.owner = www-data
listen.group = www-data
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

    systemctl enable --now "php${PHP_VER}-fpm"
    systemctl restart "php${PHP_VER}-fpm"
    ok "PHP-FPM pool: php${PHP_VER}-fpm → /run/php/ipanel.sock"
}

###############################################################################
# 8. Systemd servisleri
###############################################################################
install_systemd() {
    log "Systemd servisleri kuruluyor..."
    cp "$IPANEL_ROOT/install/systemd/ipanel-agent.service" /etc/systemd/system/
    cp "$IPANEL_ROOT/install/systemd/ipanel-cron.service"  /etc/systemd/system/
    cp "$IPANEL_ROOT/install/systemd/ipanel-cron.timer"    /etc/systemd/system/
    systemctl daemon-reload
    systemctl enable --now ipanel-agent.service
    systemctl enable --now ipanel-cron.timer
    ok "ipanel-agent.service başlatıldı."
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

    # Nginx config içindeki IPANEL_ROOT yolunu güncelle
    sed -i "s|/usr/local/ipanel|$IPANEL_ROOT|g" \
        /etc/nginx/sites-available/ipanel.conf \
        /etc/nginx/conf.d/ipanel.conf \
        2>/dev/null || true

    systemctl enable --now nginx
    nginx -t || fail "Nginx config hatası var"
    systemctl reload nginx
    ok "Panel vhost etkinleştirildi (https://IP:8443)"
}

###############################################################################
# 10. Son ayarlar + izinler
###############################################################################
finalize() {
    log "Dosya izinleri ve son ayarlar yapılıyor..."

    # iApp/Storage — ZN Framework cache/session/DB + sistem dosyaları
    mkdir -p "$IPANEL_ROOT/web/iApp/Storage"/{cache,logs,session,database,Files}
    chown -R www-data:www-data "$IPANEL_ROOT/web/iApp/Storage"
    chmod -R 775 "$IPANEL_ROOT/web/iApp/Storage"
    ok "Storage dizinleri hazırlandı."

    # Çoklu dil desteği dizinleri
    mkdir -p "$IPANEL_ROOT/web/iApp/Languages"/{tr,en}
    chown -R www-data:www-data "$IPANEL_ROOT/web/iApp/Languages"
    chmod -R 755 "$IPANEL_ROOT/web/iApp/Languages"
    ok "Languages dizinleri hazırlandı (tr, en)."

    # Template dizini
    mkdir -p "$IPANEL_ROOT/web/iApp/Templates"
    chown www-data:www-data "$IPANEL_ROOT/web/iApp/Templates"
    chmod 755 "$IPANEL_ROOT/web/iApp/Templates"
    ok "Templates dizini hazırlandı."

    # Plugin dizini
    mkdir -p "$IPANEL_ROOT/web/iApp/Plugins"
    chown www-data:www-data "$IPANEL_ROOT/web/iApp/Plugins"
    chmod 755 "$IPANEL_ROOT/web/iApp/Plugins"
    ok "Plugins dizini hazırlandı."

    # Themes dizini
    mkdir -p "$IPANEL_ROOT/web/iApp/Themes"
    chown www-data:www-data "$IPANEL_ROOT/web/iApp/Themes"
    chmod 755 "$IPANEL_ROOT/web/iApp/Themes"
    ok "Themes dizini hazırlandı."

    # uploads/ — web erişimli, kullanıcı dosyaları için
    mkdir -p "$IPANEL_ROOT/web/uploads"
    chown www-data:www-data "$IPANEL_ROOT/web/uploads"
    chmod 775 "$IPANEL_ROOT/web/uploads"
    ok "uploads/ dizini hazırlandı (web-accessible)."

    # Web dizini sahipliği
    chown -R www-data:www-data "$IPANEL_ROOT/web"
    chmod -R o-rwx "$IPANEL_ROOT/web/iApp/Config"

    # CLI aracı symlink
    ln -sf "$IPANEL_ROOT/bin/ipanel" /usr/local/bin/ipanel
    chmod +x "$IPANEL_ROOT/bin/ipanel" "$IPANEL_ROOT/agent/agent.php"
    ok "CLI: 'ipanel' komutu kullanılabilir."

    # Temel güvenlik duvarı kuralları
    if command -v ufw >/dev/null 2>&1; then
        ufw allow 22/tcp  >/dev/null
        ufw allow 80/tcp  >/dev/null
        ufw allow 443/tcp >/dev/null
        ufw allow 8443/tcp >/dev/null
        echo "y" | ufw enable >/dev/null 2>&1 || true
        ok "UFW etkinleştirildi."
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

    # PHP-FPM — hangi versiyon olduğunu bul
    local PHP_VER=""
    for v in 8.2 8.3 8.1 8.4 8.0; do
        if systemctl is-active --quiet "php${v}-fpm" 2>/dev/null; then
            PHP_VER="$v"; break
        fi
    done
    if [[ -n "$PHP_VER" ]]; then
        ok "PHP ${PHP_VER}-FPM çalışıyor"
    else
        warn "PHP-FPM servisi aktif değil"; ALL_OK=false
    fi

    [[ -S /run/ipanel/agent.sock ]] \
        && ok "Agent socket mevcut" \
        || { warn "Agent socket eksik: /run/ipanel/agent.sock"; ALL_OK=false; }

    [[ -S /run/php/ipanel.sock ]] \
        && ok "PHP-FPM socket mevcut" \
        || { warn "PHP-FPM socket eksik: /run/php/ipanel.sock"; ALL_OK=false; }

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

  Panel URL   :  https://${SERVER_IP}:8443
  Kullanıcı   :  admin
  Şifre       :  password   ← hemen değiştir!

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
