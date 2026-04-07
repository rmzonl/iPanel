#!/usr/bin/env bash
#
# iPanel uninstaller — DOES NOT touch managed sites or databases.
#
set -euo pipefail

[[ $EUID -eq 0 ]] || { echo "must run as root"; exit 1; }

read -r -p "This will remove iPanel core (NOT user sites). Continue? [y/N] " ans
[[ "$ans" =~ ^[Yy]$ ]] || exit 0

systemctl disable --now ipanel-agent.service 2>/dev/null || true
systemctl disable --now ipanel-cron.timer    2>/dev/null || true
rm -f /etc/systemd/system/ipanel-agent.service \
      /etc/systemd/system/ipanel-cron.service \
      /etc/systemd/system/ipanel-cron.timer
systemctl daemon-reload

rm -f /usr/local/bin/ipanel
rm -f /etc/nginx/sites-enabled/ipanel.conf /etc/nginx/sites-available/ipanel.conf 2>/dev/null || true
rm -f /etc/nginx/conf.d/ipanel.conf 2>/dev/null || true
nginx -t 2>/dev/null && systemctl reload nginx 2>/dev/null || true

rm -rf /usr/local/ipanel /etc/ipanel /var/log/ipanel /run/ipanel

echo "iPanel removed. User data under /home/* and DBs are untouched."
