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
