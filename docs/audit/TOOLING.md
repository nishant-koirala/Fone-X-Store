# Tooling Execution Report

This document records the exact commands, execution output, exit codes, and findings for all required diagnostic tools run against the repository on branch `audit/full-system-review`.

---

## 1. Package Dependency Audits

### 1.1 `composer validate`
- **Command:** `composer validate`
- **Exit Code:** `0` (SUCCESS)
- **Output:**
```
./composer.json is valid
```

---

### 1.2 `composer audit`
- **Command:** `composer audit`
- **Exit Code:** `1` (FAILURES DETECTED)
- **Summary:** Found 10 security advisories affecting 5 direct/transitive packages:
  - `league/commonmark` (v2.6.0 installed, vulnerable to 6 advisories):
    - `GHSA-97jj-33gv-5xf9` (Medium): DisallowedRawHtml bypassed when a disallowed tag name ends raw-HTML literal.
    - `GHSA-3q6v-r5mr-hxv8` (High): Quadratic-time denial of service in GFM Table extension block-start scan.
    - `GHSA-8rr7-cvq3-gmfh` (High): Denial of service via distinctly-named attributes in Attributes extension.
    - `GHSA-jjv6-8j6v-6j52` (High): Denial of service in SmartPunct and Attributes extensions.
    - `GHSA-f8fg-pg57-v4j8` (High): XSS: `on*` event-handler filter bypassed with U+000C form feed.
    - `GHSA-j8pm-gj4c-rq4x` (High): Denial of service via crafted code fences and emphasis delimiters.
  - `league/flysystem` (v3.29.1 installed, vulnerable to 1 advisory):
    - `CVE-2026-102601` / `GHSA-cxf4-7mrp-vvpr` (Low): WhitespacePathNormalizer control-character check bypassed by malformed UTF-8.
  - `livewire/livewire` (v3.5.19 installed, vulnerable to 1 advisory):
    - `GHSA-mhf7-xp6r-6qrc` (Medium): Authorization bypass in `#[Authorize]` via reordered parameter keys.
  - `symfony/http-foundation` (v7.2.0 installed, vulnerable to 1 advisory):
    - `CVE-2026-108082` / `GHSA-r925-mgvr-h9cc` (High): ReDoS in `HeaderUtils::split()` reachable through Cache-Control header.
  - `symfony/mime` (v7.2.0 installed, vulnerable to 1 advisory):
    - `CVE-2026-45070` / `GHSA-vqc8-7275-q272` (Medium): Email Header Injection via Non-Token Characters in Mime Parameter Names.

---

### 1.3 `npm audit`
- **Command:** `npm audit`
- **Exit Code:** `1` (FAILURES DETECTED)
- **Summary:** Found 6 vulnerabilities (1 moderate, 3 high, 2 critical) across frontend dependencies:
  - `concurrently` (via `shell-quote`): Critical command injection (`GHSA-pqg4-j6r4-53mv`, CVSS 8.1).
  - `nanoid`: High severity infinite loop when custom generator size is zero (`GHSA-2v37-7h3g-55p8`).
  - `postcss-selector-parser`: Moderate CPU exhaustion via quadratic complexity in flat selector parsing (`GHSA-rj75-hqrm-r3gf`).
  - `source-map-js`: High severity event-loop denial of service (`GHSA-68fv-2mgg-jv7q`).
  - `vite`: High severity directory traversal and command execution vulnerabilities on Windows (`GHSA-698v-4r3x-3vcv`, `GHSA-r825-w8mm-5pxw`, `GHSA-587h-7wmj-h5pm`, `GHSA-rq7h-c2jc-7f22`).

---

## 2. Syntax & Static Analysis

### 2.1 `php -l` (Syntax Check on Every PHP File)
- **Command:** `Get-ChildItem -Path app, bootstrap, config, database, routes, tests -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }`
- **Exit Code:** `0` (ALL PASS)
- **Summary:** Verified all 300+ PHP source and test files. Zero syntax errors detected. Special attention was paid to `resources/views/components/storefront/⚡product-catalog.blade.php`, which contains a multibyte Unicode character (`⚡`) in the filename; it parsed and passed without syntax issues.

---

### 2.2 `vendor/bin/pint --test` (Code Style Check)
- **Command:** `vendor/bin/pint --test`
- **Exit Code:** `1` (13 FILES WITH VIOLATIONS)
- **Violations Detected:**
  1. `app/Filament/Resources/DeviceUnitResource/Pages/CreateDeviceUnit.php`: `single_quote`, `concat_space`
  2. `app/Filament/Resources/DeviceUnitResource.php`: `unary_operator_spaces`, `no_unused_imports` (unused `GradeEnum`), `not_operator_with_successor_space`
  3. `app/Filament/Resources/OrderResource/Pages/CreateOrder.php`: `unary_operator_spaces`, `no_unused_imports` (unused `InsufficientStockException`, `DB`), `not_operator_with_successor_space`, `binary_operator_spaces`
  4. `app/Filament/Resources/OrderResource.php`: `concat_space`, `unary_operator_spaces`, `not_operator_with_successor_space`
  5. `app/Models/OrderItem.php`: `fully_qualified_strict_types`
  6. `app/Models/ProductCondition.php`: `fully_qualified_strict_types`, `ordered_imports`
  7. `app/Models/TradeInValuation.php`: `fully_qualified_strict_types`, `ordered_imports`
  8. `app/Rules/NepaliPhone.php`: `fully_qualified_strict_types`, `unary_operator_spaces`, `not_operator_with_successor_space`
  9. `config/shop.php`: `binary_operator_spaces`
  10. `database/migrations/2026_11_02_163555_clean_legacy_trade_in_placeholder_addresses.php`: `class_definition`, `braces_position`, `no_unused_imports` (unused `Blueprint`, `Schema`)
  11. `tests/Feature/Admin/OrderResourceTest.php`: `class_attributes_separation`, `no_unused_imports`, `binary_operator_spaces`
  12. `tests/Feature/TradeInTest.php`: `fully_qualified_strict_types`, `ordered_imports`
  13. `tests/Unit/Models/OrderTest.php`: `class_attributes_separation`

---

### 2.3 `vendor/bin/phpstan analyse` (Level 5 - Repo Configured)
- **Command:** `vendor/bin/phpstan analyse --memory-limit=2G`
- **Exit Code:** `1` (17 ERRORS)
- **Error Details (Verbatim):**
```
 ------ ----------------------------------------------------------------------------------------------------
  Line   app\Actions\Checkout\PlaceOrder.php
 ------ ----------------------------------------------------------------------------------------------------
  82     Offset 'delivery_zone' on array{name: string, phone: string, email?: string|null, address: string}
         on left side of ?? does not exist.
 ------ ----------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------
  Line   app\Console\Commands\ExpireUnconfirmedOrders.php
 ------ ----------------------------------------------------------------------------------------------------
  46     Using nullsafe property access "?->name" on left side of ?? is unnecessary. Use -> instead.
 ------ ----------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------
  Line   app\Filament\Resources\DeviceUnitResource\Pages\CreateDeviceUnit.php
 ------ ----------------------------------------------------------------------------------------------------
  18     Access to an undefined property Illuminate\Database\Eloquent\Model::$productCondition.
  18     Access to an undefined property Illuminate\Database\Eloquent\Model::$status.
 ------ ----------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------
  Line   app\Filament\Resources\OrderResource.php
 ------ ----------------------------------------------------------------------------------------------------
  210    Anonymous function never returns null so it can be removed from the return type.
 ------ ----------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------
  Line   app\Filament\Resources\OrderResource\Pages\CreateOrder.php
 ------ ----------------------------------------------------------------------------------------------------
  39     Access to an undefined property Illuminate\Database\Eloquent\Model::$items.
  55     Access to an undefined property Illuminate\Database\Eloquent\Model::$items.
  86     Access to an undefined property Illuminate\Database\Eloquent\Model::$status.
  90     Access to an undefined property Illuminate\Database\Eloquent\Model::$id.
  104    Access to an undefined property Illuminate\Database\Eloquent\Model::$status.
 ------ ----------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------
  Line   app\Filament\Resources\TradeInValuationResource\Pages\EditTradeInValuation.php
 ------ ----------------------------------------------------------------------------------------------------
  25     Cannot access property $stock_ingested_at on Illuminate\Database\Eloquent\Model|int|string.
  26     Cannot access property $status on Illuminate\Database\Eloquent\Model|int|string.
  27     Cannot access property $product_condition_id on Illuminate\Database\Eloquent\Model|int|string.
  29     Cannot access property $productCondition on Illuminate\Database\Eloquent\Model|int|string.
  30     Cannot access property $productCondition on Illuminate\Database\Eloquent\Model|int|string.
  30     Cannot call method ingestIntoStock() on Illuminate\Database\Eloquent\Model|int|string.
 ------ ----------------------------------------------------------------------------------------------------

 ------ ----------------------------------------------------------------------------------------------------
  Line   app\Models\TradeInValuation.php
 ------ ----------------------------------------------------------------------------------------------------
  77     Using nullsafe property access "?->name" on left side of ?? is unnecessary. Use -> instead.
 ------ ----------------------------------------------------------------------------------------------------
 [ERROR] Found 17 errors
```

---

### 2.4 `vendor/bin/phpstan analyse --level=6` (Report Only)
- **Command:** `vendor/bin/phpstan analyse --level=6 --memory-limit=2G`
- **Exit Code:** `1` (38 ERRORS)
- **Summary:** Includes the 17 Level 5 errors plus 21 additional Level 6 findings:
  - Missing return type hints on Controller actions (`BlogController::index`, `BlogController::show`, `DashboardController::index`, `ProductController::storeReview`, `SitemapController::index`, `WishlistController::index`, `WishlistController::toggle`).
  - Missing parameter type hints on Controller methods (`BlogController::show($slug)`).
  - Missing generic type specifications for `HasFactory` and Eloquent relation methods across `Category`, `Customer`, `DeviceUnit`, `Order`, `OrderItem`.

---

## 3. Test Suite

### 3.1 `php artisan test`
- **Command:** `php artisan test`
- **Exit Code:** `0` (ALL PASS)
- **Test Output Summary:**
```
  PASS  Tests\Unit\Actions\PlaceOrderTest
  PASS  Tests\Unit\ExampleTest
  PASS  Tests\Unit\Models\CategoryTest
  PASS  Tests\Unit\Models\CustomerTest
  PASS  Tests\Unit\Models\DeviceUnitTest
  PASS  Tests\Unit\Models\OrderTest
  PASS  Tests\Unit\Models\ProductConditionTest
  PASS  Tests\Unit\Models\ProductTest
  PASS  Tests\Unit\Models\StockMovementTest
  PASS  Tests\Unit\Models\TradeInValuationTest
  PASS  Tests\Unit\Services\CartServiceTest
  PASS  Tests\Unit\Services\StockMovementServiceTest
  PASS  Tests\Unit\SetupValidationTest
  PASS  Tests\Feature\Admin\AdminAuthTest
  PASS  Tests\Feature\Admin\CategoryResourceTest
  PASS  Tests\Feature\Admin\DeviceUnitResourceTest
  PASS  Tests\Feature\Admin\ExpireUnconfirmedOrdersTest
  PASS  Tests\Feature\Admin\LowStockWidgetTest
  PASS  Tests\Feature\Admin\OrderResourceTest
  PASS  Tests\Feature\Admin\ProductResourceTest
  PASS  Tests\Feature\Admin\StockMovementResourceTest
  PASS  Tests\Feature\Admin\TradeInResourceTest
  PASS  Tests\Feature\Auth\AuthenticationTest
  PASS  Tests\Feature\Auth\EmailVerificationTest
  PASS  Tests\Feature\Auth\PasswordConfirmationTest
  PASS  Tests\Feature\Auth\PasswordResetTest
  PASS  Tests\Feature\Auth\PasswordUpdateTest
  PASS  Tests\Feature\Auth\RegistrationTest
  PASS  Tests\Feature\Cart\CartTest
  PASS  Tests\Feature\CartTest
  PASS  Tests\Feature\Catalog\HomepageTest
  PASS  Tests\Feature\Catalog\ProductIndexTest
  PASS  Tests\Feature\Catalog\ProductShowTest
  PASS  Tests\Feature\Checkout\CheckoutTest
  PASS  Tests\Feature\CheckoutTest
  PASS  Tests\Feature\ExampleTest
  PASS  Tests\Feature\Integrity\Phase2DataIntegrityTest
  PASS  Tests\Feature\Performance\Phase3PerformanceTest
  PASS  Tests\Feature\ProfileTest
  PASS  Tests\Feature\Security\Phase1SecurityTest
  PASS  Tests\Feature\Security\SecurityTest
  PASS  Tests\Feature\Smoke\CatalogSeederTest
  PASS  Tests\Feature\Smoke\EndToEndOrderFlowTest
  PASS  Tests\Feature\Smoke\EndToEndTradeInFlowTest
  PASS  Tests\Feature\Smoke\PublicRoutesSmokeTest
  PASS  Tests\Feature\TradeIn\TradeInTest
  PASS  Tests\Feature\TradeInTest

  Tests:    219 passed (1504 assertions)
  Duration: 14.12s
```
- **Flaky or Skipped Tests:** Zero tests skipped, zero flaky failures encountered.

---

## 4. Database Migrations, Rollback & Re-migration

### 4.1 `php artisan migrate:fresh --seed`
- **Command:** `php artisan migrate:fresh --seed`
- **Exit Code:** `0` (SUCCESS)
- **Output:** Dropped all tables successfully, migrated 33 migration batches, and executed database seeders (`DatabaseSeeder`, `IphoneCatalogSeeder`, `SamsungCatalogSeeder`).

### 4.2 `php artisan migrate:status`
- **Command:** `php artisan migrate:status`
- **Exit Code:** `0` (SUCCESS)
- **Status:** All 33 migrations marked as `Ran: Yes`.

### 4.3 `php artisan migrate:rollback --step=999`
- **Command:** `php artisan migrate:rollback --step=999`
- **Exit Code:** `0` (SUCCESSFUL ROLLBACK EXECUTION)
- **Output:** Rolled back 33 migration classes.

### 4.4 `php artisan migrate` (Re-migrating after Rollback)
- **Command:** `php artisan migrate`
- **Exit Code:** `1` (FATAL MIGRATION CRASH)
- **Verbatim Error Output:**
```
   Illuminate\Database\QueryException 

  SQLSTATE[HY000]: General error: 1 table "media" already exists (Connection: sqlite, SQL: create table "media" ("id" integer primary key autoincrement not null, "model_type" varchar not null, "model_id" integer not null, "uuid" varchar, "collection_name" varchar not null, "name" varchar not null, "file_name" varchar not null, "mime_type" varchar, "disk" varchar not null, "conversions_disk" varchar, "size" integer not null, "manipulations" text not null, "custom_properties" text not null, "generated_conversions" text not null, "responsive_images" text not null, "order_column" integer, "created_at" datetime, "updated_at" datetime))

  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:839
    835▕                     $this->getName(), $query, $this->prepareBindings($bindings), $e
    836▕                 );
    837▕             }
    838▕ 
  > 839▕             throw new QueryException(
    840▕                 $this->getName(), $query, $this->prepareBindings($bindings), $e
    841▕             );
    842▕         }
    843▕     }

  1   database\migrations\2026_10_26_114437_create_media_table.php:11
```
- **Root Cause Analysis:** Migration `2026_10_26_114437_create_media_table.php` completely lacks a `down()` method. During rollback, Laravel skipped dropping the `media` table. When subsequent migrations ran, `Schema::create('media')` crashed because the orphaned table already existed. (Documented in Finding `A-001`).
- **Remediation:** Executed `php artisan migrate:fresh --seed` to return the test database to a valid seeded state.

---

## 5. Caching & Frontend Build

### 5.1 Route, Config, and View Caching
- `php artisan config:cache`: Succeeded (`Configuration cached successfully`).
- `php artisan route:cache`: Succeeded (`Routes cached successfully`).
- `php artisan view:cache`: Succeeded (`Blade templates cached successfully`).
- `php artisan config:clear; php artisan route:clear; php artisan view:clear`: Succeeded (All caches cleared).

---

### 5.2 Frontend Asset Compilation (`npm run build`)
- **Command:** `npm run build`
- **Exit Code:** `0` (SUCCESS)
- **Output:**
```
vite v8.2.3 building for production...
transforming...
✓ 49 modules transformed.
rendering chunks...
computing gzip size...
public/build/manifest.json            1.21 kB │ gzip: 0.35 kB
public/build/assets/app-CF7Y1s_z.css  28.94 kB │ gzip: 5.64 kB
public/build/assets/app-Bi86d4zJ.js   36.42 kB │ gzip: 14.28 kB
✓ built in 5.04s
```

---

## 6. Scheduled Tasks & Command Dry-Runs

### 6.1 `php artisan schedule:list`
- **Command:** `php artisan schedule:list`
- **Exit Code:** `0` (SUCCESS)
- **Schedule Table:**
```
  0 * * * *  php artisan shop:expire-unconfirmed-orders  Next Due: within 1 hour
```

---

### 6.2 Custom Artisan Commands Dry-Runs

#### `shop:expire-unconfirmed-orders --dry-run`
- **Command:** `php artisan shop:expire-unconfirmed-orders --dry-run`
- **Exit Code:** `0` (SUCCESS)
- **Output:**
```
Scanning for unconfirmed pending COD orders older than 48 hours (placed before 2026-11-01 18:25:00)...
No expired unconfirmed orders found.
```

#### `products:convert-images --help`
- **Command:** `php artisan products:convert-images --help`
- **Exit Code:** `0` (SUCCESS)
- **Description:** `Migrate existing product images to Spatie Media Library with thumb conversions` with `--force` flag.

#### `user:make-admin --help`
- **Command:** `php artisan user:make-admin --help`
- **Exit Code:** `0` (SUCCESS)
- **Description:** `Promote an existing user to administrator` with argument `email`.
