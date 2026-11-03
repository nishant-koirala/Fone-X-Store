# Configuration & Environment Matrix

This document audits every configuration file under `config/`, every environment variable defined in `.env.example`, and all `config('...')` and `env('...')` calls across the codebase to identify dead keys, missing variables, and architectural mismatches.

---

## 1. Direct `env()` Call Audit

- **Rule:** Direct calls to `env('...')` outside configuration files under `config/` return `null` when configuration is cached (`php artisan config:cache`), causing production bugs.
- **Audit Scan:** Executed regex search across `app/`, `routes/`, `resources/views/`, and `database/`.
- **Finding:** **ZERO direct `env()` calls exist outside of `config/` files.** The repository adheres strictly to Laravel configuration caching best practices.

---

## 2. Shop Configuration Matrix (`config/shop.php`)

| Config Key | Environment Variable | Default in Config | Present in `.env.example` | Defined Where | Used Where in Code | Status |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: |
| `shop.currency` | None | `'NPR'` | NO | `config/shop.php:9` | **Nowhere** | **DEAD KEY (A-016)** |
| `shop.currency_symbol` | None | `'Rs '` | NO | `config/shop.php:10` | **Nowhere** | **DEAD KEY (A-016)** |
| `shop.accessory_category_slugs` | None | `['chargers-cables', ...]` | NO | `config/shop.php:20` | `CartService.php:102`, `ProductController.php:34,44` | **ACTIVE** |
| `shop.delivery.free_threshold` | `SHOP_FREE_DELIVERY_THRESHOLD` | `0` | YES (commented) | `config/shop.php:41` | **Nowhere** | **DEAD KEY (A-016)** |
| `shop.delivery.inside_valley_fee` | `SHOP_INSIDE_VALLEY_FEE` | `0` | YES (commented) | `config/shop.php:42` | `CartService.php:115` | **ACTIVE** |
| `shop.delivery.outside_valley_fee` | `SHOP_OUTSIDE_VALLEY_FEE` | `200` | YES (commented) | `config/shop.php:43` | **Nowhere** | **DEAD / UNUSED (A-006, A-016)** |
| `shop.guarantees.check_guarantee_days` | `SHOP_CHECK_GUARANTEE_DAYS` | `7` | YES (commented) | `config/shop.php:58` | `resources/views/layouts/storefront.blade.php:84` | **ACTIVE** |
| `shop.guarantees.warranty_new_days` | `SHOP_WARRANTY_NEW_DAYS` | `365` | YES (commented) | `config/shop.php:59` | **Nowhere** | **DEAD KEY (A-016)** |
| `shop.orders.pending_expiry_hours` | `SHOP_PENDING_EXPIRY_HOURS` | `48` | YES (commented) | `config/shop.php:72` | `app/Console/Commands/ExpireUnconfirmedOrders.php:21` | **ACTIVE** |

---

## 3. Core & Mail Configuration Matrix

| Config Key | Environment Variable | Default in Config | Present in `.env.example` | Defined Where | Used Where in Code | Status |
| :--- | :--- | :--- | :---: | :--- | :--- | :---: |
| `app.name` | `APP_NAME` | `'Laravel'` | YES | `config/app.php:14` | Views, mail headers, layouts | **ACTIVE** |
| `app.env` | `APP_ENV` | `'production'` | YES | `config/app.php:28` | Kernel, Seeders (`DatabaseSeeder.php:25`) | **ACTIVE** |
| `app.debug` | `APP_DEBUG` | `false` | YES | `config/app.php:41` | Exception handlers, Blade error pages | **ACTIVE** |
| `app.url` | `APP_URL` | `'http://localhost'` | YES | `config/app.php:55` | Routing, asset generation | **ACTIVE** |
| `mail.admin_address` | `ADMIN_EMAIL` | `null` | YES | `config/mail.php:118` | `TradeInController.php:102` | **ACTIVE** |
| `mail.default` | `MAIL_MAILER` | `'log'` | YES | `config/mail.php:18` | Mail transport bootstrap | **ACTIVE** |
| `queue.default` | `QUEUE_CONNECTION` | `'database'` | YES | `config/queue.php:16` | Queue worker driver | **ACTIVE** |

---

## 4. Discrepancy & Gotcha Analysis

1. **Dead Delivery Configuration (`SHOP_OUTSIDE_VALLEY_FEE`):**
   - In `config/shop.php`, `shop.delivery.outside_valley_fee` is set to `env('SHOP_OUTSIDE_VALLEY_FEE', 200)`.
   - In `CheckoutRequest.php`, the customer can select `outside_valley` as their delivery zone.
   - However, neither `CartService.php` nor `PlaceOrder.php` references `shop.delivery.outside_valley_fee`. Courier deliveries outside Kathmandu Valley are billed at Rs 0 delivery fee, causing shop revenue loss. (**Finding A-006, A-016**).

2. **Dead Warranty Configuration (`SHOP_WARRANTY_NEW_DAYS`):**
   - `shop.guarantees.warranty_new_days` is configured as 365 days.
   - None of the storefront product detail views or guarantee badges reference this key; product copy hardcodes warranty text or omits it.

3. **Admin Email Fallback Safety:**
   - In `TradeInController.php`, if `config('mail.admin_address')` is `null`, it logs a warning instead of falling back to a placeholder email (e.g. `admin@fonex.com`). This protects customer trade-in details from leaking to unconfigured destinations.
