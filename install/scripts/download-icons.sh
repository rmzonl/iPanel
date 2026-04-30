#!/usr/bin/env bash
# download-icons.sh — Tabler Icons webfont'u manuel indir
#
# Kullanım: sudo bash /usr/local/ipanel/install/scripts/download-icons.sh
#
# Bu script, kurulum sırasında internet erişimi olmayan sistemler için
# veya güncelleme sonrası ikonu eksik kalan kurulumlar için kullanılır.

set -euo pipefail

IPANEL_ROOT="${IPANEL_ROOT:-/usr/local/ipanel}"
THEMES="$IPANEL_ROOT/web/iApp/Themes/Tabler"
ICONS_VER="${ICONS_VER:-3.31.0}"
BASE="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@${ICONS_VER}"

echo "→ Tabler Icons v${ICONS_VER} indiriliyor..."

mkdir -p "$THEMES/css" "$THEMES/fonts"

curl -f --connect-timeout 15 --max-time 90 \
    "$BASE/tabler-icons.min.css" -o "$THEMES/css/tabler-icons.min.css"
echo "  ✓  tabler-icons.min.css"

curl -f --connect-timeout 15 --max-time 90 \
    "$BASE/fonts/tabler-icons.woff2" -o "$THEMES/fonts/tabler-icons.woff2"
echo "  ✓  tabler-icons.woff2"

curl -f --connect-timeout 15 --max-time 90 \
    "$BASE/fonts/tabler-icons.woff" -o "$THEMES/fonts/tabler-icons.woff"
echo "  ✓  tabler-icons.woff"

chown -R "${SUDO_USER:-root}":root "$THEMES/css/tabler-icons.min.css" \
    "$THEMES/fonts/tabler-icons.woff2" "$THEMES/fonts/tabler-icons.woff" 2>/dev/null || true

if command -v restorecon >/dev/null 2>&1; then
    restorecon -RF "$THEMES/css/tabler-icons.min.css" \
        "$THEMES/fonts/tabler-icons.woff2" \
        "$THEMES/fonts/tabler-icons.woff" 2>/dev/null || true
fi

echo ""
echo "Tabler Icons başarıyla indirildi."
