#!/usr/bin/env bash
#
# iPanel installer
# Usage: curl -sL https://get.ipanel.io | bash
#        OR sudo bash install.sh
#
set -euo pipefail

IPANEL_VERSION="0.1.0"
IPANEL_ROOT="/usr/local/ipanel"
IPANEL_REPO="${IPANEL_REPO:-https://github.com/rmzonl/ipanel.git}"
IPANEL_BRANCH="${IPANEL_BRANCH:-develop}"

C_RED='\033[0;31m'; C_GRN='\033[0;32m'; C_YEL='\033[1;33m'; C_BLU='\033[0;34m'; C_RST='\033[0m'
log()   { echo -e "${C_BLU}==>${C_RST} $*"; }
ok()    { echo -e "${C_GRN}OK${C_RST}  $*"; }
warn()  { echo -e "${C_YEL}!! ${C_RST} $*"; }
fail()  { echo -e "${C_RED}!!!${C_RST} $*"; exit 1; }

[[ $EUID -eq 0 ]] || fail "iPanel installer must run as root."

###############################################################################
# 1. OS detection
###############################################################################
detect_os() {
    if [[ -f /etc/os-release ]]; then
        . /etc/os-release
        OS_ID="$ID"
        OS_VER="${VERSION_ID%%.*}"
    else
        fail "Cannot detect OS"
    fi
    case "$OS_ID" in
        ubuntu|debian) PKG="apt-get"; PKG_INSTALL="apt-get install -y"; PKG_UPDATE="apt-get update -qq" ;;
        almalinux|rocky|centos|rhel) PKG="dnf"; PKG_INSTALL="dnf install -y"; PKG_UPDATE="dnf -q makecache" ;;
        *) fail "Unsupported OS: $OS_ID" ;;
    esac
    log "OS: $OS_ID $OS_VER"
}

###############################################################################
# 2. Dependencies
###############################################################################
install_deps() {
    log "Updating package index..."
    $PKG_UPDATE >/dev/null

    log "Installing core packages..."
    if [[ "$PKG" == "apt-get" ]]; then
        DEBIAN_FRONTEND=noninteractive $PKG_INSTALL \
            curl wget git ca-certificates lsb-release gnupg \
            nginx \
            php-cli php-fpm php-mysql php-mbstring php-xml php-curl php-zip php-gd php-bcmath \
            mariadb-server mariadb-client \
            postfix dovecot-imapd dovecot-pop3d dovecot-lmtpd \
            bind9 bind9utils \
            pure-ftpd \
            redis-server \
            certbot \
            ufw fail2ban \
            tar gzip rsync >/dev/null
    else
        $PKG_INSTALL epel-release >/dev/null || true
        $PKG_INSTALL \
            curl wget git ca-certificates \
            nginx \
            php-cli php-fpm php-mysqlnd php-mbstring php-xml php-gd \
            mariadb-server mariadb \
            postfix dovecot \
            bind bind-utils \
            pure-ftpd \
            redis \
            certbot \
            firewalld fail2ban \
            tar gzip rsync >/dev/null
    fi
    ok "Core packages installed."
}

###############################################################################
# 3. Fetch iPanel sources
###############################################################################
fetch_sources() {
    if [[ -d "$IPANEL_ROOT/.git" ]]; then
        log "Updating existing iPanel install..."
        git -C "$IPANEL_ROOT" fetch --quiet
        git -C "$IPANEL_ROOT" reset --hard "origin/$IPANEL_BRANCH"
    else
        log "Cloning iPanel..."
        git clone --depth 1 --branch "$IPANEL_BRANCH" "$IPANEL_REPO" "$IPANEL_ROOT"
    fi
    ok "Sources at $IPANEL_ROOT"
}

###############################################################################
# 4. System users / groups
###############################################################################
create_users() {
    if ! getent group ipanel >/dev/null; then
        groupadd ipanel
        ok "Created group: ipanel"
    fi
    if id www-data >/dev/null 2>&1; then
        usermod -aG ipanel www-data
        ok "www-data added to ipanel group"
    fi
}

###############################################################################
# 5. Configuration / secret
###############################################################################
write_config() {
    mkdir -p /etc/ipanel /run/ipanel /var/log/ipanel /var/backups/ipanel
    chown root:ipanel /run/ipanel
    chmod 750 /run/ipanel

    if [[ ! -f /etc/ipanel/agent.conf.php ]]; then
        SECRET=$(head -c 48 /dev/urandom | base64 | tr -d '\n=' | head -c 64)
        cat > /etc/ipanel/agent.conf.php <<PHP
<?php
return [
    'secret_key' => '$SECRET',
    'rate_limit' => 30,
    'rate_window' => 10,
];
PHP
        chmod 640 /etc/ipanel/agent.conf.php
        chown root:ipanel /etc/ipanel/agent.conf.php
        ok "Generated /etc/ipanel/agent.conf.php"
    fi
}

###############################################################################
# 6. Database setup
###############################################################################
setup_database() {
    log "Configuring MariaDB..."
    systemctl enable --now mariadb >/dev/null

    if ! mysql -e 'SHOW DATABASES' | grep -q '^ipanel$'; then
        DBPASS=$(head -c 24 /dev/urandom | base64 | tr -d '/+=' | head -c 24)
        mysql <<SQL
CREATE DATABASE ipanel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ipanel'@'localhost' IDENTIFIED BY '$DBPASS';
GRANT ALL PRIVILEGES ON ipanel.* TO 'ipanel'@'localhost';
FLUSH PRIVILEGES;
SQL
        echo "DB_PASS=$DBPASS" >> /etc/ipanel/db.env
        chmod 600 /etc/ipanel/db.env
        mysql ipanel < "$IPANEL_ROOT/install/schema.sql"
        ok "Database 'ipanel' created and seeded"

        # Patch web/iApp/Config/Database.php with credentials
        sed -i \
            -e "s/'database'\s*=>\s*'.*'/'database' => 'ipanel'/" \
            -e "s/'user'\s*=>\s*'.*'/'user' => 'ipanel'/" \
            -e "s/'password'\s*=>\s*'.*'/'password' => '$DBPASS'/" \
            "$IPANEL_ROOT/web/iApp/Config/Database.php" || true
    else
        warn "Database 'ipanel' already exists, skipping schema import."
    fi
}

###############################################################################
# 7. Systemd integration
###############################################################################
install_systemd() {
    cp "$IPANEL_ROOT/install/systemd/ipanel-agent.service" /etc/systemd/system/
    cp "$IPANEL_ROOT/install/systemd/ipanel-cron.service"  /etc/systemd/system/
    cp "$IPANEL_ROOT/install/systemd/ipanel-cron.timer"    /etc/systemd/system/
    systemctl daemon-reload
    systemctl enable --now ipanel-agent.service
    systemctl enable --now ipanel-cron.timer
    ok "ipanel-agent.service running"
}

###############################################################################
# 8. Nginx vhost for the panel itself
###############################################################################
install_nginx_panel() {
    install -d /etc/ipanel/ssl

    if [[ ! -f /etc/ipanel/ssl/panel.crt ]]; then
        log "Generating self-signed cert (replace with Let's Encrypt later)..."
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -subj "/CN=ipanel" \
            -keyout /etc/ipanel/ssl/panel.key \
            -out /etc/ipanel/ssl/panel.crt 2>/dev/null
        chmod 600 /etc/ipanel/ssl/panel.key
    fi

    cp "$IPANEL_ROOT/install/nginx/panel.conf" /etc/nginx/sites-available/ipanel.conf 2>/dev/null \
        || cp "$IPANEL_ROOT/install/nginx/panel.conf" /etc/nginx/conf.d/ipanel.conf

    if [[ -d /etc/nginx/sites-enabled ]] && [[ ! -L /etc/nginx/sites-enabled/ipanel.conf ]]; then
        ln -s /etc/nginx/sites-available/ipanel.conf /etc/nginx/sites-enabled/ipanel.conf
    fi

    nginx -t && systemctl reload nginx
    ok "Panel vhost installed (https://YOUR_IP:8443)"
}

###############################################################################
# 9. Symlinks / final wiring
###############################################################################
finalize() {
    ln -sf "$IPANEL_ROOT/bin/ipanel" /usr/local/bin/ipanel
    chmod +x "$IPANEL_ROOT/bin/ipanel" "$IPANEL_ROOT/agent/agent.php"

    chown -R www-data:www-data "$IPANEL_ROOT/web" 2>/dev/null || true
    chmod -R 750 "$IPANEL_ROOT/web/iApp/Storage" 2>/dev/null || true

    if command -v ufw >/dev/null; then
        ufw allow 22/tcp  >/dev/null
        ufw allow 80/tcp  >/dev/null
        ufw allow 443/tcp >/dev/null
        ufw allow 8443/tcp >/dev/null
        echo "y" | ufw enable >/dev/null || true
    fi
}

###############################################################################
# Run!
###############################################################################
detect_os
install_deps
fetch_sources
create_users
write_config
setup_database
install_systemd
install_nginx_panel
finalize

cat <<EOF

${C_GRN}╔══════════════════════════════════════════════════════════╗
║              iPanel installation complete!               ║
╚══════════════════════════════════════════════════════════╝${C_RST}

  Panel URL:      https://$(hostname -I | awk '{print $1}'):8443
  Default admin:  admin
  Default pass:   password   (change immediately with: ipanel passwd admin)

  Useful commands:
    ipanel status        — show service status
    ipanel logs agent    — tail agent log
    ipanel update        — pull updates and restart agent

  Files:
    Sources:   $IPANEL_ROOT
    Config:    /etc/ipanel/
    Logs:      /var/log/ipanel/
    Socket:    /run/ipanel/agent.sock

EOF
