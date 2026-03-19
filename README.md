# IslandBid - Base Foundation

Modular PHP (no framework) + PostgreSQL + AngularJS base for a bidding/fixed-price marketplace.

This foundation is intentionally clean and minimal so new modules can be plugged in without refactoring core pieces.

## Tech Stack

- Backend: PHP (no framework)
- Frontend: AngularJS 1.8.3 + HTML + custom CSS
- Database: PostgreSQL (existing schema, not recreated)

## Project Structure

```text
/public
  index.php
  api.php
  .htaccess
  /assets
  /uploads
/api
  /auth
  /items
  /bids
  /categories
  /dashboards
  /saved-items
  /documents
/src
  /bootstrap
    api_bootstrap.php
  /config
    Env.php
    env.php
    config.php
  /db
    Database.php
  /helpers
    JsonResponse.php
    Request.php
    SessionAuth.php
    Cors.php
    Upload.php
    Slug.php
    Validator.php
    Util.php
  /middleware
    AuthMiddleware.php
  /services
  /repositories
  /views
/frontend
  app.js
  /services
  /controllers
  /templates
  /styles
/.env.example
```

## Implemented Reusable Foundation

- Environment/config loader with `.env` support
- Reusable PostgreSQL PDO connection
- Reusable JSON response helper
- Reusable request parser helper
- Reusable session auth helper
- Reusable API bootstrap for API entrypoints
- Reusable CORS handling (including preflight)
- Reusable file upload helper (image-safe baseline)
- Reusable slug helper
- Reusable validation helper
- AngularJS app base structure
- Base HTML page and base CSS theme

## Existing Auth API

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`

## Setup Instructions

1. Copy `.env.example` to `.env`.
2. Update DB credentials in `.env` to match your existing PostgreSQL instance.
3. Configure web root to `/public`.
4. Ensure Apache rewrite is enabled (`mod_rewrite`) so `.htaccess` routing works.
5. Ensure PHP extensions are enabled:
   - `pdo`
   - `pdo_pgsql`
   - `fileinfo`
6. Start Apache/PHP and open your app URL.
7. Test API:
   - `POST /api/auth/register`
   - `POST /api/auth/login`
   - `GET /api/auth/me`

## Notes

- Database schema is **not** regenerated and not modified.
- Foundation follows existing DB constraints and table names.
- Passwords use `password_hash` and `password_verify`.
- All DB queries use PDO prepared statements.
