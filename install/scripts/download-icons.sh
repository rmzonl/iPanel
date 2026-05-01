#!/usr/bin/env bash
# download-icons.sh — Tabler Icons webfont'u manuel indir
#
# Kullanım: sudo bash /usr/local/ipanel/install/scripts/download-icons.sh

set -euo pipefail

IPANEL_ROOT="${IPANEL_ROOT:-/usr/local/ipanel}"
THEMES="$IPANEL_ROOT/web/iApp/Themes/Tabler"
BASE="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest"

mkdir -p "$THEMES/css" "$THEMES/fonts"

dl() {
    local url="$1" dest="$2"
    if curl -fsSL --connect-timeout 15 --max-time 120 "$url" -o "$dest"; then
        echo "  ✓  $(basename "$dest")"
        return 0
    fi
    return 1
}

echo "→ Tabler Icons indiriliyor..."

# CSS — önce root, yoksa dist/ dene
if ! dl "${BASE}/tabler-icons.min.css" "$THEMES/css/tabler-icons.min.css"; then
    dl "${BASE}/dist/tabler-icons.min.css" "$THEMES/css/tabler-icons.min.css" \
        || { echo "HATA: tabler-icons.min.css indirilemedi." >&2; exit 1; }
fi

# CSS'deki font yolları css/ altından ../fonts/ olarak düzelt
sed -i \
    -e "s|url('fonts/|url('../fonts/|g" \
    -e 's|url("fonts/|url("../fonts/|g' \
    -e "s|url(fonts/|url(../fonts/|g" \
    "$THEMES/css/tabler-icons.min.css"
echo "  ✓  font yolları düzeltildi"

# Font dosyaları
if ! dl "${BASE}/fonts/tabler-icons.woff2" "$THEMES/fonts/tabler-icons.woff2"; then
    dl "${BASE}/dist/fonts/tabler-icons.woff2" "$THEMES/fonts/tabler-icons.woff2" \
        || echo "  !  tabler-icons.woff2 indirilemedi (opsiyonel)"
fi
if ! dl "${BASE}/fonts/tabler-icons.woff" "$THEMES/fonts/tabler-icons.woff"; then
    dl "${BASE}/dist/fonts/tabler-icons.woff" "$THEMES/fonts/tabler-icons.woff" \
        || echo "  !  tabler-icons.woff indirilemedi (opsiyonel)"
fi

# İzinler
chown nginx:nginx \
    "$THEMES/css/tabler-icons.min.css" \
    "$THEMES/fonts/tabler-icons.woff2" \
    "$THEMES/fonts/tabler-icons.woff" 2>/dev/null || true
chmod 644 \
    "$THEMES/css/tabler-icons.min.css" \
    "$THEMES/fonts/tabler-icons.woff2" \
    "$THEMES/fonts/tabler-icons.woff" 2>/dev/null || true

if command -v restorecon >/dev/null 2>&1; then
    restorecon -RF \
        "$THEMES/css/tabler-icons.min.css" \
        "$THEMES/fonts/tabler-icons.woff2" \
        "$THEMES/fonts/tabler-icons.woff" 2>/dev/null || true
fi

echo ""
echo "Tabler Icons başarıyla indirildi."
