# IslandBid – PHP + PostgreSQL + AngularJS Bidding Platform

## Overview

IslandBid is a **bidding / fixed-price marketplace** built with:

- **PHP (no framework)** for a modular REST-style JSON API and SEO server-rendered pages.
- **PostgreSQL** for all data storage, with a pre-existing schema (users, items, bids, saved items, documents).
- **AngularJS 1.8.3** for a modern, responsive single-page feel on top of server-rendered HTML.

Core features:

- User **registration** and **login** with session-based authentication.
- Public **landing page** of listings with filters and pagination.
- SEO-friendly **item details** page (server-rendered) enhanced by AngularJS for bids and saved items.
- **Create / edit item** workflow with **dynamic category fields**.
- Multiple **image uploads** per item via document tables.
- **Place / update / remove bid** on items.
- **Save / unsave** items (wish list).
- **Seller dashboard** (manage own listings).
- **Buyer dashboard** (active, won, lost bids + saved items).

---

## Tech Stack

- **Backend**
  - PHP 8+ (no framework)
  - PDO with prepared statements
  - Session-based auth (`SessionAuth`, `AuthMiddleware`)
- **Database**
  - PostgreSQL (existing schema)
- **Frontend**
  - AngularJS 1.8.3
  - HTML5
  - Custom CSS (`public/frontend/styles/main.css`) – no external CSS frameworks
- **Web server**
  - Apache with `.htaccess` for clean URLs and API front controller

---

## Folder Structure

At the project root (`c:\Projects\IslandBid\Implementation`):

- `public/`
  - `.htaccess` – URL rewriting and routing:
    - `/` → `index.php`
    - `/api/*` → `api.php`
    - `/login`, `/register`, `/create-item`, `/seller-dashboard`, `/buyer-dashboard`, `/item/{slug}`
  - `api.php` – API front controller and router
  - `index.php` – public landing page (AngularJS `HomeController`)
  - `item.php` – SEO item details page (server-rendered, AngularJS `ItemController`)
  - `login.php` – login page (AngularJS `LoginController`)
  - `register.php` – registration page (AngularJS `RegisterController`)
  - `create-item.php` – create/edit listing page (AngularJS `CreateItemController`)
  - `seller-dashboard.php` – seller dashboard (AngularJS `SellerDashboardController`)
  - `buyer-dashboard.php` – buyer dashboard (AngularJS `BuyerDashboardController`)
  - `frontend/`
    - `app.js` – main AngularJS module (`islandBidApp`)
    - `styles/main.css` – all custom styling
    - `services/`
      - `api.service.js` – `$http` wrapper with 401 handling
      - `auth.service.js` – auth state, login/logout, `requireAuth`
      - `saved.service.js` – saved items (wishlist)
      - `category.service.js` – category + dynamic fields
      - `dashboard.service.js` – seller/buyer dashboard data
    - `controllers/`
      - `auth.controller.js` – global auth header
      - `home.controller.js` – landing page
      - `item.controller.js` – item details (bids, save)
      - `login.controller.js` – login form
      - `register.controller.js` – registration form
      - `create-item.controller.js` – create/edit listing, file uploads
      - `seller-dashboard.controller.js` – seller listings
      - `buyer-dashboard.controller.js` – buyer dashboard
    - `directives/file-input.directive.js` – file input + preview
- `api/`
  - `auth/`
    - `login.php`
    - `logout.php`
    - `register.php`
    - `me.php`
  - `items/`
    - `list.php` – landing page listings
    - `detail.php` – item detail JSON for `item.php`
    - `create.php` – create/update item
    - `edit-data.php` – prefill data for editing
    - `upload-image.php` – image uploads
  - `categories/`
    - `list.php` – active categories
    - `fields.php` – dynamic category fields + options
  - `bids/`
    - `place.php`
    - `update.php`
    - `remove.php`
    - `by-item.php`
  - `saved-items/`
    - `list.php`
    - `save.php`
    - `unsave.php`
  - `dashboards/`
    - `seller.php`
    - `buyer.php`
- `src/`
  - `bootstrap/api_bootstrap.php` – common API bootstrap (env, DB, helpers, repositories)
  - `config/`
    - `env.php` – environment loader
    - `config.php` – app + DB + session config
  - `db/`
    - `Database.php` – PDO connection
  - `helpers/`
    - `EnvironmentLoader.php`
    - `JsonResponse.php`
    - `Request.php`
    - `Util.php`
    - `SessionAuth.php`
    - `Cors.php`
    - `Upload.php`
    - `Slug.php`
    - `Validator.php`
  - `middleware/`
    - `AuthMiddleware.php`
  - `repositories/`
    - `UserRepository.php`
    - `ItemRepository.php`
    - `CategoryRepository.php`
    - `ParameterRepository.php`
    - `DocumentTypeRepository.php`
    - `DocumentRepository.php`
    - `BidRepository.php`
    - `SavedItemRepository.php`
    - `SellerDashboardRepository.php`
    - `BuyerDashboardRepository.php`
  - `services/`
    - `AuthService.php`
    - `ItemService.php`
    - `CategoryService.php`
    - `BidService.php`

---

## Prerequisites

- **PHP**: 8.0+ with:
  - `pdo_pgsql`
  - `curl`
  - `mbstring`
  - `json`
- **PostgreSQL**: 13+ (with the pre-existing IslandBid schema already created and seeded)
- **Web server**: Apache (or compatible) with `.htaccess` and `mod_rewrite` enabled
- **Composer**: *Not required* (no vendor deps), but fine if installed
- **Node/npm**: *Not required* (AngularJS loaded via CDN)

---

## Database (PostgreSQL) Configuration

The platform assumes an existing schema with at least:

- `app_user`
- `item`
- `bid`
- `saved_item`
- `document`
- `item_document`
- `category`, `category_field`, `category_field_option`
- `parameter`
- `document_type`

Key constraints (must match DB):

- `app_user.email` – unique.
- `item.seo_slug` – unique.
- `item.listing_type` – enum-like check: `'bid' | 'fixed_price' | 'both'`.
- `item.item_status` – `'draft' | 'active' | 'sold' | 'expired' | 'cancelled'`.
- `bid.bid_status` – includes `'active' | 'updated' | 'won' | 'lost'`.

Configure connection in `.env` (copied from `.env.example`) and `src/config/config.php`:

```ini
# .env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=islandbid
DB_USER=islandbid_user
DB_PASSWORD=your_password
APP_URL=http://islandbid.local
```

```php
// src/config/config.php (DB section)
'db' => [
    'driver' => 'pgsql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
    'database' => $_ENV['DB_NAME'] ?? 'islandbid',
    'username' => $_ENV['DB_USER'] ?? 'islandbid_user',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
],
```

The `Database::connection()` helper (used in `api_bootstrap.php`) reads this configuration and creates a shared PDO instance.

---

## PHP / App Configuration

Main configuration is in `src/config/config.php`:

- **App URL**

```php
'app' => [
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'uploads_physical' => null,  // can override DB parameter
    'uploads_folder'   => null,  // can override DB parameter
],
```

- **Session settings**

```php
'session' => [
    'name' => 'ISLANDBIDSESSID',
    'lifetime' => 60 * 60 * 24, // 1 day
],
```

`SessionAuth::start()` uses this to configure `session_name`, lifetime, cookies, etc.

---

## Pointing the App to the Existing Database

1. **Set up `.env`**

Copy `.env.example` → `.env` and set `DB_*` and `APP_URL` to match your environment.

2. **Verify `config.php`**

Ensure `src/config/config.php` uses `$_ENV` or equivalent env loader to populate DB settings.

3. **Verify `Database.php`**

`Database::connection()` uses `config.php` to instantiate PDO with DSN:

```php
$dsn = sprintf(
    'pgsql:host=%s;port=%d;dbname=%s',
    $config['db']['host'],
    $config['db']['port'],
    $config['db']['database']
);
```

4. **Confirm schema**

Confirm the DB you configured already contains the required tables and constraints. The PHP code does not perform migrations.

---

## Sessions & Authentication Notes

- Session management is handled by `src/helpers/SessionAuth.php`:
  - `SessionAuth::start()` – configures and starts PHP session.
  - `SessionAuth::login($user)` – stores a safe subset of user fields into `$_SESSION['user']`.
  - `SessionAuth::logout()` – clears and destroys the session.
  - `SessionAuth::user()` – returns logged-in user array or `null`.
  - `SessionAuth::check()` – boolean status.

- `AuthMiddleware::requireAuth()` (in `src/middleware/AuthMiddleware.php`) is used across API endpoints to enforce auth; on missing user, it returns a standard JSON 401:

```json
{
  "success": false,
  "message": "Authentication required."
}
```

- AngularJS `AuthService` (`public/frontend/services/auth.service.js`) mirrors this session state on the client:
  - Tracks `currentUser` from `window.__APP_BOOTSTRAP__.user` and `/api/auth/me`.
  - Exposes `isAuthenticated()`, `login()`, `logout()`, `refresh()`, `requireAuth()`.

- Header/navs in `index.php` and `item.php` use AngularJS `AuthController` / `AuthService` to conditionally show login/register vs dashboards and logout.

---

## Upload Directory & Document/Image Storage

Uploads are handled via:

- `Upload::moveImage()` (in `src/helpers/Upload.php`)
- `DocumentRepository.php`, `DocumentTypeRepository.php`, `ParameterRepository.php`

Configuration:

- **DB parameters** (table `parameter`):
  - `BASE_PHYSICAL_PATH` – base folder on disk for storing images.
  - `BASE_SERVER_URL` – base public URL for serving those images.

- **App overrides**:
  - In `config.php`, `app.uploads_physical` and `app.uploads_folder` can override physical path and URL-folder if desired.

Path logic in `api/items/upload-image.php`:

- Reads physical base from:
  1. `config['app']['uploads_physical']`, or
  2. `UPLOAD_PHYSICAL_BASE` env, or
  3. `parameter.paramater_value` for `BASE_PHYSICAL_PATH`.
- Builds `server_path` for the `document.server_file_path` using:
  1. `config['app']['uploads_folder']`, or
  2. `UPLOAD_SERVER_FOLDER` env, or
  3. default `/uploads`.

Images are linked to items through the `document` and `item_document` tables. Repositories and listing queries (item list, detail, dashboards) join these tables and the `parameter` table to construct a full image URL, which is then consumed by AngularJS.

**Setup steps:**

1. Create the physical directory (if using a folder like `/var/www/islandbid/uploads`).
2. Ensure PHP has write permissions to this directory.
3. Populate `parameter` table with `BASE_PHYSICAL_PATH` and `BASE_SERVER_URL`, or configure equivalent overrides in `.env` / `config.php`.

---

## Running Locally

1. **Clone the project** into your web root, e.g.:

   ```bash
   c:\Projects\IslandBid\Implementation
   ```

2. **Configure virtual host** (Apache example):

   ```apache
   <VirtualHost *:80>
       ServerName islandbid.local
       DocumentRoot "C:/Projects/IslandBid/Implementation/public"

       <Directory "C:/Projects/IslandBid/Implementation/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. **Enable `mod_rewrite`** and restart Apache.

4. **Create `.env`** from `.env.example` and configure DB & APP_URL.

5. **Make sure uploads folder is writable** according to your parameter / config.

6. **Navigate to** `http://islandbid.local/` in your browser.

The AngularJS app is loaded from CDN; no frontend build step is required.

---

## Public Routes

These routes are public (no login required):

- `/` – Landing page (search, filters, cards).
- `/item/{seo_slug}` – SEO-friendly item page (server-rendered; AngularJS adds bids, saving, etc.).
- `/login` – Login page.
- `/register` – Registration page.

Non-logged users can:

- Browse lists and details.
- See bid history and current highest bid.
- Cannot place, update, or remove bids; the bid box prompts login/registration instead.

---

## Authenticated-Only Routes

These views and actions require login:

- `/create-item` – create/edit listing.
- `/seller-dashboard` – manage own listings.
- `/buyer-dashboard` – view active / won / lost bids and saved items.

And APIs (via `AuthMiddleware::requireAuth()`):

- `POST /api/items` – create/update item.
- `POST /api/items/upload-image` – upload item images.
- `POST /api/bids/place` – place bid.
- `POST /api/bids/update` – update bid.
- `POST /api/bids/remove` – remove bid.
- `GET /api/dashboards/seller` – seller dashboard data.
- `GET /api/dashboards/buyer` – buyer dashboard data.
- `GET /api/saved-items` – list saved item IDs.
- `POST /api/saved-items/save` – save item.
- `POST /api/saved-items/unsave` – unsave item.

AngularJS uses:

- `AuthService.requireAuth()` in controllers (`CreateItemController`, seller and buyer dashboards) to guard views.
- `ApiService` wrapper to intercept 401 responses and redirect to `/login`.

---

## API Routes Summary

Auth:

- `POST /api/auth/register` – create account.
- `POST /api/auth/login` – login (returns user).
- `POST /api/auth/logout` – logout (clears session).
- `GET /api/auth/me` – get current session user (if any).

Items:

- `GET /api/items` – list items for landing page (filters, pagination).
- `GET /api/items/detail?slug={seo_slug}` – detail JSON for `item.php`.
- `GET /api/items/edit-data?id_item={uuid}` – full item data for editing (fields, images, dynamic field values).
- `POST /api/items` – create or update item (depending on presence of `id_item` in payload).
- `POST /api/items/upload-image` – upload image for an item.

Categories:

- `GET /api/categories` – list active categories.
- `GET /api/categories/fields?id_category={uuid}` – list active dynamic fields + options for a category.

Bids:

- `POST /api/bids/place` – place a new bid.
- `POST /api/bids/update` – update existing active bid for the user/item.
- `POST /api/bids/remove` – remove (cancel) the user’s active bid on an item.
- `GET /api/bids/by-item?id_item={uuid}` – list bids for an item.

Saved items:

- `GET /api/saved-items` – list saved item IDs for the current user.
- `POST /api/saved-items/save` – save an item.
- `POST /api/saved-items/unsave` – unsave an item.

Dashboards:

- `GET /api/dashboards/seller?status={all|active|sold|expired|draft}` – seller listings + segmentation.
- `GET /api/dashboards/buyer` – buyer summary:
  - `active_bids`
  - `won_items`
  - `lost_items`
  - `saved_items`

All JSON responses use the standard `JsonResponse` helper:

```json
{
  "success": true,
  "data": { ... }
}
```

or

```json
{
  "success": false,
  "message": "Human readable error",
  "errors": { ... }
}
```

---

## SEO & Server-Rendered Item Page

The **item details page** (`public/item.php`) is **server-rendered**:

- It calls `GET /api/items/detail?slug=...` via cURL.
- Renders `<title>`, `<meta name="description">`, canonical URL, schema.org `Product` markup, and item content directly in HTML for crawlers.
- Renders images, description, and dynamic fields on the server.

After the page is rendered, AngularJS (`ItemController`) enhances it:

- Binds the existing `item` data via `window.__APP_BOOTSTRAP__`.
- Loads and renders bids (`/api/bids/by-item`).
- Enables bid placement/update/removal for logged-in users.
- Handles save/unsave (wishlist) actions.

This combination keeps the page **SEO-friendly** (fully rendered HTML) while still providing a rich, interactive bidding UI.

---

## Dynamic Category Fields

Category-specific fields are fully dynamic and controlled by the database:

- `category` – base category record.
- `category_field` – dynamic fields per category (label, type, required, etc).
- `category_field_option` – options for select-type fields.

Backend:

- `CategoryRepository` + `CategoryService` provide:
  - `GET /api/categories` – list categories.
  - `GET /api/categories/fields?id_category=...` – return fields and options.

Frontend:

- `CreateItemController`:
  - On category change, calls `CategoryService.getFields(id_category)`.
  - Builds dynamic form controls (text, number, date, select, boolean).
  - On edit, pre-fills values from `GET /api/items/edit-data`.

Values are stored in an `item_field_value`-type table (behind `ItemRepository`), and mapped according to field type (text, number, boolean, date, select).

---

## Document and Image Storage

Images are not stored directly on the item record. Instead:

- `document` – stores file metadata and paths (`server_file_path`, physical path info, etc).
- `item_document` – links a `document` to an `item`, with `display_order` and `is_primary`.

Upload flow:

1. `CreateItemController` posts a listing via `POST /api/items`.
2. Once an item exists, the image upload section becomes available.
3. The user selects a file, optional order, and “primary” flag.
4. AngularJS posts to `POST /api/items/upload-image` (multipart/form-data).
5. Backend:
   - Moves the file using `Upload::moveImage()`.
   - Creates a `document` row via `DocumentRepository::createImageDocument()`.
   - Links it to the item via `DocumentRepository::linkToItem()`.
6. API responds with full metadata, including a computed `full_server_url` for the image.

Listing and detail endpoints join `item_document`, `document`, and `parameter` to return the **first main image** as `server_file_path`.

---

## Future Enhancements

Some suggested next steps:

- **Payments & checkout**
  - Integrate a payment provider for checkout and seller payouts.
  - Add order and invoice tables and flows.

- **Messaging & notifications**
  - In-app messaging between buyers and sellers.
  - Email or push notifications for:
    - Outbid events
    - Auction won
    - Auction nearing end

- **Advanced search & filters**
  - Faceted search (by location, price range, category-specific attributes).
  - Saved searches and alerts.

- **Improved moderation & trust**
  - Seller verification / KYC.
  - Item reporting and admin dashboard.

- **Performance & scalability**
  - Add caching for public listing pages.
  - Queue-based processing for heavy tasks (image processing, notifications).

- **Mobile optimizations**
  - Deeper responsive breakpoints.
  - Possible future SPA or PWA rewrite using a modern framework, reusing the same API layer.

---

## License

Internal / proprietary (adjust as appropriate for your deployment).
