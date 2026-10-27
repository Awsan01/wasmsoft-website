# Rollback

What to do when a release breaks the site, or when content was lost by mistake. It assumes the release-directory layout from [deployment.md](deployment.md#7-updating-the-site), where each release has its own directory and `current` points at the live one. Tag every release in git (for example `git tag release-2026-10-26`) so the previous version is easy to find.

## Which case is it?

| Situation | What to do |
|---|---|
| The new release misbehaves and it contained **no migrations** | [Switch back to the previous release](#switching-back-to-the-previous-release). |
| The new release contained **migrations** | [Roll back its migrations](#releases-with-migrations) first, then switch back. |
| Content, users or settings were changed or deleted by mistake | [Recover from a backup](#recovering-content). There is no undo in the dashboard. |

## Switching back to the previous release

```sh
ln -sfn /var/www/wasmsoft/releases/<previous-release> /var/www/wasmsoft/current
cd /var/www/wasmsoft/current
php artisan optimize:clear && php artisan optimize
sudo systemctl reload php8.4-fpm
php artisan cache:clear        # drops public content cached by the newer release
php artisan queue:restart
php artisan inertia:stop-ssr   # only with server-side rendering: restarts it with this release's bundle
```

Without release directories: `git checkout <previous-tag>`, then `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build` and the commands above.

## Releases with migrations

Migrations can only be rolled back by the code that contains them, so roll them back **before** switching the code:

```sh
php artisan down --render="errors::503"
php artisan migrate:status           # the release's migrations share the highest batch number
php artisan migrate:rollback --force # rolls back the last batch, i.e. the last `migrate` run
# now switch back to the previous release as above, then:
php artisan up
```

Every migration in this project has a `down()` method; rolling all of them back and applying them again was tested on 2026-10-26. A rollback restores the structure, not data: if a migration removed or rewrote data, restore the database backup taken just before the deployment instead ([backup-and-restore.md](backup-and-restore.md#restoring)) and then switch back to the previous release.

If the previous code works with the new structure (most migrations only add tables or columns), you can also keep the migrations and switch the code back only.

## Recovering content

The dashboard deletes permanently: there is no recycle bin or revision history. The audit log (Dashboard › Audit log, administrators only) shows who changed or deleted what and when, which tells you which backup to use.

1. Restore the most recent backup from before the mistake into a **separate** database (never over the live one), following [backup-and-restore.md](backup-and-restore.md#restoring).
2. Copy the lost rows back, with their translations and media, into the live database, or re-enter the content in the dashboard using the restored copy as reference. Copying rows needs a developer: content is spread over a main table, its `*_translations` table, and for some items tags, gallery images and menu links.
3. If only media files were lost, copy them back from the media backup to the same paths.

## Afterwards

- Check `/up`, the home page in every language, an article, the contact form and signing in to the dashboard.
- Jobs that failed during the incident: `php artisan queue:failed`, then `php artisan queue:retry all` once the cause is fixed.
- Write down what happened and fix it in a new release, rather than editing files on the server.
