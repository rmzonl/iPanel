#!/usr/bin/env bash
#
# iPanel — Kaldırma Betiği
# Kullanım: sudo bash /usr/local/ipanel/install/scripts/uninstall.sh
#
# Kaldırılanlar:
#   - iPanel servisleri (agent, jobs, cron)
#   - Nginx vhost + rate-limit config
#   - PHP-FPM pool
#   - Veritabanı ve kullanıcı (ipanel)
#   - SELinux policy modülü
#   - /usr/local/ipanel, /etc/ipanel, /var/log/ipanel
#   - logrotate + tmpfiles.d + CLI symlink + ipanel group
#
# Kaldırılmayanlar:
#   - Sistem paketleri (Nginx, PHP, MariaDB, vb.)
#   - /home/* altındaki site dosyaları
#
set -euo pipefail

C_RED='\033[0;31m'; C_GRN='\033[0;32m'; C_YEL='\033[1;33m'; C_BLU='\033[0;34m'; C_RST='\033[0m'
log()  { echo -e "${C_BLU}==>${C_RST} $*"; }
ok()   { echo -e "  ${C_GRN}✓${C_RST}  $*"; }
warn() { echo -e "  ${C_YEL}!${C_RST}  $*"; }

IPANEL_ROOT="${IPANEL_ROOT:-/usr/local/ipanel}"

[[ $EUID -eq 0 ]] || { echo "Hata: root olarak çalıştırın."; exit 1; }

echo -e "${C_RED}"
echo "  ╔══════════════════════════════════════════════════════════╗"
echo "  ║           iPanel — Kaldırma İşlemi                      ║"
echo "  ╠══════════════════════════════════════════════════════════╣"
echo "  ║  Veritabanı ve yapılandırma KALICI OLARAK SİLİNECEK.    ║"
echo "  ║  Site dosyaları (/home/*) dokunulmayacak.               ║"
echo "  ║  SSL sertifikaları /root/ altına yedeklenecek.          ║"
echo "  ║                                                          ║"
echo "  ║  Devam etmek için  EVET  yazın:                         ║"
echo "  ╚══════════════════════════════════════════════════════════╝"
echo -e "${C_RST}"

read -r -p "  Onay: " CONFIRM
if [[ "$CONFIRM" != "EVET" ]]; then
    echo "İptal edildi."
    exit 0
fi

BACKUP_SSL=""

# ─────────────────────────────────────────────
# 1. Servisleri durdur ve kaldır
# ─────────────────────────────────────────────
log "Servisler durduruluyor..."
for svc in ipanel-agent ipanel-jobs ipanel-cron; do
    systemctl stop    "$svc" 2>/dev/null || true
    systemctl disable "$svc" 2>/dev/null || true
done
rm -f /etc/systemd/system/ipanel-agent.service \
      /etc/systemd/system/ipanel-cron.service  \
      /etc/systemd/system/ipanel-cron.timer    \
      /etc/systemd/system/ipanel-jobs.service
systemctl daemon-reload 2>/dev/null || true
ok "Servisler kaldırıldı."

# ─────────────────────────────────────────────
# 2. Nginx vhost kaldır
# ─────────────────────────────────────────────
log "Nginx vhost kaldırılıyor..."
rm -f /etc/nginx/sites-enabled/ipanel.conf \
      /etc/nginx/sites-available/ipanel.conf \
      /etc/nginx/conf.d/ipanel.conf \
      /etc/nginx/conf.d/ipanel-ratelimit.conf
nginx -t >/dev/null 2>&1 && systemctl reload nginx 2>/dev/null || true
ok "Nginx vhost kaldırıldı."

# ─────────────────────────────────────────────
# 3. PHP-FPM pool kaldır
# ─────────────────────────────────────────────
log "PHP-FPM pool kaldırılıyor..."
rm -f /etc/php-fpm.d/ipanel.conf
for ver in 8.1 8.2 8.3 8.4; do
    rm -f "/etc/php/$ver/fpm/pool.d/ipanel.conf" 2>/dev/null || true
done
for svc in php-fpm php8.2-fpm php8.3-fpm php8.1-fpm; do
    if systemctl is-active --quiet "$svc" 2>/dev/null; then
        systemctl restart "$svc" && break
    fi
done
ok "PHP-FPM pool kaldırıldı."

# ─────────────────────────────────────────────
# 4. Veritabanı ve kullanıcı sil
# ─────────────────────────────────────────────
log "Veritabanı siliniyor..."
if command -v mysql >/dev/null 2>&1; then
    mysql -e "DROP DATABASE IF EXISTS ipanel;" 2>/dev/null || warn "DB silinemedi."
    mysql -e "DROP USER IF EXISTS 'ipanel'@'localhost';" 2>/dev/null || true
    mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    ok "Veritabanı ve kullanıcı silindi."
else
    warn "mysql komutu bulunamadı — veritabanını manuel silin."
fi

# ─────────────────────────────────────────────
# 5. SELinux policy modülü kaldır
# ─────────────────────────────────────────────
if command -v semodule >/dev/null 2>&1 \
        && semodule -l 2>/dev/null | grep -q ipanel_agent; then
    log "SELinux policy modülü kaldırılıyor..."
    semodule -r ipanel_agent 2>/dev/null || true
    ok "SELinux ipanel_agent modülü kaldırıldı."
fi
if command -v semanage >/dev/null 2>&1; then
    semanage fcontext -d '/var/run/ipanel(/.*)?' 2>/dev/null || true
fi

# ─────────────────────────────────────────────
# 6. SSL sertifikasını yedekle
# ─────────────────────────────────────────────
if [[ -d /etc/ipanel/ssl ]]; then
    BACKUP_SSL="/root/ipanel-ssl-backup-$(date +%Y%m%d%H%M%S).tar.gz"
    tar -czf "$BACKUP_SSL" /etc/ipanel/ssl/ 2>/dev/null && ok "SSL yedeği: $BACKUP_SSL"
fi

# ─────────────────────────────────────────────
# 7. Dosya ve dizinleri sil
# ─────────────────────────────────────────────
log "Dosyalar siliniyor..."
rm -rf "$IPANEL_ROOT"
rm -rf /etc/ipanel
rm -rf /var/log/ipanel
rm -rf /var/backups/ipanel
rm -f  /etc/tmpfiles.d/ipanel.conf
rm -f  /etc/logrotate.d/ipanel
rm -f  /usr/local/bin/ipanel
rm -rf /run/ipanel
ok "Dosyalar silindi."

# ─────────────────────────────────────────────
# 8. ipanel grubunu kaldır
# ─────────────────────────────────────────────
if getent group ipanel >/dev/null 2>&1; then
    groupdel ipanel 2>/dev/null \
        && ok "ipanel grubu silindi." \
        || warn "ipanel grubu silinemedi."
fi

echo ""
echo -e "${C_GRN}iPanel başarıyla kaldırıldı.${C_RST}"
[[ -n "$BACKUP_SSL" ]] && echo "  SSL yedeği: $BACKUP_SSL"
echo ""
echo "  Yeniden kurmak için:"
echo "    curl -sSL https://raw.githubusercontent.com/rmzonl/iPanel/develop/install/install.sh | sudo bash"
echo ""
