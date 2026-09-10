# Ominimo Claims Experience Hub

A Laravel community platform where customers can share experiences, ask questions, and discuss the stages of an insurance claim. The application is designed as a realistic extension of a traditional blog assignment while avoiding the storage of real claim references or sensitive insurance data.

## Project status

Preparation and Phases 2 through 5 are complete. The application includes Laravel session authentication, account management, role boundaries, representative seed data, the complete post and comment lifecycles, database-backed search and filters, preserved pagination, and strict relationship loading.

## Planned functionality

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

The detailed delivery instructions will be finalized as dependencies are added. The current workflow is:

1. Run `composer install`.
2. Copy `.env.example` to `.env` and run `php artisan key:generate`.
3. Start MySQL and Redis with `docker compose up -d`.
4. Run `php artisan migrate --seed`.
5. Run `npm install` and `npm run build`.
6. Start Laravel with `php artisan serve`.

The local seeder creates these demonstration accounts:

| Role | Email | Local password |
| --- | --- | --- |
| User | `kronos.p@example.com` | `!Kronos.p1` |
| Moderator | `themis.r@example.com` | `!Themis.t2` |
| Administrator | `atlas.m@example.com` | `!Atlas.a3` |

These credentials are only for seeded local development data and must be replaced outside local development.

Do not commit `.env`, database data, uploaded files, credentials, or API keys.

## Documentation

The implementation phases, architecture decisions, acceptance criteria, and quality standards are recorded in [docs/PROJECT_PLAN.md](docs/PROJECT_PLAN.md).

## Testing and quality

Every feature must include appropriate automated tests. Before a feature is merged, the project must pass:

```powershell
php artisan test
vendor\bin\pint --test
npm run build
```

## License

This project is being developed as a technical assignment.
