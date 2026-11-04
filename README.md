# Forward Edge

Laravel consulting website and administrator portal for services, events, bootcamps, articles, gallery content, and direct contact details. Visitors do not create accounts. Bootcamps are events with guest registration.

See [FINAL_ARCHITECTURE.md](FINAL_ARCHITECTURE.md) for the active feature scope and historical-data policy.

## Local setup

Use PHP 8.2–8.4 for the current locked dependencies, Composer, and Node.js for optional Vite assets.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Configure the database, mail transport, and application URL in `.env`. Set a long random `ADMIN_SECRET_SETUP_KEY` and your chosen `ADMIN_SECRET_SETUP_PATH`, then use that path to provision the administrator. Administrator login is at `/ctrl-panel-v2/login`.

`composer dev` runs the local web server and Vite only. Background queue processing is optional and disabled in the scheduler by default. See [hosting guidance](docs/HOSTING.md) for a single bounded cron worker, process diagnostics, and deployment settings.

## Checks

```sh
php artisan test
php artisan route:list --except-vendor
```

The committed lock file predates PHP 8.5 support in some dependencies. PHP 8.5 can emit vendor deprecation warnings; upgrading the framework and its dependency set is separate from the feature removal.

## Deployment

Install the updated dependencies, run `php artisan migrate --force`, and clear/rebuild Laravel caches for the target environment. The new migration adds bootcamp support to event types. It does not delete historical accounts, transactions, course tables, or files.

Public account, academy, scholarship, and commerce routes are intentionally unavailable. New website pages attach to events or stand alone. Image management for the consulting website remains available.

## Consultancy page

Visit `/consultancy`. Discovery-call buttons open an email request until a real calendar URL is set in `CONSULTANCY_BOOKING_URL`. The consultant video and portrait can be added once approved files are available. No demonstration calendar or fake confirmations are displayed.
