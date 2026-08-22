#!/usr/bin/env bash
#
# update.sh — safe in-place updater for an EXISTING ReadyEcommerce install.
#
#   ./update.sh /path/to/new-release-folder [--yes]
#
# Run from the LIVE project root. Preserves .env, storage/, uploads.
#
set -euo pipefail

NEW_RELEASE="${1:-}"
ASSUME_YES=0
[[ "${2:-}" == "--yes" || "${1:-}" == "--yes" ]] && ASSUME_YES=1

if [[ -z "$NEW_RELEASE" || "$NEW_RELEASE" == "--yes" ]]; then
    echo "Usage: ./update.sh /path/to/new-release-folder [--yes]" >&2
    exit 1
fi

# ---------------------------------------------------------------- sanity
[[ -f "$NEW_RELEASE/artisan" ]] || { echo "ERROR: $NEW_RELEASE is not a release folder (no artisan)." >&2; exit 1; }
[[ -f artisan && -f .env ]] || { echo "ERROR: run this from the live project root (needs artisan + .env)." >&2; exit 1; }
command -v php >/dev/null 2>&1 || { echo "ERROR: php not found in PATH." >&2; exit 1; }

OLD_VERSION="unknown"; NEW_VERSION="unknown"
[[ -f VERSION ]] && OLD_VERSION="$(tr -d '[:space:]' < VERSION)"
[[ -f "$NEW_RELEASE/VERSION" ]] && NEW_VERSION="$(tr -d '[:space:]' < "$NEW_RELEASE/VERSION")"

echo "==> Updating $OLD_VERSION -> $NEW_VERSION"
echo "    live : $(pwd)"
echo "    new  : $NEW_RELEASE"

if [[ "$ASSUME_YES" -eq 0 ]]; then
    read -r -p "Continue? [y/N] " answer
    [[ "$answer" == "y" || "$answer" == "Y" ]] || { echo "Aborted."; exit 1; }
fi

# ---------------------------------------------------------------- backup
mkdir -p storage/backups
if command -v mysqldump >/dev/null 2>&1; then
    DB_HOST="$(grep -E '^DB_HOST=' .env | cut -d= -f2- | tr -d '"' || true)"
    DB_PORT="$(grep -E '^DB_PORT=' .env | cut -d= -f2- | tr -d '"' || true)"
    DB_NAME="$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"' || true)"
    DB_USER="$(grep -E '^DB_USERNAME=' .env | cut -d= -f2- | tr -d '"' || true)"
    DB_PASS="$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2- | tr -d '"' || true)"
    BACKUP="storage/backups/pre-update-${NEW_VERSION}-$(date +%Y%m%d-%H%M%S).sql"

    echo "==> Backing up database '$DB_NAME' to $BACKUP"
    if ! mysqldump -h "${DB_HOST:-127.0.0.1}" -P "${DB_PORT:-3306}" -u "$DB_USER" \
        ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" > "$BACKUP" 2>/dev/null; then
        echo "!!! WARNING: database backup FAILED — you have no fresh backup !!!"
        rm -f "$BACKUP"
        if [[ "$ASSUME_YES" -eq 0 ]]; then
            read -r -p "Continue WITHOUT a backup? [y/N] " answer
            [[ "$answer" == "y" || "$answer" == "Y" ]] || { echo "Aborted."; exit 1; }
        fi
    fi
else
    echo "!!! WARNING: mysqldump not found — skipping database backup !!!"
fi

# ---------------------------------------------------------------- update
php artisan down || true

echo "==> Syncing new release over the live install"
rsync -a --delete \
    --exclude='.env' \
    --exclude='storage/' \
    --exclude='public/storage' \
    --exclude='public/uploads' \
    --exclude='.git' \
    "$NEW_RELEASE/" ./

if [[ ! -f vendor/autoload.php ]] && command -v composer >/dev/null 2>&1; then
    echo "==> vendor/ missing — composer install --no-dev"
    composer install --no-dev --optimize-autoloader --no-interaction
fi

echo "==> Running migrations (additive — existing data preserved)"
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart || true
php artisan up

echo ""
echo "================================================================"
echo " Updated $OLD_VERSION -> $NEW_VERSION"
echo " Reminders:"
echo "  - restart workers:  supervisorctl restart all   (if used)"
echo "  - reload PHP-FPM:   sudo systemctl reload php*-fpm  (if used)"
echo "  - diff your .env against .env.example for new keys"
echo "================================================================"
