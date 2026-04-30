#!/usr/bin/env bash
# download-icons.sh — Tabler Icons webfont'u manuel indir
#
# Kullanım: sudo bash /usr/local/ipanel/install/scripts/download-icons.sh

set -euo pipefail

IPANEL_ROOT="${IPANEL_ROOT:-/usr/local/ipanel}"
THEMES="$IPANEL_ROOT/web/iApp/Themes/Tabler"
BASE="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont"

mkdir -p "$THEMES/css" "$THEMES/fonts"

# Mevcut en son sürümü bul
echo "→ Tabler Icons sürümü kontrol ediliyor..."
ICONS_VER=$(curl -sf --connect-timeout 10 "https://data.jsdelivr.com/v1/package/npm/@tabler/icons-webfont" \
    | grep -oP '"tags":\{"latest":"[^"]+' | grep -oP '[0-9]+\.[0-9]+\.[0-9]+' | head -1 2>/dev/null \
    || echo "3.30.0")
echo "  Sürüm: ${ICONS_VER}"

VER_BASE="${BASE}@${ICONS_VER}"

dl() {
    local url="$1" dest="$2"
    if curl -sf --connect-timeout 15 --max-time 90 "$url" -o "$dest" 2>/dev/null; then
        echo "  ✓  $(basename "$dest")"
        return 0
    fi
    return 1
}

echo "→ Tabler Icons indiriliyor..."

# CSS — önce root, yoksa dist/ dene
if ! dl "${VER_BASE}/tabler-icons.min.css" "$THEMES/css/tabler-icons.min.css"; then
    dl "${VER_BASE}/dist/tabler-icons.min.css" "$THEMES/css/tabler-icons.min.css" \
        || { echo "HATA: tabler-icons.min.css indirilemedi." >&2; exit 1; }
    # dist/ içinden geldi; font yolları ../fonts/ → CSS doğru olur
fi

# Font dosyaları — önce root/fonts, yoksa dist/fonts
if ! dl "${VER_BASE}/fonts/tabler-icons.woff2" "$THEMES/fonts/tabler-icons.woff2"; then
    dl "${VER_BASE}/dist/fonts/tabler-icons.woff2" "$THEMES/fonts/tabler-icons.woff2" || true
fi
if ! dl "${VER_BASE}/fonts/tabler-icons.woff" "$THEMES/fonts/tabler-icons.woff"; then
    dl "${VER_BASE}/dist/fonts/tabler-icons.woff" "$THEMES/fonts/tabler-icons.woff" || true
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
    restorecon -RF "$THEMES/css/tabler-icons.min.css" \
        "$THEMES/fonts/tabler-icons.woff2" \
        "$THEMES/fonts/tabler-icons.woff" 2>/dev/null || true
fi

echo ""
echo "Tabler Icons başarıyla indirildi (v${ICONS_VER})."
