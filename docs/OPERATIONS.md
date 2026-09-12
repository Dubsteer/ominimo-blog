# Installation and operations

## Prerequisites

- PHP 8.4 with Composer and the extensions required by Laravel, MySQL, Redis, GD, and image processing
- Node.js and npm
- Docker Desktop/Compose for the supplied MySQL, Redis, and Horizon services

Horizon needs Linux's `pcntl` and `posix` extensions. On native Windows, install Composer dependencies with:

```powershell
composer install --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix
```

On Linux or WSL, use `composer install`. The supplied Docker worker provides the Linux extensions even when the host PHP does not.

## First run

From the repository root:

```powershell
Copy-Item .env.example .env
php artisan key:generate
docker compose up -d mysql redis
php artisan migrate --seed
php artisan storage:link
npm ci
npm run build
php artisan serve
```

Use the equivalent copy command on Linux/WSL. Review the local-only database and Redis values in `.env` before starting Compose; change forwarded ports if 3306 or 6379 are occupied. If Windows blocks `storage:link`, enable Developer Mode or create the link from an elevated shell. The application defaults to `http://localhost:8000`.

In another terminal, build and start the image worker:

```powershell
docker compose --profile worker up -d --build horizon
```

Keep the worker running for image derivatives. Rebuild it after changing PHP dependencies, queue code, or bundled application source. To collect Horizon metrics snapshots, also run `php artisan schedule:work` on the host, or invoke `php artisan schedule:run` once per minute in a deployed scheduler.

The seeded example accounts and local-only passwords are listed in the [README](../README.md#demo-accounts). Do not run `migrate --seed` against a public database without replacing the demonstration account strategy. Never commit `.env`, uploaded files, local database files, logs, or real credentials.

## Daily use

- Guests can browse published stories and add comments to them.
- Members can create and manage their posts, update their account, and manage their comments.
- Moderators use `/admin` to filter and change post/comment status.
- Administrators also use `/admin/users` to manage roles. `/horizon` is available locally; outside local development it is administrator-only.
- The post editor accepts JPG, PNG, and WebP uploads up to 8 MB and 8000 × 8000 pixels. If a derivative does not appear, check the worker and queue before re-uploading.

Useful checks:

```powershell
docker compose ps
php artisan migrate:status
php artisan horizon:status
php artisan queue:failed
```

For a stopped local environment, use `docker compose --profile worker stop` and stop the Laravel/scheduler terminal processes. Do not use `docker compose down -v` unless you intend to remove local MySQL and Redis data.

## Verification

```powershell
php artisan test
vendor\bin\pint --test
npm run build
composer validate --strict --no-check-publish
docker compose --profile worker config --quiet
```

Tests use in-memory SQLite, so they do not modify the configured MySQL database. A deployment should also smoke-test registration/login, public post search, admin role boundaries, an image upload with Horizon running, and the public storage link.

For design and data decisions, see [Architecture and limitations](ARCHITECTURE.md). For the captured interface, see [Screenshots](SCREENSHOTS.md).
