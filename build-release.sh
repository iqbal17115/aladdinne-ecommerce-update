#!/usr/bin/env bash
#
# build-release.sh — package ReadyEcommerce into a CodeCanyon-ready zip.
#
#   ./build-release.sh            # prompts for version (default from VERSION)
#   ./build-release.sh 1.2.0      # non-interactive version
#   Flags: --no-vendor  (skip composer install in stage)
#          --no-assets  (skip npm build in stage)
#
set -euo pipefail

APP_SLUG="readyecommerce"
ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT_DIR"

NO_VENDOR=0
NO_ASSETS=0
VERSION_ARG=""

for arg in "$@"; do
    case "$arg" in
        --no-vendor) NO_VENDOR=1 ;;
        --no-assets) NO_ASSETS=1 ;;
        *) VERSION_ARG="$arg" ;;
    esac
done

# ---------------------------------------------------------------- version
DEFAULT_VERSION="1.0.0"
[[ -f VERSION ]] && DEFAULT_VERSION="$(tr -d '[:space:]' < VERSION)"

if [[ -n "$VERSION_ARG" ]]; then
    VERSION="$VERSION_ARG"
elif [[ -t 0 ]]; then
    read -r -p "Release version [$DEFAULT_VERSION]: " VERSION
    VERSION="${VERSION:-$DEFAULT_VERSION}"
else
    VERSION="$DEFAULT_VERSION"
fi

echo "$VERSION" > VERSION
echo "==> Building $APP_SLUG v$VERSION"

STAMP="$(date +%Y%m%d-%H%M%S)"
STAGE="$(mktemp -d "${TMPDIR:-/tmp}/${APP_SLUG}-release.XXXXXX")"
trap 'rm -rf "$STAGE"' EXIT
mkdir -p dist
ZIP_NAME="${APP_SLUG}-v${VERSION}-${STAMP}.zip"
ZIP_PATH="$ROOT_DIR/dist/$ZIP_NAME"

# ---------------------------------------------------------------- stage
echo "==> Staging project (rsync)"
rsync -a \
    --exclude='.git' \
    --exclude='.github' \
    --exclude='node_modules' \
    --exclude='dist' \
    --exclude='tests' \
    --exclude='.env' \
    --exclude='.env.backup' \
    --exclude='.env.production' \
    --exclude='.idea' \
    --exclude='.vscode' \
    --exclude='.fleet' \
    --exclude='.DS_Store' \
    --exclude='auth.json' \
    --exclude='INSTALLER_MASTER_PROMPT.md' \
    --exclude='storage/installed' \
    --exclude='storage/app/install-token.txt' \
    --exclude='storage/*.key' \
    --exclude='storage/racord.txt' \
    --exclude='storage/app/firebase' \
    --exclude='storage/app/public/*' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/data' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='database/database.sqlite' \
    --exclude='public/storage' \
    --exclude='public/hot' \
    ./ "$STAGE/"

# ---------------------------------------------------------------- vendor
if [[ "$NO_VENDOR" -eq 0 ]]; then
    if command -v composer >/dev/null 2>&1; then
        echo "==> composer install --no-dev (stage)"
        if ! (cd "$STAGE" && composer install --no-dev --optimize-autoloader --no-interaction --quiet); then
            echo "WARN: composer install failed — shipping the existing vendor/ copy"
        fi
    else
        echo "WARN: composer not found — shipping the existing vendor/ copy"
    fi
fi

if [[ ! -f "$STAGE/vendor/autoload.php" ]]; then
    echo "ERROR: stage has no vendor/autoload.php — a CodeCanyon package must run without composer." >&2
    exit 1
fi

# ---------------------------------------------------------------- assets
if [[ "$NO_ASSETS" -eq 0 && -f "$STAGE/package.json" ]]; then
    if command -v npm >/dev/null 2>&1; then
        echo "==> npm ci && npm run build (stage)"
        if (cd "$STAGE" && npm ci --silent && npm run build --silent); then
            rm -rf "$STAGE/node_modules"
        else
            echo "WARN: asset build failed — shipping the existing public/build"
            rm -rf "$STAGE/node_modules"
        fi
    else
        echo "WARN: npm not found — shipping the existing public/build"
    fi
fi

# ---------------------------------------------------------------- scrub
echo "==> Final scrub"
rm -f  "$STAGE/.env" "$STAGE/storage/installed" "$STAGE/storage/app/install-token.txt" "$STAGE/database/database.sqlite"
rm -rf "$STAGE/storage/app/firebase"
find "$STAGE/storage/logs" -name '*.log' -delete 2>/dev/null || true
find "$STAGE/storage/framework/views" -type f ! -name '.gitignore' -delete 2>/dev/null || true
find "$STAGE/storage/framework/sessions" -type f ! -name '.gitignore' -delete 2>/dev/null || true
rm -rf "$STAGE/storage/framework/cache/data"
find "$STAGE/bootstrap/cache" -name '*.php' -delete 2>/dev/null || true
find "$STAGE/storage/app/public" -mindepth 1 ! -name '.gitignore' -delete 2>/dev/null || true

# keep the .gitignore convention in storage dirs
for d in storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; do
    mkdir -p "$STAGE/$d"
    [[ -f "$STAGE/$d/.gitignore" ]] || printf '*\n!.gitignore\n' > "$STAGE/$d/.gitignore"
done

[[ -f "$STAGE/.env.example" ]] || { echo "ERROR: .env.example missing from stage" >&2; exit 1; }

# abort if any secret/lock survived
for secret in .env storage/installed storage/app/firebase auth.json; do
    if [[ -e "$STAGE/$secret" ]]; then
        echo "ERROR: secret/lock file survived the scrub: $secret" >&2
        exit 1
    fi
done

# ---------------------------------------------------------------- zip
echo "==> Creating $ZIP_NAME"
if command -v zip >/dev/null 2>&1; then
    (cd "$STAGE" && zip -qr "$ZIP_PATH" .)
elif command -v python3 >/dev/null 2>&1; then
    python3 -c "import shutil,sys; shutil.make_archive(sys.argv[1][:-4], 'zip', sys.argv[2])" "$ZIP_PATH" "$STAGE"
else
    echo "ERROR: neither zip nor python3 available — install zip." >&2
    exit 1
fi

[[ -f "$ZIP_PATH" ]] || { echo "ERROR: zip was not created" >&2; exit 1; }

echo ""
echo "================================================================"
echo " Package : dist/$ZIP_NAME ($(du -h "$ZIP_PATH" | cut -f1 | tr -d ' '))"
echo " Ships   : WITH vendor/ + built assets, WITHOUT secrets/.env/locks"
echo " Buyer   : upload & extract -> visit https://their-domain/install"
echo "================================================================"
