# FoneX Store

[![CI](https://github.com/nishant-koirala/Fone-X-Store/actions/workflows/ci.yml/badge.svg)](https://github.com/nishant-koirala/Fone-X-Store/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-v5.7-FFAA00?logo=filament&logoColor=white)](https://filamentphp.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**FoneX Store** is an e-commerce platform and trade-in management portal tailored for a smartphone retailer in Nepal. The application supports buying brand-new devices, certified pre-owned phones across condition grades (New, A, B, C), and requesting instant trade-in valuations.

---

## Key Features

- **Strict NPR Currency:** Tailored specifically for the Nepali market, standardizing all storefront and admin pricing on Nepalese Rupees (`NPR` / `Rs `).
- **Session-Based Cart & COD Checkout:** Frictionless customer checkout with Cash on Delivery (COD) and customer lookup/deduplication by Nepali phone numbers.
- **Graded Device Conditions & Stock Audit:** Transactional stock movements (`recordStockMovement`) ensure inventory integrity, prevent overselling, and reject negative stock states with an immutable audit log.
- **Trade-In Valuation Portal:** Interactive customer trade-in submission with condition assessments, automated email dispatch, and Filament admin valuation review & pricing.
- **Filament Admin Panel:** Back-office dashboard for managing products, categories, stock adjustments, orders, customer records, and low-inventory alerts.
- **Catalog Capped Under 150 Products:** Optimized server-side pagination, sorting, search, and category filters built for fast navigation.

---

## Quality Assurance & Automated Testing

The codebase features a system-wide automated test suite (142 tests, 1,029 assertions) executing on SQLite in-memory without side effects on production data.

### Running Quality Gates Locally

| Command | Description | Tool |
| :--- | :--- | :--- |
| `composer run test` | Run complete PHPUnit automated test suite | PHPUnit 12 |
| `composer run test:coverage` | Run test suite with 70% minimum code coverage threshold | PHPUnit + PCOV |
| `composer run lint` | Verify code style and formatting standards | Laravel Pint (`--test`) |
| `composer run lint:fix` | Automatically format and fix code style issues | Laravel Pint |
| `composer run analyse` | Run static type analysis at **Level 5** | Larastan / PHPStan |
| `composer run ci` | Run full local pre-push pipeline (`lint` &rarr; `analyse` &rarr; `test`) | Composite Script |

---

## Continuous Integration (CI) Pipeline

GitHub Actions workflows are defined in [`.github/workflows/`](.github/workflows/):

- **[`ci.yml`](.github/workflows/ci.yml):** Runs on every push and pull request to `main`:
  - **Job 1 (Lint):** Validates code styling via Laravel Pint.
  - **Job 2 (Static Analysis):** Executes Larastan at Level 5 across `app/`, `config/`, `database/`, and `routes/`.
  - **Job 3 (Test Matrix):** Builds frontend assets with Vite and executes the full PHPUnit test suite against PHP 8.3 and PHP 8.4 on Ubuntu runners with PCOV code coverage reporting.
- **[`nightly.yml`](.github/workflows/nightly.yml):** Nightly automated security audit (`composer audit`) and regression test suite run against latest upstream packages.

---

## Local Development Setup

```bash
# 1. Clone repository
git clone https://github.com/nishant-koirala/Fone-X-Store.git
cd Fone-X-Store

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Migrate and seed catalog
php artisan migrate --seed

# 5. Build assets & serve
npm run build
php artisan serve
```

---

## Production Deployment & Optimization

When deploying FoneX Store to production, execute the following commands to warm caches, optimize routing, and maximize performance:

```bash
# 1. Optimize framework configuration, route registrations, and compiled blade views
php artisan optimize

# 2. Optimize Filament panel resources, pages, and components
php artisan filament:optimize

# 3. Cache Blade Icons for zero runtime disk IO
php artisan icons:cache

# 4. Migrate product images to Spatie Media Library with pre-generated thumbnails
php artisan products:convert-images

# 5. Production Environment Safety
# Ensure debug mode is strictly disabled in your production .env file:
APP_DEBUG=false
```

---

## Security & Architecture Notes

- **CSRF Protection:** Strictly enforced on all order checkout, cart modification, and trade-in submission endpoints.
- **IDOR Protection:** Customer order confirmations and trade-in receipts verify the requesting session token to prevent unauthorized access.
- **Filament User Gate:** Admin access requires an explicit administrator account (`is_admin = true`) via `FilamentUser::canAccessPanel()`. In production, promote administrators via `php artisan user:make-admin {email}`.
- **Negative Inventory Guard:** Stock decrement operations automatically validate that remaining stock cannot fall below zero, throwing `InvalidArgumentException` before database writes.
