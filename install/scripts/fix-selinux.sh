#!/usr/bin/env bash
# fix-selinux.sh — Mevcut kurulumlar için SELinux düzeltmesi
#
# Kullanım: sudo bash /usr/local/ipanel/install/scripts/fix-selinux.sh
#
# Bu betik üç şeyi yapar:
#   1. /var/run/ipanel için httpd_var_run_t bağlamı (agent socket)
#   2. web/iApp/Storage ve web/Uploads için httpd_sys_rw_content_t bağlamı
#      (session, cache, uploads yazma izni)
#   3. httpd_t → httpd_var_run_t:sock_file connectto policy modülü
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

# ─── 1. Agent socket dizini ─────────────────────────────────────────────────
echo ""
echo "→ [1/3] /var/run/ipanel → httpd_var_run_t"
semanage fcontext -a -t httpd_var_run_t '/var/run/ipanel(/.*)?' 2>/dev/null \
    || semanage fcontext -m -t httpd_var_run_t '/var/run/ipanel(/.*)?' 2>/dev/null \
    || echo "    WARN: semanage başarısız (zaten eklenmiş olabilir)."
restorecon -Rv /run/ipanel/ 2>/dev/null || true
echo "    Mevcut socket bağlamı:"
ls -laZ /run/ipanel/ 2>/dev/null || echo "    (socket henüz yok — agent başlatıldığında ayarlanacak)"

# ─── 2. Web Storage dizini ──────────────────────────────────────────────────
echo ""
echo "→ [2/3] web/iApp/Storage → httpd_sys_rw_content_t"
echo "         (PHP-FPM session, cache, ZN Framework log yazma izni)"
semanage fcontext -a -t httpd_sys_rw_content_t "$IPANEL_ROOT/web/iApp/Storage(/.*)?" 2>/dev/null \
    || semanage fcontext -m -t httpd_sys_rw_content_t "$IPANEL_ROOT/web/iApp/Storage(/.*)?" 2>/dev/null \
    || echo "    WARN: semanage başarısız."
restorecon -Rv "$IPANEL_ROOT/web/iApp/Storage/" 2>/dev/null || true

echo ""
echo "→ [2/3] web/Uploads → httpd_sys_rw_content_t"
semanage fcontext -a -t httpd_sys_rw_content_t "$IPANEL_ROOT/web/Uploads(/.*)?" 2>/dev/null \
    || semanage fcontext -m -t httpd_sys_rw_content_t "$IPANEL_ROOT/web/Uploads(/.*)?" 2>/dev/null \
    || echo "    WARN: semanage başarısız."
restorecon -Rv "$IPANEL_ROOT/web/Uploads/" 2>/dev/null || true

echo ""
echo "→ [2/3] web/map.php → httpd_sys_rw_content_t"
echo "         (ZN Autoloader bu dosyaya yazar; usr_t bağlamında httpd_t yazamaz)"
semanage fcontext -a -t httpd_sys_rw_content_t "$IPANEL_ROOT/web/map\\.php" 2>/dev/null \
    || semanage fcontext -m -t httpd_sys_rw_content_t "$IPANEL_ROOT/web/map\\.php" 2>/dev/null \
    || echo "    WARN: semanage başarısız."
touch "$IPANEL_ROOT/web/map.php"
restorecon "$IPANEL_ROOT/web/map.php" 2>/dev/null || true
chown nginx:nginx "$IPANEL_ROOT/web/map.php" 2>/dev/null || true
chmod 664 "$IPANEL_ROOT/web/map.php"
echo "    map.php bağlamı:"
ls -laZ "$IPANEL_ROOT/web/map.php" 2>/dev/null

echo "    Storage bağlamları:"
ls -laZ "$IPANEL_ROOT/web/iApp/Storage/" 2>/dev/null | head -5

# ─── 3. Policy modülü ───────────────────────────────────────────────────────
echo ""
echo "→ [3/3] ipanel_agent SELinux policy modülü derleniyor..."

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
echo "    ✓ ipanel_agent policy modülü yüklendi."

# ─── Servisler ───────────────────────────────────────────────────────────────
echo ""
echo "→ Servisler yeniden başlatılıyor..."
systemctl restart ipanel-agent
systemctl restart php-fpm
echo "    ✓ ipanel-agent ve php-fpm yeniden başlatıldı."
echo ""
echo "Tüm SELinux düzeltmeleri tamamlandı."
echo "Panel: https://$(hostname -I | awk '{print $1}'):3333"
