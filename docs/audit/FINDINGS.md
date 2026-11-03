# Detailed Audit Findings & Evidence

This document registers every issue uncovered during the audit of FoneX Store. Every finding includes its exact file location, code or tool output evidence, reproduction steps, architectural impact, recommended remediation, effort estimate, and status.

---

## Index of Findings by Severity

| ID | Title | Severity | Category | Status |
| :---: | :--- | :---: | :---: | :---: |
| **A-001** | Migration `create_media_table` Lacks `down()` Method Breaking Rollback & Re-migration | **CRITICAL** | Schema / Migrations | OPEN |
| **A-002** | Customer Deletion Cascade Permanently Destroys Orders & Financial Transaction History | **CRITICAL** | Data Loss / Schema | OPEN |
| **A-003** | Device Unit Status Transitions & Deletions Cause Phantom Inventory Drift | **HIGH** | Inventory / Stock Logic | OPEN |
| **A-004** | Scheduled Expiry Command Auto-Cancels Staff-Created Admin Orders | **HIGH** | Order Lifecycle / Business Logic | OPEN |
| **A-005** | Trade-In Ingestion Race Condition & Status Verification Bypass | **HIGH** | Concurrency / Stock Drift | OPEN |
| **A-006** | Delivery Fee Omission in `PlaceOrder` & Dead Outside-Valley Delivery Pricing | **HIGH** | Financial / Pricing | OPEN |
| **A-007** | Re-activating Cancelled Orders Duplicates Stock Without Inventory Deduction | **HIGH** | Stock / Inventory Drift | OPEN |
| **A-008** | Security Vulnerabilities in Composer & NPM Dependencies (CVEs & GHSAs) | **HIGH** | Security / Dependencies | OPEN |
| **A-009** | Unescaped Blade Echo `{!! !!}` in Product Details & Blog Content (Stored XSS Risk) | **MEDIUM** | Security / XSS | OPEN |
| **A-010** | Plaintext Customer PII Dumped to Log Files on Checkout Failure | **MEDIUM** | Security / Privacy | OPEN |
| **A-011** | Static Analysis Failures: 17 Errors on PHPStan Level 5, 38 on Level 6 | **MEDIUM** | Static Analysis / Types | OPEN |
| **A-012** | Code Style Violations Failing CI Quality Gate on 13 Files (`pint --test`) | **MEDIUM** | Code Style / CI | OPEN |
| **A-013** | Missing Database Check Constraint & Race Condition Allowing Negative Inventory | **MEDIUM** | Data Integrity / Schema | OPEN |
| **A-014** | `StoreTradeInRequest` Omits Input Validation for Customer Email | **LOW** | Input Validation | OPEN |
| **A-015** | Tracked Temporary Scratch and Lock Files in `storage/tmp/` | **LOW** | Repository Hygiene | OPEN |
| **A-016** | Unused / Dead Configuration Keys in `config/shop.php` | **LOW** | Configuration | OPEN |
| **A-017** | Missing Custom Branded HTTP Error Pages (404, 419, 429, 500) | **LOW** | User Experience | OPEN |
| **A-018** | Phone Catalog Seeders Use USD Pricing (Rs 150 - Rs 1700) Instead of NPR Values | **INFO** | Seeders / Data Realism | OPEN |

---

## Detailed Findings

### Finding A-001: Migration `create_media_table` Lacks `down()` Method Breaking Rollback & Re-migration
- **Severity:** **CRITICAL**
- **Category:** Schema / Migrations
- **File & Line(s):** `database/migrations/2026_10_26_114437_create_media_table.php:9-32`
- **Evidence:**
```php
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            // ...
        });
    }
}; // Lacks public function down(): void { Schema::dropIfExists('media'); }
```
- **Why It Matters:** When running `php artisan migrate:rollback`, Laravel executes the down method of every migrated file. Because this migration lacks a `down()` method, it silently does nothing and leaves the physical `media` table in the database. When migrations are subsequently re-run (`php artisan migrate`), the migration fails fatally with:
  `SQLSTATE[HY000]: General error: 1 table "media" already exists`. This prevents repeatable deployments, test rollbacks, and zero-downtime rollback procedures.
- **How to Reproduce:**
  1. Run `php artisan migrate:fresh --seed`
  2. Run `php artisan migrate:rollback --step=999`
  3. Run `php artisan migrate`
  4. Observe fatal SQLite exception on `create_media_table`.
- **Recommended Fix:** Add the missing down method to `2026_10_26_114437_create_media_table.php`:
```php
public function down(): void
{
    Schema::dropIfExists('media');
}
```
- **Effort:** S
- **Status:** **OPEN** *(Per audit rules, already-run migrations must never be edited directly in Mode B).*

---

### Finding A-002: Customer Deletion Cascade Permanently Destroys Orders & Financial Transaction History
- **Severity:** **CRITICAL**
- **Category:** Data Loss / Schema
- **File & Line(s):**
  - `database/migrations/2026_08_07_000006_create_orders_table.php:13`
  - `app/Filament/Resources/CustomerResource.php:68`
- **Evidence:**
```php
// In 2026_08_07_000006_create_orders_table.php:
$table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

// In app/Filament/Resources/CustomerResource.php:
DeleteAction::make(),
```
- **Why It Matters:** When staff deletes a customer in the Filament admin table, the underlying database foreign key constraint executes `ON DELETE CASCADE`. This immediately and irreversibly destroys all historical sales orders, order items, and revenue records associated with that customer. Financial accounting data and tax audit records are permanently lost.
- **How to Reproduce:**
  1. Create a customer with 3 completed orders.
  2. In Filament admin (`/admin/customers`), click "Delete" on the customer row.
  3. Query `Order::withTrashed()->count()` — all 3 orders have been hard-deleted from the database table.
- **Recommended Fix:**
  1. Create a migration altering `orders.customer_id` foreign key from `cascadeOnDelete()` to `restrictOnDelete()` or make it nullable with `nullOnDelete()`.
  2. In `CustomerResource`, configure `DeleteAction` to disallow deletion if `$record->orders()->exists()`.
- **Effort:** M
- **Status:** **OPEN** *(Requires database schema migration; documented only per Mode A rules).*

---

### Finding A-003: Device Unit Status Transitions & Deletions Cause Phantom Inventory Drift
- **Severity:** **HIGH**
- **Category:** Inventory / Stock Logic
- **File & Line(s):**
  - `app/Filament/Resources/DeviceUnitResource/Pages/CreateDeviceUnit.php:18-25`
  - `app/Filament/Resources/DeviceUnitResource/Pages/EditDeviceUnit.php:13-18`
  - `app/Filament/Resources/DeviceUnitResource.php:155`
  - `app/Actions/Checkout/PlaceOrder.php:128-142`
- **Evidence:**
```php
// In CreateDeviceUnit.php:
if ($unit->status === DeviceUnitStatusEnum::AVAILABLE && $unit->productCondition) {
    $unit->productCondition->recordStockMovement(
        type: StockMovementTypeEnum::RESTOCK,
        quantity: 1, ...
    );
}
// EditDeviceUnit.php has no afterSave() hook:
// Changing status from AVAILABLE -> DEFECTIVE never calls recordStockMovement(ADJUSTMENT, -1)
// Deleting via DeleteAction::make() in DeviceUnitResource.php never decrements stock!
```
- **Why It Matters:**
  1. If a shop already has 5 units in stock (`quantity_in_stock = 5`) and staff enters the individual IMEIs into "Device Units (IMEI)", each created unit adds +1 via `recordStockMovement`, inflating stock from 5 to 10.
  2. If a physical phone is dropped or found defective, staff changes status from `AVAILABLE` to `DEFECTIVE` in `EditDeviceUnit`. The condition stock is NOT decremented, leaving a phantom sellable unit on the storefront.
  3. If staff deletes an available unit via `DeleteAction`, no stock movement is recorded.
  4. Storefront checkout (`PlaceOrder`) only deducts aggregate `ProductCondition->quantity_in_stock` and never assigns a `DeviceUnit` to the `OrderItem`, leaving all sold units detached from orders.
- **How to Reproduce:**
  1. Check a condition with stock = 2.
  2. Create a DeviceUnit in Filament with status = AVAILABLE. Stock becomes 3.
  3. Edit the unit to status = DEFECTIVE. Stock remains 3.
  4. Delete the unit in Filament. Stock remains 3.
- **Recommended Fix:** Implement a unified inventory synchronization service or model observer on `DeviceUnit` that detects status transitions (`wasChanged('status')`) and automatically adjusts `ProductCondition->quantity_in_stock` via `StockMovement`. Prevent deletion of available units without recording an inventory adjustment.
- **Effort:** L
- **Status:** **OPEN** *(Exceeds minor-fix limit and touches core inventory logic).*

---

### Finding A-004: Scheduled Expiry Command Auto-Cancels Staff-Created Admin Orders
- **Severity:** **HIGH**
- **Category:** Order Lifecycle / Business Logic
- **File & Line(s):**
  - `app/Console/Commands/ExpireUnconfirmedOrders.php:27-33`
  - `app/Filament/Resources/OrderResource/Pages/CreateOrder.php:102-105`
- **Evidence:**
```php
// In ExpireUnconfirmedOrders.php:
$orders = Order::query()
    ->where('status', OrderStatusEnum::PENDING)
    ->whereNull('confirmed_at')
    ->whereNull('restocked_at')
    ->where('created_at', '<=', $cutoff)
    ->get();

// In CreateOrder.php (Filament):
$order->forceFill([
    'total'        => $total,
    'restocked_at' => $order->status === OrderStatusEnum::CANCELLED ? now() : null,
])->saveQuietly(); // Leaves confirmed_at NULL!
```
- **Why It Matters:** When store staff manually registers a phone or walk-in order in Filament admin (`/admin/orders/create`), the order is created with `status = PENDING` and `confirmed_at = null` unless staff manually sets a confirmation date. After 48 hours, the scheduled command `shop:expire-unconfirmed-orders` classifies the manual staff order as an abandoned web COD order, cancels it, and restocks the items, disrupting store fulfillment.
- **How to Reproduce:**
  1. In Filament admin, create an order for a customer. Leave `confirmed_at` blank.
  2. Fast-forward timestamps: `$order->update(['created_at' => now()->subHours(50)]);`
  3. Run `php artisan shop:expire-unconfirmed-orders`
  4. Observe that staff order is cancelled and items restocked.
- **Recommended Fix:** In `CreateOrder::afterCreate()`, automatically set `'confirmed_at' => now()` for all staff-created orders, or add a column `is_manual` / `channel` distinguishing storefront web checkouts from staff admin orders.
- **Effort:** S
- **Status:** **OPEN** *(Touches order lifecycle business rules; documented only in Mode A).*

---

### Finding A-005: Trade-In Ingestion Race Condition & Status Verification Bypass
- **Severity:** **HIGH**
- **Category:** Concurrency / Stock Drift
- **File & Line(s):**
  - `app/Models/TradeInValuation.php:70-75`
  - `app/Filament/Resources/TradeInValuationResource.php:125`
- **Evidence:**
```php
// In TradeInValuation.php:
public function ingestIntoStock(ProductCondition $condition, ...): ?StockMovement
{
    if ($this->stock_ingested_at !== null) {
        return null;
    }
    // Transaction entered AFTER idempotency check without row locking!
    return DB::transaction(function () use (...) { ... });
}

// In TradeInValuationResource.php:
Action::make('ingestStock')
    ->visible(fn (TradeInValuation $record): bool => $record->stock_ingested_at === null)
    // Does NOT check if status === TradeInStatusEnum::COMPLETED or REVIEWED!
```
- **Why It Matters:**
  1. Two staff members clicking "Ingest to Stock" simultaneously both read `stock_ingested_at === null` before either commits the transaction. Both transactions proceed, adding +2 stock movements and creating 2 duplicate `DeviceUnit` records for a single physical phone.
  2. The table action is visible for ANY trade-in whose `stock_ingested_at` is null, including requests in `PENDING` or `REJECTED` status. Staff can accidentally ingest unassessed or rejected devices into sellable stock.
- **How to Reproduce:**
  1. Submit a trade-in request (status = PENDING).
  2. Go to Filament Trade-Ins table: "Ingest to Stock" button is active and functional.
  3. Trigger two concurrent HTTP requests to the action endpoint: both execute `recordStockMovement`, incrementing stock by 2.
- **Recommended Fix:**
  1. In `TradeInValuationResource`, set visibility: `->visible(fn (TradeInValuation $r) => $r->stock_ingested_at === null && $r->status === TradeInStatusEnum::COMPLETED)`.
  2. In `TradeInValuation::ingestIntoStock()`, move the check inside `DB::transaction()` with `TradeInValuation::where('id', $this->id)->lockForUpdate()->first()`.
- **Effort:** M
- **Status:** **OPEN** *(Touches stock and concurrency logic; documented only).*

---

### Finding A-006: Delivery Fee Omission in `PlaceOrder` & Dead Outside-Valley Delivery Pricing
- **Severity:** **HIGH**
- **Category:** Financial / Pricing
- **File & Line(s):**
  - `app/Actions/Checkout/PlaceOrder.php:44,67-68,115`
  - `app/Services/Cart/CartService.php:94-116,123-125`
  - `config/shop.php:43`
- **Evidence:**
```php
// In CartService.php:
public function shipping(?array $sessionCart = null): float {
    // Returns inside_valley_fee for accessories-only orders
    return (float) config('shop.delivery.inside_valley_fee', 0.0);
}
public function total(?array $sessionCart = null): float {
    return $this->subtotal($sessionCart) + $this->shipping($sessionCart);
}

// In PlaceOrder.php:
$orderTotal = 0;
foreach ($cart as $condId => $item) {
    // ...
    $orderTotal += $lineTotal; // Pure line items only! Shipping is NEVER added!
}
$order = Order::create([
    'total' => $orderTotal, // Missing shipping fee!
]);
```
- **Why It Matters:**
  1. On the cart and checkout pages, `CartService::total()` displays `subtotal + shipping` to the customer. When the order is placed, `PlaceOrder` computes `Order->total` solely from line items without adding delivery fees, creating an invoice discrepancy where the order record total does not match what the customer was shown.
  2. Even though `CheckoutRequest` captures `delivery_zone = outside_valley` and `.env.example` defines `SHOP_OUTSIDE_VALLEY_FEE=200`, neither `CartService` nor `PlaceOrder` uses `SHOP_OUTSIDE_VALLEY_FEE`. Courier delivery outside Kathmandu Valley is billed at Rs 0, resulting in uncollected delivery revenue.
- **How to Reproduce:**
  1. Add an accessory (e.g. charger, Rs 500) to cart. Set `SHOP_INSIDE_VALLEY_FEE=100`.
  2. Cart displays Rs 600 (Rs 500 + Rs 100 shipping).
  3. Complete checkout. Inspect database order row: `total` is Rs 500.00 instead of Rs 600.00.
- **Recommended Fix:**
  1. Pass `delivery_zone` into `CartService::shipping()`.
  2. Add `delivery_fee` column to `orders` table.
  3. Include delivery fee in `PlaceOrder` calculation of `Order->total`.
- **Effort:** M
- **Status:** **OPEN** *(Touches financial/pricing calculations and requires schema additions).*

---

### Finding A-007: Re-activating Cancelled Orders Duplicates Stock Without Inventory Deduction
- **Severity:** **HIGH**
- **Category:** Stock / Inventory Drift
- **File & Line(s):** `app/Models/Order.php:40-59`
- **Evidence:**
```php
static::updated(function (Order $order) {
    $isCancelled = $order->status === OrderStatusEnum::CANCELLED;
    if ($order->wasChanged('status') && $isCancelled && $order->restocked_at === null) {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $item->productCondition->recordStockMovement(
                    type: StockMovementTypeEnum::RESTOCK,
                    quantity: $item->quantity, ...
                );
            }
            $order->forceFill(['restocked_at' => now()])->saveQuietly();
        });
    }
});
```
- **Why It Matters:** If an order is marked `CANCELLED`, inventory is returned to stock (+N) and `restocked_at` is stamped. If staff later changes the status back to `PENDING` or `PAID` (for example, customer calls back and confirms COD), the system does NOT re-deduct stock! If the order is then fulfilled, the customer receives goods whose inventory was never re-deducted, causing permanent inventory discrepancy.
- **How to Reproduce:**
  1. Create an order for 1 unit (stock drops from 5 to 4).
  2. Cancel order in Filament: stock returns to 5, `restocked_at` set.
  3. Edit order in Filament: change status to `PAID`. Stock remains 5!
  4. Fulfill order: 1 unit physically leaves store, but system still reports 5 in stock.
- **Recommended Fix:** In `Order::booted()`, if `wasChanged('status')` transitions from `CANCELLED` to a non-cancelled status, verify stock availability, deduct inventory with `recordStockMovement(SALE, -$qty)`, and reset `restocked_at = null`.
- **Effort:** M
- **Status:** **OPEN** *(Core inventory transaction logic; documented only).*

---

### Finding A-008: Security Vulnerabilities in Composer & NPM Dependencies (CVEs & GHSAs)
- **Severity:** **HIGH**
- **Category:** Security / Dependencies
- **File & Line(s):** `composer.lock`, `package-lock.json`
- **Evidence:** Tool execution outputs from `composer audit` and `npm audit`:
  - `concurrently` (via `shell-quote`): Critical Command Injection (`GHSA-pqg4-j6r4-53mv`, CVSS 8.1).
  - `vite` (v8.2.0-8.2.3): High severity file disclosure and command execution on Windows (`GHSA-587h-7wmj-h5pm`, `GHSA-698v-4r3x-3vcv`).
  - `league/commonmark` (v2.6.0): High severity DoS and XSS vulnerabilities (`GHSA-3q6v-r5mr-hxv8`, `GHSA-f8fg-pg57-v4j8`).
  - `livewire/livewire` (v3.5.19): Authorization bypass in `#[Authorize]` attribute (`GHSA-mhf7-xp6r-6qrc`).
  - `symfony/http-foundation` (v7.2.0): ReDoS via Cache-Control header (`CVE-2026-108082`).
  - `symfony/mime` (v7.2.0): Email header injection (`CVE-2026-45070`).
- **Why It Matters:** Known CVEs can be weaponized for denial of service, remote command execution during development on Windows, and email header tampering.
- **How to Reproduce:** Run `composer audit` and `npm audit` in terminal.
- **Recommended Fix:** Run `composer update` and `npm update` to pull patched minor/patch versions of affected packages.
- **Effort:** M
- **Status:** **OPEN** *(Requires updating dependencies across multiple packages).*

---

### Finding A-009: Unescaped Blade Echo `{!! !!}` in Product Details & Blog Content (Stored XSS Risk)
- **Severity:** **MEDIUM**
- **Category:** Security / XSS
- **File & Line(s):**
  - `resources/views/products/show.blade.php:471`
  - `resources/views/blog/show.blade.php:28`
- **Evidence:**
```blade
{{-- In resources/views/products/show.blade.php:471 --}}
<div class="prose max-w-none text-brand-charcoal/80 font-sans leading-relaxed">
    {!! $product->description !!}
</div>

{{-- In resources/views/blog/show.blade.php:28 --}}
<div class="prose max-w-none text-brand-charcoal font-sans leading-relaxed">
    {!! $post->content !!}
</div>
```
- **Why It Matters:** In `ProductResource.php`, `description` is a standard `Textarea` without HTML sanitization. If an administrator account is compromised or if product descriptions are populated via third-party web scrapers, unescaped `{!! $product->description !!}` allows execution of malicious JavaScript in customer browsers (Stored Cross-Site Scripting).
- **How to Reproduce:**
  1. In Filament admin, edit a product description to: `<script>alert('XSS')</script>`.
  2. Visit `/products/{slug}` on the storefront.
  3. The script executes directly in the user's browser.
- **Recommended Fix:** Use `e()` or Blade `{{ $product->description }}` with `nl2br()`, or pass HTML through an established purifier (e.g. `mews/purifier` or HTML Purifier) before rendering raw HTML.
- **Effort:** S
- **Status:** **OPEN** *(Touches user-visible view templates).*

---

### Finding A-010: Plaintext Customer PII Dumped to Log Files on Checkout Failure
- **Severity:** **MEDIUM**
- **Category:** Security / Privacy
- **File & Line(s):** `app/Http/Controllers/CheckoutController.php:78-81`
- **Evidence:**
```php
} catch (\Throwable $e) {
    Log::error('Order checkout failed: '.$e->getMessage(), [
        'exception' => $e,
        'payload' => $request->except(['_token']),
    ]);
    return back()->withInput()->with('error', '...');
}
```
- **Why It Matters:** When any unexpected error occurs during checkout, `$request->except(['_token'])` dumps full customer personally identifiable information (customer full name, private mobile number, private email address, and home delivery address) into unencrypted log files (`storage/logs/laravel.log`). If logs are sent to third-party aggregation tools (Datadog, Papertrail) or viewed by contractors, customer privacy is breached.
- **How to Reproduce:**
  1. Simulate a database failure or throw an exception in `PlaceOrder`.
  2. Submit checkout with customer details.
  3. Inspect `storage/logs/laravel.log` — full customer name, phone number, and physical home address are visible in plain text.
- **Recommended Fix:** Sanitize logged payload by masking or stripping sensitive fields:
```php
'payload' => $request->only(['delivery_zone']),
```
- **Effort:** S
- **Status:** **OPEN** *(Exceeds minor fix scope; touches controller error logging).*

---

### Finding A-011: Static Analysis Failures: 17 Errors on PHPStan Level 5, 38 on Level 6
- **Severity:** **MEDIUM**
- **Category:** Static Analysis / Types
- **File & Line(s):**
  - `app/Actions/Checkout/PlaceOrder.php:82`
  - `app/Console/Commands/ExpireUnconfirmedOrders.php:46`
  - `app/Filament/Resources/DeviceUnitResource/Pages/CreateDeviceUnit.php:18`
  - `app/Filament/Resources/OrderResource.php:210`
  - `app/Filament/Resources/OrderResource/Pages/CreateOrder.php:39,55,86,90,104`
  - `app/Filament/Resources/TradeInValuationResource/Pages/EditTradeInValuation.php:25,26,27,29,30`
  - `app/Models/TradeInValuation.php:77`
- **Evidence:** Running `vendor/bin/phpstan analyse --memory-limit=2G` fails with 17 Level 5 errors.
  - `nullCoalesce.offset`: `Offset 'delivery_zone' on array{name: string, phone: string, email?: string|null, address: string} does not exist.`
  - `nullsafe.neverNull`: `Using nullsafe property access "?->name" on left side of ?? is unnecessary.`
  - `property.notFound`: Access to undefined properties on `Illuminate\Database\Eloquent\Model` in Filament pages (`$status`, `$productCondition`, `$items`, `$id`).
  - `return.unusedType`: Anonymous function never returns null in `OrderResource.php:210`.
- **Why It Matters:** Breaks continuous integration quality gates (`ci.yml:75` running `composer run analyse`). Untyped `$this->record` references in Filament pages hide runtime type bugs.
- **Recommended Fix:** Add correct PHPDoc types to `$data` in `PlaceOrder`, remove redundant nullsafe operators, declare `/** @var Order $order */` and `/** @var DeviceUnit $unit */` on Filament page records, and adjust return type in `OrderResource.php`.
- **Effort:** S
- **Status:** **RESOLVED (Mode B)** *(Fully resolved across commits `2be8ee4`, `3c20719`, `0f13457`, `bc6a59d`, `93fa3c6`, `8cd6416`, and `2ee356a`. PHPStan Level 5 now passes with 0 errors across the entire codebase).*

---

### Finding A-012: Code Style Violations Failing CI Quality Gate on 13 Files (`pint --test`)
- **Severity:** **MEDIUM**
- **Category:** Code Style / CI
- **File & Line(s):** 13 files across `app/`, `config/`, `database/`, and `tests/`.
- **Evidence:** `vendor/bin/pint --test` exited with code 1.
  - Unused imports in `CreateOrder.php`, `DeviceUnitResource.php`, `clean_legacy_trade_in_placeholder_addresses.php`.
  - Missing blank lines between methods in `OrderResourceTest.php` and `OrderTest.php`.
  - Operator spacing in `config/shop.php` and `NepaliPhone.php`.
- **Why It Matters:** The CI workflow (`.github/workflows/ci.yml:44`) executes `composer run lint` (`vendor/bin/pint --test`) as a mandatory prerequisite for running test suites. Pull requests will fail CI immediately until style issues are resolved.
- **Recommended Fix:** Run `vendor/bin/pint` on the repository or resolve unused imports and spacing in qualifying files.
- **Effort:** S
- **Status:** **RESOLVED (Mode B for all application & test code)** *(Fixed across commits `162f6ca`, `03cc296`, `0809dd0`, `6145dcb`, `f6b7d2b`, `34f4fa4`, `eae6cc0`, `7d58d44`, and `8dad519`. The sole remaining Pint notice is in migration `database/migrations/2026_11_02_163555_clean_legacy_trade_in_placeholder_addresses.php`, which is intentionally preserved unedited under audit rule "Never edit an already-run migration" and Decision [D-004](file:///docs/audit/DECISIONS.md#d-004)).*

---

### Finding A-013: Missing Database Check Constraint & Race Condition Allowing Negative Inventory
- **Severity:** **MEDIUM**
- **Category:** Data Integrity / Schema
- **File & Line(s):**
  - `database/migrations/2026_08_07_000003_create_product_conditions_table.php:16`
  - `app/Models/ProductCondition.php:120-124`
- **Evidence:**
```php
// In 2026_08_07_000003_create_product_conditions_table.php:
$table->integer('quantity_in_stock')->default(0); // Signed integer, no check constraint!

// In ProductCondition.php:
if ($this->quantity_in_stock + $quantity < 0) { ... } // Evaluated in-memory BEFORE transaction!
return DB::transaction(function () use (...) {
    $this->increment('quantity_in_stock', $quantity); // DB accepts negative values!
});
```
- **Why It Matters:** If two concurrent operations attempt to deduct stock without acquiring a prior row lock (`lockForUpdate()`), both evaluate `$this->quantity_in_stock` against stale memory models. Both execute `$this->increment('quantity_in_stock', -1)`, driving `quantity_in_stock` to `-1`. Because the column is signed without a database constraint (`CHECK (quantity_in_stock >= 0)`), invalid negative inventory is persisted.
- **How to Reproduce:**
  1. Condition stock = 1.
  2. Concurrently call `$condition->recordStockMovement('sale', -1)` from two background threads without locking.
  3. Condition stock becomes -1 in the database.
- **Recommended Fix:** Add a database migration adding an unsigned constraint or `CHECK (quantity_in_stock >= 0)`, and acquire `lockForUpdate()` inside `recordStockMovement()` before checking stock bounds.
- **Effort:** M
- **Status:** **OPEN** *(Requires database migration and core model locking redesign).*

---

### Finding A-014: `StoreTradeInRequest` Omits Input Validation for Customer Email
- **Severity:** **LOW**
- **Category:** Input Validation
- **File & Line(s):**
  - `app/Http/Requests/StoreTradeInRequest.php:24-34`
  - `app/Http/Controllers/TradeInController.php:53-54,95-97`
- **Evidence:**
```php
// In StoreTradeInRequest.php:
public function rules(): array
{
    return [
        'device_brand' => ['required', 'string', 'max:100'],
        'device_model' => ['required', 'string', 'max:255'],
        'condition' => ['required', 'string', 'in:flawless,good,broken'],
        'customer_name' => ['required', 'string', 'max:255'],
        'customer_phone' => ['required', 'string', new NepaliPhone],
        'additional_notes' => ['nullable', 'string', 'max:1000'],
    ]; // 'email' is completely omitted from validation rules!
}
```
- **Why It Matters:** In `TradeInController.php:53`, the controller inspects `$request->input('email')`. If present, it writes to `customers.email` and executes `Mail::to($customer->email)->send(...)`. Because `email` is unvalidated, a malicious actor could send malformed strings or header injections, bypassing request validation.
- **How to Reproduce:** POST to `/trade-in` with `email = not-a-valid-email-address`. The request passes validation.
- **Recommended Fix:** Add `'email' => ['nullable', 'email', 'max:255']` to `StoreTradeInRequest::rules()`.
- **Effort:** S
- **Status:** **OPEN** *(Single file validation rule fix).*

---

### Finding A-015: Tracked Temporary Scratch and Lock Files in `storage/tmp/`
- **Severity:** **LOW**
- **Category:** Repository Hygiene
- **File & Line(s):**
  - `storage/tmp/php*.tmp` (12 files)
  - `storage/tmp/sf_proc_00.*` (4 files)
- **Evidence:** `git ls-files storage/tmp/*` returns 16 tracked files.
- **Why It Matters:** Temporary upload files and Symfony process runner lock files were inadvertently staged and committed to the Git repository. They clutter the repository history, inflate clone times, and represent poor repository hygiene.
- **How to Reproduce:** Run `git ls-files "storage/tmp/*"`.
- **Recommended Fix:** Untrack the files using `git rm --cached storage/tmp/*` and ensure `storage/tmp/*` is added to `.gitignore`.
- **Effort:** S
- **Status:** **OPEN** *(Requires git untracking commit).*

---

### Finding A-016: Unused / Dead Configuration Keys in `config/shop.php`
- **Severity:** **LOW**
- **Category:** Configuration
- **File & Line(s):** `config/shop.php:9,10,41,43,59`
- **Evidence:** Cross-reference in `docs/audit/CONFIG_ENV_MATRIX.md`:
  - `shop.currency` ('NPR'): Used nowhere in code.
  - `shop.currency_symbol` ('Rs '): Used nowhere in code; views hardcode `'Rs '`.
  - `shop.delivery.free_threshold` (0): Used nowhere in code.
  - `shop.delivery.outside_valley_fee` (200): Used nowhere in code.
  - `shop.guarantees.warranty_new_days` (365): Used nowhere in code.
- **Why It Matters:** Misleads developers and administrators into believing that changing these configuration options in `.env` or `config/shop.php` alters storefront behavior or pricing.
- **Recommended Fix:** Either wire these configuration keys into their corresponding Blade views and calculation services (`CartService`), or deprecate and remove them.
- **Effort:** S
- **Status:** **OPEN** *(Documented for configuration hygiene).*

---

### Finding A-017: Missing Custom Branded HTTP Error Pages (404, 419, 429, 500)
- **Severity:** **LOW**
- **Category:** User Experience
- **File & Line(s):** `resources/views/errors/` (Missing directory)
- **Evidence:** Directory `resources/views/errors/` does not exist.
- **Why It Matters:** When a customer encounters a non-existent URL (404), an expired session (419), a throttled action (429), or a server exception (500), Laravel falls back to default unbranded framework error pages. Customers lose shop navigation and may assume the store is offline.
- **Recommended Fix:** Create customized Blade views in `resources/views/errors/` (`404.blade.php`, `419.blade.php`, `429.blade.php`, `500.blade.php`) extending `layouts.storefront`.
- **Effort:** M
- **Status:** **OPEN** *(Design and user experience enhancement).*

---

### Finding A-018: Phone Catalog Seeders Use USD Pricing (Rs 150 - Rs 1700) Instead of NPR Values
- **Severity:** **INFO**
- **Category:** Seeders / Data Realism
- **File & Line(s):**
  - `database/seeders/IphoneCatalogSeeder.php:24-65`
  - `database/seeders/SamsungCatalogSeeder.php:23-31`
- **Evidence:**
```php
// In IphoneCatalogSeeder.php:
['name' => 'iPhone 15 Pro Max', 'base_price' => 1150],
['name' => 'iPhone 16 Pro Max', 'base_price' => 1200],

// In SamsungCatalogSeeder.php:
['name' => 'Samsung Galaxy S24 Ultra', 'base_price' => 1400],
```
- **Why It Matters:** Seeded prices represent USD dollar figures (150 to 1700) instead of realistic Nepali Rupees (NPR). When local developers seed the database, flagship phones appear in the storefront catalog for Rs 1,200 instead of Rs 150,000.
- **Recommended Fix:** Multiply base prices in seeders by 100 or convert to realistic market NPR prices (e.g. `base_price => 150000`).
- **Effort:** S
- **Status:** **OPEN** *(Seeder data realism).*
