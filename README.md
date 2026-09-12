# Ominimo Claims Experience Hub

A Laravel community platform where customers can share experiences, ask questions, and discuss the stages of an insurance claim. The application is designed as a realistic extension of a traditional blog assignment while avoiding the storage of real claim references or sensitive insurance data.

## Project status

All ten implementation and delivery phases are complete. The application includes Laravel session authentication, account management, role boundaries, representative seed data, complete post and comment lifecycles, database-backed search and filters, preserved pagination, strict relationship loading, validated image uploads, asynchronous image optimization, Redis queues monitored by Laravel Horizon, an accessible Ominimo-inspired responsive Blade interface, and a role-protected administration workspace for content moderation and user-role management. The domain and user workflows are covered by unit and feature tests.

## Implemented functionality

- User registration, login, logout, and profile management
- User, moderator, and administrator roles
- Post creation, editing, publishing, viewing, and deletion
- Claim-stage classification and post filtering
- Guest and authenticated comment creation, ownership-based deletion, and moderation
- Ownership and role authorization through Laravel policies and middleware
- Database-level search and pagination
- Eager loading and relationship counts to prevent N+1 queries
- Validated image uploads with queued image processing
- Redis queues monitored through Laravel Horizon
- Responsive Blade interface inspired by Ominimo's visual identity
- Moderator dashboard with post and comment search, filters, and status controls
- Administrator-only user-role management
- Feature and unit test coverage

## Technology

- PHP 8.4
- Laravel 13
- Laravel's built-in session authentication with Blade
- Tailwind CSS
- MySQL 8.4
- Redis 7
- Laravel Horizon
- Docker Compose
- PHPUnit

## Local prerequisites

- Laravel Herd or an equivalent PHP and Composer environment
- Node.js and npm
- Git
- Docker Desktop with Docker Compose

## Initial local setup

Laravel Horizon requires the `pcntl` and `posix` PHP extensions available in Linux. On native Windows, install the application dependencies with `composer install --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix`; the provided Docker worker supplies those extensions. Linux and WSL environments can use `composer install` normally.

1. Install the Composer dependencies using the command appropriate for the operating system.
2. Copy `.env.example` to `.env` and run `php artisan key:generate`.
3. Start MySQL and Redis with `docker compose up -d`.
4. Run `php artisan migrate --seed`.
5. Expose processed images with `php artisan storage:link`.
6. Run `npm ci` and `npm run build`.
7. Start Laravel with `php artisan serve`.
8. Build and start Horizon with `docker compose --profile worker up -d --build horizon`.

Keep the Horizon worker running whenever queued images should be processed. Its local dashboard is available at `/horizon`; outside the local environment it is restricted to administrators. Rebuild the worker after changing PHP dependencies or queued image code because its application source is copied into the image.

## Image and queue behaviour

- JPG, PNG, and WebP uploads are validated for content, an 8 MB maximum size, and maximum dimensions of 8000 by 8000 pixels.
- Server-generated names are used. Originals remain on private storage; public derivatives are auto-oriented, scaled down to at most 1600 pixels wide without upscaling, and encoded as WebP at quality 82.
- Image jobs are dispatched to the Redis `images` queue only after the post transaction commits. They are unique per image version, overlap-safe, idempotent, and use five attempts with 5, 15, and 30 second backoffs and a 60 second timeout.
- Replacing or removing an image, deleting a post, and deleting an account clean up the corresponding files. Stale jobs cannot overwrite a newer image.
- Horizon processes both the `images` and `default` queues. Its metrics snapshot runs every five minutes through Laravel's scheduler, so production must also run `php artisan schedule:work` or invoke `php artisan schedule:run` every minute.

## Demo accounts

The local seeder creates these demonstration accounts:

| Role | Email | Local password |
| --- | --- | --- |
| User | `kronos.p@example.com` | `!Kronos.p1` |
| Moderator | `themis.r@example.com` | `!Themis.t2` |
| Administrator | `atlas.m@example.com` | `!Atlas.a3` |

These credentials are only for seeded local development data and must be replaced outside local development.

Do not commit `.env`, database data, uploaded files, real credentials, or API keys.

## Documentation

The [project plan](docs/PROJECT_PLAN.md) records the implementation phases and acceptance criteria. See [architecture and limitations](docs/ARCHITECTURE.md), [installation and operations](docs/OPERATIONS.md), and [application screenshots](docs/SCREENSHOTS.md) for the delivery handoff.

## Testing and quality

Every feature must include appropriate automated tests. Before a feature is merged, the project must pass:

```powershell
php artisan test
vendor\bin\pint --test
npm run build
composer validate --strict --no-check-publish
docker compose --profile worker config --quiet
```

## License

This project is being developed as a technical assignment.
