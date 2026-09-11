# Project Plan

## Product purpose

The Ominimo Claims Experience Hub is a community and information platform. Customers can publish experiences or questions about the insurance claim journey and discuss them through comments. It is not a claim submission or payment-processing system and must not store real claim numbers, financial details, or other sensitive insurance information.

## Roles and permissions

| Capability | Guest | User | Moderator | Administrator |
| --- | --- | --- | --- | --- |
| Browse published posts | Yes | Yes | Yes | Yes |
| Read approved comments | Yes | Yes | Yes | Yes |
| Create posts | No | Yes | Yes | Yes |
| Create comments | Yes | Yes | Yes | Yes |
| Manage own posts and comments | No | Yes | Yes | Yes |
| Moderate all content | No | No | Yes | Yes |
| Manage user roles | No | No | No | Yes |

All protected actions must be enforced on the server through policies or middleware. Hiding a button is not authorization.

## Domain model

### User

- Has many posts
- Has many comments
- Has one role: user, moderator, or administrator

### Post

- Belongs to a user
- Has many comments
- Has a unique slug
- Has a claim stage
- Has a draft or published status
- May have an original image and processed image

### Comment

- Belongs to a user when submitted by an authenticated user
- Uses a nullable user relationship for assignment-required guest comments
- Belongs to a post
- Has an active or hidden moderation status

Planned claim stages are reporting, assessment, review, decision, payment, and closed.

## Query and performance rules

- Define every Eloquent relationship explicitly on its model.
- Eager-load relationships required by a page.
- Use `withCount` when a page displays relationship totals.
- Use `whereHas` only for meaningful relationship filters.
- Paginate using the database query, not an in-memory collection.
- Preserve active search and filter values between pages.
- Add indexes for common ownership, status, publication-date, and relationship queries.
- Enable lazy-loading detection during development and testing.

## Validation and security rules

- Validate every external input through a dedicated Form Request.
- Authorize every create, update, delete, publish, moderation, and role-management action.
- Use Laravel CSRF protection for forms.
- Escape user content by default in Blade.
- Validate image type, size, and dimensions.
- Generate server-controlled image names.
- Never accept or display real claim numbers or sensitive personal information.
- Rate-limit guest comments without collecting unnecessary personal information.
- Never commit `.env`, credentials, local database data, uploaded files, or logs.

## Image and queue workflow

1. A Form Request validates the incoming image.
2. Laravel stores the original image safely.
3. The post record is committed to the database.
4. An image-processing job is dispatched after the transaction commits.
5. Redis stores the queued job.
6. Laravel Horizon runs and monitors the queue.
7. The job creates the required optimized image or thumbnail.
8. Failed jobs use controlled retries and remain safe to run again.

The HTTP upload itself is not deferred. Expensive processing is performed asynchronously.

## Implementation phases

### Phase 1: Preparation

- [x] Confirm the initial Laravel installation and baseline tests
- [x] Confirm the local Git repository and ignored sensitive files
- [x] Define the product story, roles, domain model, and quality rules
- [x] Prepare MySQL and Redis Docker services
- [x] Prepare the shared environment template
- [x] Replace the framework README with project documentation
- [x] Start Docker Desktop and verify the MySQL and Redis containers
- [x] Switch the local `.env` from SQLite to MySQL and Redis
- [x] Run the existing migrations against MySQL
- [ ] Create a private GitLab remote when the owner is ready

### Phase 2: Authentication and roles

- [x] Implement Laravel's built-in session authentication with Blade
- [x] Implement profile, password, and account management
- [x] Add user roles and seed representative accounts
- [x] Register role middleware
- [x] Test authentication and role boundaries

### Phase 3: Posts

- [x] Add the post migration, model, factory, and seeder
- [x] Define user and post relationships
- [x] Implement resource routes and controllers
- [x] Add Store and Update Form Requests
- [x] Add PostPolicy ownership and moderation rules
- [x] Implement draft and published states
- [x] Test the complete post lifecycle

### Phase 4: Comments

- [x] Add the comment migration, model, factory, and seeder
- [x] Define authenticated and guest comment relationships
- [x] Implement nested comment actions
- [x] Add Form Requests and CommentPolicy
- [x] Add moderation behaviour
- [x] Test the complete comment lifecycle

### Phase 5: Search and performance

- [x] Add claim-stage, status, author, and discussion filters
- [x] Add text search
- [x] Use eager loading, `withCount`, and appropriate `whereHas` filters
- [x] Add database pagination with preserved query parameters
- [x] Add database indexes and lazy-loading detection
- [x] Test filtering, pagination, and visibility

### Phase 6: Images, queues, and Horizon

- [x] Add validated image storage
- [x] Add idempotent queued image processing
- [x] Configure Redis as the queue connection
- [x] Install and configure Laravel Horizon
- [x] Schedule Horizon metrics snapshots
- [x] Define retry, timeout, failure, and cleanup behaviour
- [x] Test storage and queued jobs with Laravel fakes

### Phase 7: User interface

- Define reusable design tokens and Blade components
- Implement the Ominimo-inspired responsive layout
- Build accessible navigation, cards, forms, status messages, and empty states
- Add claim-stage and publication-status indicators
- Verify mobile and desktop layouts

### Phase 8: Administration

- Build the moderation dashboard
- Add post and comment moderation filters
- Add administrator-only role management
- Test every administrator and moderator boundary

### Phase 9: Test completion and refactoring

- Complete unit tests for isolated domain logic
- Complete feature tests for user workflows
- Remove duplication using Laravel features
- Run Laravel Pint and the frontend production build
- Confirm that a fresh database can migrate and seed successfully

### Phase 10: Delivery

- Complete installation and operating instructions
- Add an entity-relationship diagram and application screenshots
- Document architectural decisions and known limitations
- Verify that no secrets or local artifacts are tracked
- Run the complete quality suite
- Review the Git history and prepare the private GitLab repository for submission

## Git workflow

- Keep the remote repository private during development.
- Use `main` as the stable branch.
- Develop each coherent feature on a `feature/...` branch.
- Commit small, complete changes with descriptive messages.
- Run tests and formatting before merging.
- Never rewrite or remove history merely to conceal how work was produced.

Suggested feature branches:

- `feature/authentication`
- `feature/posts`
- `feature/comments`
- `feature/search-and-pagination`
- `feature/image-processing`
- `feature/admin-dashboard`
- `feature/frontend`

## Definition of done

A feature is complete only when:

- Its requirements and authorization rules are implemented.
- All external input is validated.
- Relevant automated tests pass.
- Required relationships are eager-loaded.
- The code follows Laravel conventions and is formatted.
- User-facing success, validation, authorization, and empty states are handled.
- Documentation is updated when setup or behaviour changes.

## Commenting standard

Comments and docblocks should explain non-obvious decisions, constraints, and side effects. They should not repeat what clearly named code already says.
