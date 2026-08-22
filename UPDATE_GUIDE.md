# ReadyEcommerce — Update Guide

## What an update preserves vs replaces

| Preserved (never touched) | Replaced (new release wins) |
|---|---|
| `.env` (all your config & keys) | All application code (`app/`, `routes/`, `resources/`, …) |
| `storage/` (uploads, logs, backups) | `vendor/` (shipped with each release) |
| `public/storage` symlink & `public/uploads` | `public/build` compiled assets |
| Your database (migrations are additive) | `composer.lock`, `VERSION` |

## Option A — update.sh (recommended)

1. Upload & extract the new release zip somewhere OUTSIDE the live folder,
   e.g. `/home/user/releases/readyecommerce-v1.1.0`.
2. From the live project root run:

   ```bash
   ./update.sh /home/user/releases/readyecommerce-v1.1.0
   ```

   It will: back up the DB to `storage/backups/`, enter maintenance mode,
   rsync the new code over the install (preserving `.env` + `storage/` +
   uploads), run new migrations only, rebuild caches, and bring the site up.

## Option B — manual overwrite

1. Back up your database and your `.env`.
2. `php artisan down`
3. Copy the new release over the live folder, **except**: do NOT overwrite
   `.env`, and do NOT delete `storage/` or `public/storage`/`public/uploads`.
4. ```bash
   php artisan migrate --force
   php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
   php artisan queue:restart
   php artisan up
   ```

## Rollback

1. `php artisan down`
2. Restore the previous release's files (keep your `.env` + `storage/`).
3. Restore the DB backup from `storage/backups/pre-update-*.sql`:
   `mysql -u USER -p DBNAME < storage/backups/pre-update-….sql`
4. `php artisan optimize:clear && php artisan up`

## Seller checklist (before shipping a release)

- Bump `VERSION` (build-release.sh persists the version you enter).
- Keep migrations **additive and idempotent** — guard with
  `Schema::hasTable()` / `Schema::hasColumn()` so they are safe to run on
  populated production databases. Never rename/drop columns in a point release.
- Run `./build-release.sh <version>` and verify the zip contains `vendor/`
  and `.env.example`, and does NOT contain `.env`, `storage/installed`, or
  any credentials.
- Test `./update.sh` from the previous release on a staging copy.
