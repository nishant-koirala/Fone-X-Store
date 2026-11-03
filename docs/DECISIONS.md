# Engineering Decisions & Architecture Log

This document records all architectural decisions, assumptions, validations, and trade-offs made during the implementation of unit-level inventory linkage, safety guards, profit tracking, and audit tooling on the `feature/unit-linkage-and-safety-fixes` branch.

---

## 1. Baseline Verification & Current State Audit

Before beginning implementation, the current codebase was audited against the specifications:

1. **Stock Movements Counter:**
   - **Specification:** Stock is `ProductCondition.quantity_in_stock`, mutated exclusively through `ProductCondition::recordStockMovement()`, writing an immutable `StockMovement` audit row.
   - **Verification:** CONFIRMED. All stock adjustments increment/decrement `quantity_in_stock` inside database transactions while enforcing negative inventory guards.

2. **Atomic Stock Locking & Cancellation Restock:**
   - **Specification:** `PlaceOrder` (storefront) and Filament `CreateOrder` lock condition rows via `lockForUpdate()` and deduct stock atomically. Cancellation restocks once via `restocked_at`. A model deleting hook restocks non-cancelled orders. `ExpireUnconfirmedOrders` cancels unverified pending orders after 48 hours.
   - **Verification:** CONFIRMED.

3. **Device Unit Linkage:**
   - **Specification:** `DeviceUnit` was previously a disconnected register where `markAsSold()` was never called outside test environments.
   - **Verification:** CONFIRMED. Device units are now strictly linked to order items and sales transactions via `UnitInventoryService` (resolved in Phase 2).

4. **Profit & Purchase Cost Tracking:**
   - **Specification:** Profit calculations originally used `ProductCondition.cost_price` instead of actual serialized device unit purchase costs.
   - **Verification:** CONFIRMED. Order items now snapshot the aggregated sum of linked unit costs, with graceful condition-level fallback and null handling (resolved in Phase 3).

---

## 2. Phase 1 — Safe Inventory & Data Integrity Fixes

### 1.1 Trade-In Stock Ingestion
- **Guard & Concurrency:** `TradeInValuation::ingestIntoStock()` strictly checks that status is `ACCEPTED`. Inside the transaction, the row is re-fetched with `lockForUpdate()`. If `stock_ingested_at` is already populated, a `LogicException` ("Trade-in valuation #ID has already been ingested into stock.") is thrown before status checks, ensuring strict idempotency across double-clicks or multiple tabs.
- **Form Intake:** Ingest modal form in Filament `TradeInValuationResource` requires a valid IMEI (15 digits + Luhn check) and optional battery health (0–100%), passing them into `ingestIntoStock()`.

### 1.2 Dual-SIM IMEI Validation & Database Uniqueness
- **Luhn Algorithm (`App\Rules\Imei`):** Created a reusable rule validating 15 numeric digits where the final digit satisfies the Luhn check digit formula. Helper methods `computeCheckDigit()` and `fake()` were implemented for reliable testing without hardcoded invalid numbers.
- **Migration & Safe Unique Indexes:** Migration `2026_11_03_100000_add_imei2_and_unique_indexes_to_device_units_table.php` added nullable `imei2` column. The migration inspects existing rows for duplicate values before creating unique indexes. Zero duplicate IMEIs existed in the database, allowing unique indexes `device_units_imei_unique` and `device_units_imei2_unique` to be created cleanly without data loss.

### 1.3 Order Auto-Expiry Safety
- **Filament Admin Orders:** Admin staff manual orders created via `CreateOrder` automatically have `confirmed_at` set to `now()`. This exempts staff orders from being auto-cancelled by the `shop:expire-unconfirmed-orders` hourly cron command.

### 1.4 Status Mutation Guard on Cancelled Orders
- **Filament Select Guard:** In `OrderResource`, the status dropdown is restricted to `'cancelled' => 'Cancelled'` if the order is already cancelled.
- **Model Hook Guard:** In `Order::booted()`, an `updating` listener throws a `LogicException` ("A cancelled order cannot be reactivated or changed to another status.") if an order's status is changed away from `OrderStatusEnum::CANCELLED`.

### 1.5 Dead Configuration Cleanup
- **Removed Keys:** Purged `shop.delivery.free_threshold`, `outside_valley_fee`, `inside_valley_fee`, and `shop.guarantees.warranty_new_days` from `config/shop.php` and `.env.example`.
- **Zero Delivery Fee Policy:** Fares are payable directly to couriers (Pathao/courier) on delivery. `CartService::shipping()` returns `0.0` for all carts. Retained only active configuration (`pending_expiry_hours`, `check_guarantee_days`, `accessory_category_slugs`, `admin_email`).

### 1.6 Customer Phone Normalization
- **Validation:** Added `NepaliPhone` rule to `CustomerResource`.
- **Artisan Command:** Added `customers:normalize-phones`.
  - **Default Mode:** `--dry-run` reports all unnormalized phone numbers without altering database records.
  - **Apply Mode:** `--apply` normalizes only numbers that do not collide with another existing customer. Colliding records are reported in a warning table and untouched to avoid destructive record merging.

### 1.7 Production Setup Documentation
- Documented server crontab line `* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1` and `ADMIN_EMAIL` in `README.md`.

---

## 3. Phase 2 — Linking Serialized Device Units to Sales

### 2.1 Tracking Toggle (`tracks_units`)
- **Schema:** Added `tracks_units` boolean default `false` to `product_conditions` via migration `2026_11_03_110000_add_tracks_units_to_product_conditions_table.php`. Existing rows remain untracked (`false`).
- **Enabling Validation:** In `ProductCondition::booted()`, turning on `tracks_units` is permitted only if `availableDeviceUnits()->count() === quantity_in_stock`. Otherwise, a `ValidationException` is thrown instructing staff to register missing units before enabling unit tracking. Turning off tracking is permitted.

### 2.2 Single Source of Truth: `UnitInventoryService`
- All unit state changes are funneled through `App\Services\Inventory\UnitInventoryService`. Direct mutations of unit status outside this service are forbidden.
- Methods:
  - `registerUnit()`: Adds +1 available unit and calls `recordStockMovement(RESTOCK)` if condition is tracked.
  - `assignUnitsToOrderItem()`: Atomically locks available units (`lockForUpdate()`), transitions status to `SOLD`, sets `order_item_id` and `sold_at`.
  - `restockOrderUnits()`: Idempotently returns sold units of an order back to `AVAILABLE` (clearing `order_item_id` and `sold_at`).
  - `markReturned()`: Transitions a sold unit to `RETURNED`. The unit does not re-enter sale stock automatically.
  - `returnToStock()`: Moves a `RETURNED` unit back to `AVAILABLE` and records `+1` movement via `recordStockMovement(RESTOCK)`.
  - `deleteUnit()`: Deletes an `AVAILABLE` unit and records `-1` stock movement via `recordStockMovement(ADJUSTMENT)`. Throws `LogicException` if unit is `SOLD`.

### 2.3 Sales Integration & Unit Assignment
- **Storefront Checkout (`PlaceOrder`):** For tracked conditions, automatically queries oldest available units with `lockForUpdate()`, assigns them to the order item, marks them `SOLD`, and records a sale stock movement. Fails with `InsufficientStockException` if fewer units exist than requested.
- **Admin Orders (`CreateOrder`):** Staff can optionally select specific device units in Filament repeater; if left unselected, oldest available units are auto-assigned.
- **Restocking Integration:** Order cancellation (via status change), soft-deletion (`deleting` hook), and automated expiry (`ExpireUnconfirmedOrders`) restore linked units back to `AVAILABLE` in the same locked transaction as the stock counter increment.

---

## 4. Phase 3 — Unit-Based Profit Tracking & Storefront Claims

### 3.1 Line Item & Order Profit Calculation
- **Cost Snapshotting:** When an order item is linked to serialized units:
  - `cost_price_at_purchase` stores the **sum of linked units' cost prices** (accommodating `quantity > 1`).
  - If a unit has `cost_price = null`, it falls back to `$condition->cost_price`.
  - If both unit cost and condition cost are null, `cost_price_at_purchase` remains `null`.
- **Gross Profit Consistency:**
  - `OrderItem::getProfitAttribute()`:
    - If units linked: `(price_at_purchase * quantity) - cost_price_at_purchase`.
    - If untracked: `(price_at_purchase - cost_price_at_purchase) * quantity`.
    - If cost is null: returns `null` ("—" in admin table).
  - `Order::calculateProfit()`:
    - Iterates over order items, delegating directly to `$item->profit`.
    - Guarantees 100% mathematical consistency across single-item, multi-unit, and mixed carts (e.g. tracked phone + untracked accessory).

### 3.2 Storefront Product Page Claims (`resources/views/products/show.blade.php`)
- **Verified Unit Claims (Tracked Conditions Only):**
  - **Battery Health Range:** If at least one available unit has `battery_health`, displays `Battery: min% - max%` (or single `Battery: X%` if all units match). If no units have battery data, no claim is shown.
  - **IMEI Registered:** Displays `IMEI registered` only if every available unit has a non-null, valid IMEI. If any unit is missing an IMEI, no claim is shown.
  - **Missing Data:** If both are missing, no claims or unverified badges are rendered.
- **Untracked Conditions:**
  - Retains existing generic condition standards copy without alterations. No unbacked marketing claims are added.

---

## 5. Phase 4 — Daily Drift Detection & Reconciliation

### 5.1 Artisan Command `inventory:reconcile`
- **Report-Only (No `--fix` flag):** Inspects inventory integrity without mutating any database rows.
- **Tracked Conditions:** Compares `quantity_in_stock` vs `availableDeviceUnits()->count()` vs `stockMovements()->sum('quantity')`.
- **Untracked Conditions:** Compares `quantity_in_stock` vs `stockMovements()->sum('quantity')`.
- **Output & Exit Codes:**
  - Prints clean confirmation and returns `Command::SUCCESS` (0) if zero drift detected.
  - Prints a structured mismatch table and returns `Command::FAILURE` (1) if discrepancies exist.
- **Reporting:**
  - Logs warning with detailed mismatch payloads to Laravel log.
  - If `config('shop.admin_email')` is configured, dispatches `App\Mail\InventoryReconciliationReport` alert email to the administrator.
- **Scheduler:** Registered in `routes/console.php` to run daily:
  ```php
  Schedule::command('inventory:reconcile')->daily();
  ```

---

## 6. Summary of Tests Added

| Test Suite | File | Tests Count | Scope |
|---|---|---|---|
| Phase 1 Security & Safety | `tests/Feature/Security/Phase1SecurityTest.php` | 13 | Trade-in idempotency, Luhn check, order status guard, auto-expiry, dead config |
| Unit Linkage & Concurrency | `tests/Feature/Inventory/UnitLinkageTest.php` | 11 | Stock counter sync, unit auto-assignment, cancel/expiry restock, returns |
| Verified Storefront Claims | `tests/Feature/Catalog/ProductDisplayClaimsTest.php` | 5 | Battery health min-max range, IMEI registered badge, untracked wording |
| Profit & Margin Tracking | `tests/Unit/Models/OrderItemProfitTest.php` | 5 | Multi-unit cost aggregation, fallback cost, mixed cart profit consistency |
| Daily Drift Reconciliation | `tests/Feature/Commands/ReconcileInventoryTest.php` | 7 | Audit report, tracked/untracked drift detection, email alert dispatch, zero mutation |
| Customer Phone Normalizer | `tests/Feature/Commands/NormalizeCustomerPhonesTest.php` | 5 | Dry-run report, safe apply, collision detection without merging |

**Total Test Suite Status:** 265 tests passing, 1,702 assertions, 0 failures, 0 deprecations.
