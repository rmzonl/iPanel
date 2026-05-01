#!/usr/bin/env bash
# download-icons.sh — Tabler Icons webfont'u npm registry'den indir
#
# Kullanım: sudo bash /usr/local/ipanel/install/scripts/download-icons.sh

set -euo pipefail

IPANEL_ROOT="${IPANEL_ROOT:-/usr/local/ipanel}"
THEMES="$IPANEL_ROOT/web/iApp/Themes/Tabler"
NPM_META="https://registry.npmjs.org/@tabler/icons-webfont/latest"

mkdir -p "$THEMES/css" "$THEMES/fonts"
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT

echo "→ Tabler Icons sürümü kontrol ediliyor..."
PKG_JSON=$(curl -fsSL --connect-timeout 15 --max-time 30 "$NPM_META")
VERSION=$(echo "$PKG_JSON" | python3 -c "import sys,json; print(json.load(sys.stdin)['version'])")
TARBALL=$(echo "$PKG_JSON" | python3 -c "import sys,json; print(json.load(sys.stdin)['dist']['tarball'])")
echo "  Sürüm: ${VERSION}"

echo "→ Tabler Icons indiriliyor..."
curl -fsSL --connect-timeout 15 --max-time 180 "$TARBALL" -o "$TMP/pkg.tgz"
echo "  ✓  tarball indirildi"

# CSS ve fontları tarball'dan çıkar
tar -xzf "$TMP/pkg.tgz" -C "$TMP" \
    "package/dist/tabler-icons.min.css" \
    "package/dist/fonts/tabler-icons.woff2" \
    "package/dist/fonts/tabler-icons.woff"

cp "$TMP/package/dist/tabler-icons.min.css"          "$THEMES/css/tabler-icons.min.css"
cp "$TMP/package/dist/fonts/tabler-icons.woff2"       "$THEMES/fonts/tabler-icons.woff2"
cp "$TMP/package/dist/fonts/tabler-icons.woff"        "$THEMES/fonts/tabler-icons.woff"
echo "  ✓  dosyalar kopyalandı"

# CSS'deki ./fonts/ yolunu ../fonts/ olarak düzelt
# (CSS css/ altında, fontlar fonts/ altında — dist/ içindeki ./fonts/ göreceli yol artık yanlış)
sed -i \
    -e 's|url("\.\/fonts/|url("../fonts/|g' \
    -e "s|url('\./fonts/|url('../fonts/|g" \
    -e 's|url(\./fonts/|url(../fonts/|g' \
    "$THEMES/css/tabler-icons.min.css"
echo "  ✓  font yolları düzeltildi (./fonts/ → ../fonts/)"

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
echo "Tabler Icons başarıyla indirildi (v${VERSION})."
