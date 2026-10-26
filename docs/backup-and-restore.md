# Backup and restore

## What to back up

| Item | Where it lives | Notes |
|---|---|---|
| Database | The production database | All content in every language, users, settings, menus, inquiries (personal data) and the audit log. |
| Uploaded media | `storage/app/public/media` (or the S3 bucket when `MEDIA_DISK=s3`) | Original files and their resized WebP versions. The database refers to them by path, so database and media must come from the same day. |
| Secrets | `.env` or your secrets store | `APP_KEY`, database and mail passwords. No database value is encrypted with `APP_KEY`, so a backup can be restored without it; losing the key only signs everyone out and invalidates open forms and emailed links. Keep a copy anyway. |

Not needed: `vendor/`, `node_modules/` and `public/build/` (rebuilt from the code), `storage/framework/` (caches and sessions) and logs.

## Policy

Proposed defaults; adjust them to the company's needs and its hosting provider's own backups.

- **Database:** a full backup every night at 02:30, before the 03:15 clean-up of old inquiries and audit entries, plus one immediately before every deployment that contains migrations.
- **Media:** synchronised every night to the backup location (or bucket versioning with S3).
- **Where:** off the web server, at another provider or region, encrypted at rest. Access limited to the people who run the site.
- **How long:** 14 nightly and 8 weekly copies. Backups contain inquiries, so they should not live much longer than `INQUIRY_RETENTION_DAYS` (two years by default); otherwise messages deleted from the site survive in old backups.
- **Testing:** restore into a separate database at least every three months and after major releases, following the procedure below.
- **Targets:** at most one day of changes lost (recovery point), the site back within a few hours (recovery time).

## Making a backup

MySQL or MariaDB:

```sh
mysqldump --single-transaction --default-character-set=utf8mb4 -u wasmsoft -p wasmsoft \
  | gzip > wasmsoft-db-$(date +%F-%H%M).sql.gz
```

PostgreSQL:

```sh
pg_dump --format=custom --no-owner -U wasmsoft wasmsoft > wasmsoft-db-$(date +%F-%H%M).dump
```

SQLite (never copy the file while the site may write to it; use the backup command):

```sh
sqlite3 database/database.sqlite ".backup 'wasmsoft-db-$(date +%F-%H%M).sqlite'"
```

Media:

```sh
tar -czf wasmsoft-media-$(date +%F).tar.gz -C storage/app public
# or keep a mirror: rsync -a --delete storage/app/public/ backup-host:/backups/wasmsoft/media/
```

Then copy the files to the backup location and remove local copies.

## Restoring

1. Put the site in maintenance mode: `php artisan down --render="errors::503"`.
2. Restore the database into a **new, empty** database, never over the live one:
   - MySQL/MariaDB: `gunzip < wasmsoft-db-….sql.gz | mysql -u wasmsoft -p wasmsoft_restore`
   - PostgreSQL: `pg_restore --no-owner -U wasmsoft -d wasmsoft_restore wasmsoft-db-….dump`
   - SQLite: copy the backup file to the database path.
3. Check it (next section), then point `DB_DATABASE` in `.env` at the restored database.
4. Restore the media from the same day: `tar -xzf wasmsoft-media-….tar.gz -C storage/app`.
5. Bring the schema up to the deployed code and refresh the caches:

   ```sh
   php artisan migrate --force
   php artisan optimize:clear && php artisan optimize
   php artisan cache:clear
   php artisan queue:restart
   ```

6. `php artisan up`, then check `/up`, the home page in each language, an article with an image, and signing in to the dashboard.

## Checking a backup

- The database opens and is intact: import it into a scratch database (MySQL/PostgreSQL) or run `PRAGMA integrity_check;` (SQLite, expects `ok`).
- Row counts of `users`, `pages`, `services`, `projects`, `posts`, `media`, `inquiries`, `settings` and `audit_logs` match the live database at the time of the backup.
- `php artisan migrate:status` against the restored database lists every migration as ran.
- The media archive unpacks and contains as many files as `storage/app/public`.

## What has been verified

On 2026-10-26, on a copy of the development database (SQLite):

- A backup made with SQLite's `VACUUM INTO` passed `PRAGMA integrity_check`, had the same row counts as the source in all nine tables listed above, and `migrate:status` on it showed all 18 migrations as ran.
- A `tar` archive of `storage/app/public` unpacked into 11 files identical to the originals.
- Every migration was rolled back (`migrate:reset`) and applied again without errors, which [rollback.md](rollback.md) relies on.

The MySQL and PostgreSQL commands above are the standard tools for those databases but have not been run against this project (no such server was available). Test the backup and restore of the production setup before going live.
