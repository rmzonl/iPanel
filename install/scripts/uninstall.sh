#!/usr/bin/env bash
#
# iPanel uninstaller — removes iPanel core, config, DB, and credentials.
# Does NOT touch managed site files under /home/*.
#
set -euo pipefail

[[ $EUID -eq 0 ]] || { echo "must run as root"; exit 1; }

echo "Bu işlem iPanel'i, veritabanını ve tüm yapılandırmayı KALDIRACaktır."
echo "Yönetilen site dosyaları (/home/*) dokunulmayacaktır."
echo ""
read -r -p "Devam etmek istiyor musunuz? [y/N] " ans
[[ "$ans" =~ ^[Yy]$ ]] || exit 0

# ── Servisler ────────────────────────────────────────────────────────────────
echo "=> Servisler durduruluyor..."
systemctl disable --now ipanel-agent.service  2>/dev/null || true
systemctl disable --now ipanel-cron.timer     2>/dev/null || true
systemctl disable --now ipanel-cron.service   2>/dev/null || true
systemctl disable --now ipanel-jobs.service   2>/dev/null || true
rm -f /etc/systemd/system/ipanel-agent.service \
      /etc/systemd/system/ipanel-cron.service  \
      /etc/systemd/system/ipanel-cron.timer    \
      /etc/systemd/system/ipanel-jobs.service
systemctl daemon-reload

# ── Nginx ─────────────────────────────────────────────────────────────────────
echo "=> Nginx vhost kaldırılıyor..."
rm -f /etc/nginx/sites-enabled/ipanel.conf \
      /etc/nginx/sites-available/ipanel.conf \
      /etc/nginx/conf.d/ipanel.conf \
      /etc/nginx/conf.d/ipanel-ratelimit.conf 2>/dev/null || true
nginx -t 2>/dev/null && systemctl reload nginx 2>/dev/null || true

# ── Veritabanı ────────────────────────────────────────────────────────────────
echo "=> Veritabanı ve MySQL kullanıcısı siliniyor..."
if command -v mysql >/dev/null 2>&1; then
    mysql -e "DROP DATABASE IF EXISTS ipanel;" 2>/dev/null || true
    mysql -e "DROP USER IF EXISTS 'ipanel'@'localhost';" 2>/dev/null || true
    mysql -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    echo "   Veritabanı 'ipanel' ve kullanıcı 'ipanel'@'localhost' silindi."
else
    echo "   UYARI: mysql komutu bulunamadı, veritabanı manuel silinmeli."
fi

# ── Dosyalar ve dizinler ──────────────────────────────────────────────────────
echo "=> Dosyalar kaldırılıyor..."
rm -f  /usr/local/bin/ipanel
rm -rf /usr/local/ipanel \
       /etc/ipanel       \
       /var/log/ipanel   \
       /run/ipanel
rm -f  /etc/tmpfiles.d/ipanel.conf

echo ""
echo "iPanel tamamen kaldırıldı."
echo "Yönetilen site dosyaları (/home/*) dokunulmadı."
