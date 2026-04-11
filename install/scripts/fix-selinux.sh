#!/usr/bin/env bash
# fix-selinux.sh — Mevcut kurulumlar için SELinux agent socket düzeltmesi
#
# Kullanım: sudo bash install/scripts/fix-selinux.sh
#
# Bu betik iki şeyi yapar:
#   1. /var/run/ipanel için doğru SELinux bağlamını (httpd_var_run_t) ayarlar
#   2. httpd_t'nin agent socket'e bağlanmasına izin veren policy modülünü yükler
#
# AlmaLinux/RHEL 9 kurulumlarında gereklidir.

set -euo pipefail

if [[ $EUID -ne 0 ]]; then
    echo "Hata: Bu betik root olarak çalıştırılmalıdır." >&2
    exit 1
fi

IPANEL_ROOT="${IPANEL_ROOT:-/usr/local/ipanel}"
TE_FILE="$IPANEL_ROOT/install/selinux/ipanel_agent.te"

if ! command -v getenforce >/dev/null 2>&1 || [[ "$(getenforce)" == "Disabled" ]]; then
    echo "SELinux devre dışı — bu betik gerekli değil."
    exit 0
fi

echo "SELinux modu: $(getenforce)"

# 1. Bağlam düzeltmesi
echo "→ /var/run/ipanel için httpd_var_run_t bağlamı ayarlanıyor..."
semanage fcontext -a -t httpd_var_run_t '/var/run/ipanel(/.*)?' 2>/dev/null \
    || semanage fcontext -m -t httpd_var_run_t '/var/run/ipanel(/.*)?' 2>/dev/null \
    || { echo "WARN: semanage başarısız — zaten eklenmiş olabilir."; }

restorecon -Rv /run/ipanel/ 2>/dev/null || true

echo "→ Mevcut socket bağlamı:"
ls -laZ /run/ipanel/ 2>/dev/null || echo "  (socket henüz yok)"

# 2. Policy modülü
echo "→ ipanel_agent SELinux policy modülü derleniyor..."

if ! command -v checkmodule >/dev/null 2>&1; then
    echo "HATA: checkmodule bulunamadı. Kurun: dnf install policycoreutils-devel" >&2
    exit 1
fi

if [[ ! -f "$TE_FILE" ]]; then
    echo "HATA: $TE_FILE bulunamadı." >&2
    exit 1
fi

MOD_TMP="$(mktemp -d)"
trap 'rm -rf "$MOD_TMP"' EXIT

checkmodule -M -m -o "$MOD_TMP/ipanel_agent.mod" "$TE_FILE"
semodule_package -o "$MOD_TMP/ipanel_agent.pp" -m "$MOD_TMP/ipanel_agent.mod"
semodule -i "$MOD_TMP/ipanel_agent.pp"

echo "✓ ipanel_agent policy modülü yüklendi."
echo ""
echo "→ Agent servisini yeniden başlatın:"
echo "    systemctl restart ipanel-agent"
echo ""
echo "→ PHP-FPM'i yeniden başlatın:"
echo "    systemctl restart php-fpm"
echo ""
echo "Ardından /stats/stream endpoint'ini test edin."
