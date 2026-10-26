# Application Bugs Found During Test Suite Construction

This document tracks real application bugs discovered during the implementation and execution of the automated test suite for FoneX Store, along with failing test cases, expected vs. actual behaviors, and verified fixes.

---

## Bug 1: `recordStockMovement` Does Not Prevent Negative Inventory

- **Failing Test:** `Tests\Unit\Services\StockMovementServiceTest::test_rejects_stock_going_negative`
- **Location:** `app/Models/ProductCondition.php` -> `recordStockMovement()`
- **Expected Behavior:** When decrementing stock by a quantity greater than `quantity_in_stock`, the helper must throw an `\InvalidArgumentException` and reject the operation to prevent negative stock in the database.
- **Actual Behavior:** The method directly called `$this->increment('quantity_in_stock', $quantity)` inside the transaction. If `$quantity` was negative and exceeded current stock, `quantity_in_stock` dropped below zero without warning or error.
- **Fix:** In `recordStockMovement()`, validate `if ($this->quantity_in_stock + $quantity < 0)` and throw `new \InvalidArgumentException("Cannot reduce stock below zero for condition #{$this->id}. Current stock: {$this->quantity_in_stock}, requested adjustment: {$quantity}.")`.

---

## Bug 2: Missing Field Mapping in Existing `TradeInTest`

- **Failing Test:** `Tests\Feature\TradeInTest::test_can_submit_trade_in_request`
- **Location:** `tests/Feature/TradeInTest.php`
- **Expected Behavior:** Trade-in test provides required form parameters according to `TradeInController` validation rules (`customer_name`, `customer_phone`, `condition in:flawless,good,broken`, `device_brand`, `device_model`).
- **Actual Behavior:** The existing test sent `phone` instead of `customer_phone`, and sent custom attributes (`condition_screen`, etc.) without the required `condition` field, causing HTTP 302 with session validation errors and leaving the customer table empty.
- **Fix:** Update test payload to match the controller's validation rules (`customer_phone` and `condition`).

---

## Bug 3: Foreign Currency Symbol ($) Used in Admin Dashboard Widgets

- **Observation:** `app/Filament/Widgets/LowStockProducts.php` and `StoreStatsOverview.php` format money as `USD` with `$` prefixes (e.g. `->money('USD')`, `Stat::make('Total Revenue', '$' . ...)`), contradicting the project's strict NPR-only currency requirement.
- **Fix:** Update Filament money formatting in widgets to use `NPR` or `Rs ` prefix.

---

## Bug 4: Unchecked `number_format()` on Query Input Crashes `/products` on Non-Numeric Prices

- **Failing Test:** `Tests\Feature\Catalog\ProductIndexTest::test_invalid_filter_values_handled_gracefully`
- **Location:** `resources/views/products/index.blade.php:414`
- **Expected Behavior:** If a user passes an invalid or non-numeric query parameter (e.g. `?min_price=invalid_number` or `?max_price=abc`), the page should handle it gracefully without crashing with an uncaught 500 `TypeError`.
- **Actual Behavior:** The Blade template directly invoked `number_format(request('min_price', 0))` and `number_format(request('max_price'))`. In PHP 8.0+, passing non-numeric strings to `number_format()` throws a fatal `TypeError: number_format(): Argument #1 ($num) must be of type int|float, string given`.
- **Fix:** In `resources/views/products/index.blade.php`, check `is_numeric(request('min_price'))` and `is_numeric(request('max_price'))` (or `(float) request(...)`) before formatting.

---

## Bug 5: Undefined Variable `$total` in `resources/views/cart/index.blade.php`

- **Failing Test:** `Tests\Feature\Cart\CartTest::test_can_add_item_to_cart_and_persists_in_session`
- **Location:** `resources/views/cart/index.blade.php:101, 115` & `app/Http/Controllers/CartController.php:47`
- **Expected Behavior:** Viewing `/cart` with items in the cart displays the order summary subtotal and total without throwing an error.
- **Actual Behavior:** `CartController@index` returns `view('cart.index', compact('cartItems', 'subtotal'))`, passing `$subtotal` to the view. However, `cart/index.blade.php` lines 101 and 115 reference `$total`, throwing `ErrorException: Undefined variable $total`.
- **Fix:** In `CartController.php`, compute and pass `total: $subtotal` (or alias `$total = $subtotal`), and in `cart/index.blade.php` use `$subtotal` / `$total` consistently.

---

## Bug 6: `User` Model Does Not Implement `FilamentUser`, Blocking Admin Panel Access Outside Local Environment

- **Failing Test:** `Tests\Feature\Admin\AdminAuthTest::test_authenticated_user_can_access_admin_dashboard`
- **Location:** `app/Models/User.php`
- **Expected Behavior:** In testing and production environments (`APP_ENV != 'local'`), authorized admin users must be allowed access to the Filament admin panel (`/admin`), while non-admin users (such as registered storefront customers) and guests must be blocked.
- **Actual Behavior:** `User` did not implement `Filament\Models\Contracts\FilamentUser`. Filament defaults to denying panel access with HTTP 403 outside of `local` environment for models that do not implement `canAccessPanel()`.
- **Fix:** Implement `FilamentUser` on `User` and define `canAccessPanel(Panel $panel): bool` permitting authorized staff/admin emails (`@fonexstore.test`, `@fonex.com`, `test@example.com`) while denying non-admin storefront customers.



