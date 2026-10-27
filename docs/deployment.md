# Deployment

How to install, configure, update and watch the Wasmsoft website in production. Backups are covered in [backup-and-restore.md](backup-and-restore.md) and undoing a release in [rollback.md](rollback.md).

The application has not been deployed yet. Nothing here assumes a particular host: it describes a Linux server with nginx and PHP-FPM, which other setups (Laravel Cloud, Forge, containers) can translate.

## 1. What the server needs

| Need | Details |
|---|---|
| PHP 8.4 with PHP-FPM | Extensions: `gd` with JPEG, PNG and WebP support, `intl`, `mbstring`, `fileinfo`, `openssl`, `pdo` with the database driver, `tokenizer`, `xml`, `ctype`; optionally `exif` (photo orientation), and `curl` when server-side rendering is used. Enable OPcache. Set `expose_php = Off`. |
| Upload limits | `upload_max_filesize = 16M` and `post_max_size = 20M` in PHP, `client_max_body_size 20m;` in nginx (documents may be up to 15 MB, images 8 MB; see `MEDIA_MAX_*`). |
| Web server | nginx (or Apache) serving `public/` over HTTPS, with gzip or brotli compression (example below). |
| Database | MySQL, MariaDB or PostgreSQL as supported by Laravel 13. SQLite also works for a small single-server site. |
| Scheduler | A cron entry running `php artisan schedule:run` every minute. |
| Queue worker | A long-running `php artisan queue:work` process (supervisor or systemd). Without it, password reset emails, invitations and new-inquiry alerts are never sent. |
| Outgoing HTTPS | To the mail provider and to `api.pwnedpasswords.com` (see section 10). |
| Build tools | Composer 2, and Node.js 22 with npm where the assets are built (the server or a CI job). |
| Node.js 22 on the server | Only for [server-side rendering](#server-side-rendering), which is optional but recommended. |

The automated tests run on SQLite. The migrations only use portable column types and indexes, but the suite has not yet been run against MySQL, MariaDB or PostgreSQL. Do that once on the chosen engine before going live: create an empty test database, then run for example `DB_CONNECTION=mysql DB_DATABASE=wasmsoft_test DB_USERNAME=… DB_PASSWORD=… php artisan test`. On PostgreSQL, searches in the dashboard and on the project list are case-sensitive (its `LIKE` is); on MySQL, MariaDB and SQLite they are not.

## 2. Production settings (`.env`)

Start from `.env.example`; every variable is explained there. These must change for production:

| Variable | Production value |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` (otherwise errors show code and configuration) |
| `APP_KEY` | Generated once with `php artisan key:generate` on the first install, then kept in your secrets store. Changing it signs everyone out and makes invitation and reset links invalid. |
| `APP_URL` | `https://` address of the site, without a trailing slash |
| `APP_LOCALE` | Default language before Settings are saved (`ar`, `en` or `tr`) |
| `LOG_STACK` / `LOG_LEVEL` | `daily` / `warning` (or a log service) |
| `DB_*` | Connection of the production database, with a user that has rights on that database only |
| `MAIL_*` | Your mail provider. `MAIL_FROM_ADDRESS` must be an address the provider may send from. |
| `QUEUE_CONNECTION`, `CACHE_STORE`, `SESSION_DRIVER` | `database` works without extra services (the tables exist); `redis` if you run Redis |
| `TRUSTED_PROXIES` | Addresses of a load balancer or CDN in front of the site, if any (see `.env.example`) |
| `DISPLAY_TIMEZONE` | The company's time zone, e.g. `Europe/Istanbul` |
| `MEDIA_DISK` | `public` (files in `storage/app/public`) or `s3` with the `AWS_*` variables |
| `HTTP_CACHE_SECONDS` | How long a CDN or proxy may keep public pages (default 300) |

Session cookies are sent over HTTPS only whenever `APP_ENV=production`. Do not set `DEV_SEED_PASSWORD` in production; the development seeders refuse to run there anyway.

Keep `.env` out of the repository and readable only by the deploy user and PHP-FPM (`chmod 640`).

## 3. First installation

On the server, in the release directory:

```sh
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build          # or copy public/build (and bootstrap/ssr) from a CI build
cp .env.example .env             # then fill in the production values
php artisan key:generate         # first installation only
php artisan migrate --force
php artisan storage:link         # public/storage -> storage/app/public (MEDIA_DISK=public)
php artisan optimize             # caches configuration, routes, views and events
php artisan wasmsoft:create-admin
```

`wasmsoft:create-admin` asks for a name, an email address and a password (at least 12 characters with upper- and lower-case letters and numbers; in production it is also checked against known data breaches). Sign in at `/admin` and:

1. Settings: company name, tagline and description in each language (the description is the default text search engines show), enabled languages and the default one, logo, favicon, social sharing image, contact details, the address that receives inquiry alerts, social links and the footer note.
2. Pages: the home page texts (the hero), the about page and the introductions of the services, projects, news and contact pages, in every enabled language.
3. Services, projects, news, and the home and about page items, with real content.
4. Navigation: check the header and footer menus.
5. Users: invite the other staff members (they set their own password from the invitation email).

Check [project-status.md](project-status.md) for the business information that is still missing.

## 4. Scheduler and queue worker

Cron entry for the deploy user:

```cron
* * * * * cd /var/www/wasmsoft/current && php artisan schedule:run >> /dev/null 2>&1
```

It runs:

| Task | When | Purpose |
|---|---|---|
| `wasmsoft:release-scheduled` | every minute | Clears the public content cache when scheduled articles, projects or pages go live |
| `model:prune` | daily at 03:15 | Deletes inquiries after `INQUIRY_RETENTION_DAYS` (spam after 30 days) and audit entries after `AUDIT_RETENTION_DAYS` |

Queue worker, for example with supervisor (`/etc/supervisor/conf.d/wasmsoft-worker.conf`):

```ini
[program:wasmsoft-worker]
command=php /var/www/wasmsoft/current/artisan queue:work --tries=3 --max-time=3600
user=www-data
autostart=true
autorestart=true
stopwaitsecs=60
redirect_stderr=true
stdout_logfile=/var/www/wasmsoft/shared/storage/logs/worker.log
```

Run `php artisan queue:restart` after every deployment so the worker loads the new code. Failed jobs are listed with `php artisan queue:failed`.

### Server-side rendering

Optional, and recommended for production. With it, every public page arrives with its content already in the HTML, so it appears before the JavaScript has loaded and run: on the slow phone profile of `npm run measure:performance`, the first paint came 1.4 to 1.8 seconds earlier ([project status](project-status.md)). Crawlers that do not run JavaScript then read the full text too. Without it, pages are drawn in the browser; their titles, descriptions and other `<head>` metadata are in the HTML either way.

It needs Node.js 22 on the server and a long-running process next to PHP-FPM:

1. `npm run build` builds the bundle the process runs (`bootstrap/ssr/ssr.mjs`) together with the assets.
2. Set `INERTIA_SSR_ENABLED=true` in `.env`, then run `php artisan optimize` again.
3. Keep the rendering service running, for example with supervisor (`/etc/supervisor/conf.d/wasmsoft-ssr.conf`):

   ```ini
   [program:wasmsoft-ssr]
   command=php /var/www/wasmsoft/current/artisan inertia:start-ssr
   user=www-data
   autostart=true
   autorestart=true
   stopwaitsecs=10
   redirect_stderr=true
   stdout_logfile=/var/www/wasmsoft/shared/storage/logs/ssr.log
   ```

4. After every deployment, run `php artisan inertia:stop-ssr`: supervisor starts the service again with the new bundle.

The service listens on `127.0.0.1:13714` (`INERTIA_SSR_URL`) and must not be reachable from outside. If it stops, pages are drawn in the browser until it is back; nothing else breaks.

## 5. Web server

Example nginx server block (adapt paths, names and certificates):

```nginx
server {
    listen 443 ssl;
    http2 on;
    server_name example.com;
    root /var/www/wasmsoft/current/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/example.com/privkey.pem;

    client_max_body_size 20m;
    charset utf-8;

    gzip on;
    gzip_comp_level 5;
    gzip_types text/css application/javascript application/json application/xml image/svg+xml text/plain;
    # text/html is always compressed once gzip is on.

    # Built assets and uploaded media have unique file names: cache them for a long time.
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location /storage/media/ {
        expires 30d;
        add_header Cache-Control "public";
        add_header X-Content-Type-Options "nosniff";
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }
}

server {
    listen 80;
    server_name example.com;
    return 301 https://$host$request_uri;
}
```

The application sends its own security headers (Content-Security-Policy, HSTS over HTTPS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`). Do not add a second Content-Security-Policy in the web server: browsers would apply both.

A public address answers with an HTML page, or with the same page's data as JSON when the site itself asks for it while a visitor moves between pages (requests with an `X-Inertia` header). The responses say so in their `Vary` header; a CDN or caching proxy in front of the site must respect it, or cache only requests without an `X-Inertia` header.

Compression matters: the home page HTML is about 33 kB raw and 7 kB compressed (more with server-side rendering), the stylesheet about 74 kB raw and 13 kB compressed, and the JavaScript of a page, mostly React and Inertia shared by all pages, about 370 kB raw and 120 kB compressed.

## 6. File permissions

- The code belongs to the deploy user and is read-only for PHP-FPM.
- PHP-FPM (for example `www-data`) needs write access only to `storage/` and `bootstrap/cache/`.
- `.env`: `chmod 640`, owned by the deploy user, group of PHP-FPM.
- Uploaded files are stored under generated names with an extension taken from their detected type; PHP is never executed from `storage/` (the nginx block above only sends `.php` requests inside `public/` to PHP-FPM, and `public/storage` holds only media).

## 7. Updating the site

Deploy each release into a new directory and switch a `current` symlink, so a failed build never reaches visitors and the previous release stays available for [rollback](rollback.md). `storage/` and `.env` live in a shared directory linked into each release.

```sh
# in the new release directory
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build
ln -s /var/www/wasmsoft/shared/.env .env
rm -rf storage && ln -s /var/www/wasmsoft/shared/storage storage
php artisan storage:link

# take a database backup (backup-and-restore.md), then:
php artisan migrate --force
php artisan optimize

ln -sfn /var/www/wasmsoft/releases/<release> /var/www/wasmsoft/current
sudo systemctl reload php8.4-fpm
php artisan queue:restart
php artisan inertia:stop-ssr     # only with server-side rendering: supervisor restarts it with the new bundle
```

When a release contains migrations that change existing tables, put the site in maintenance mode around the migration: `php artisan down --render="errors::503"` and afterwards `php artisan up`. The maintenance page needs neither the database nor the session.

## 8. Health checks and monitoring

- `GET /up` answers 200 when the application, its database and its cache store respond, and 500 otherwise (the cause is written to the log, never to the response). Point the load balancer or uptime monitor at it.
- Logs: `storage/logs` (`daily` channel keeps 14 days by default, `LOG_DAILY_DAYS`). Watch for `error` entries; forwarding them to an error-tracking or log service is recommended but not set up.
- Failed queue jobs: `php artisan queue:failed`.
- The dashboard's audit log (administrators) records sign-ins, failed sign-ins, lockouts and every change to content, media, menus, settings and users.

## 9. Go-live checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` with `https://`, `APP_KEY` stored safely.
- [ ] HTTPS certificate valid; HTTP redirects to HTTPS.
- [ ] Compression enabled (check a response for `Content-Encoding: gzip` or `br`).
- [ ] `composer install --no-dev`, `npm run build`, `php artisan optimize` done.
- [ ] If server-side rendering is on: the rendering service runs, is restarted after each deployment, and the HTML of a page (for example `curl https://example.com/en`) contains its main heading.
- [ ] Migrations ran; `php artisan storage:link` done (or `MEDIA_DISK=s3` configured).
- [ ] Cron entry for the scheduler and a running queue worker.
- [ ] Mail works: send yourself a password reset from `/admin/forgot-password`.
- [ ] Contact form works and the alert arrives at the address set in Settings.
- [ ] `/up` returns 200 and is monitored.
- [ ] `/robots.txt` allows crawling and lists the sitemap; `/sitemap.xml` lists the real pages.
- [ ] Database and media backups scheduled, and one restore tested ([backup-and-restore.md](backup-and-restore.md)).
- [ ] First administrator created; demo accounts and sample content do not exist.
- [ ] Settings, pages and content filled in with real, approved information in every enabled language.
- [ ] `composer audit` and `npm audit` report nothing.
- [ ] The test suite passed on the production database engine (section 1).

## 10. External services

| Service | Used for | Required |
|---|---|---|
| Mail provider (SMTP or another Laravel mailer) | Password resets, invitations, new-inquiry alerts | Yes |
| Have I Been Pwned "Pwned Passwords" (`api.pwnedpasswords.com`) | Rejecting passwords known from data breaches, in production only. Only the first five characters of the password's SHA-1 hash are sent. If the service cannot be reached, the error is logged and the password is accepted. | Recommended |
| S3-compatible storage | Media files when `MEDIA_DISK=s3` | Optional |
| Redis | Cache, sessions or queue instead of the database | Optional |
| CDN or caching proxy | Public pages are cacheable (`s-maxage`, ETag) and set no cookies | Optional |

The site loads no third-party scripts, fonts, analytics or embeds.
