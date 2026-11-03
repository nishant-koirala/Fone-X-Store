# Exception & Error-Path Architecture Map

This document exhaustively audits all exception classes, `try/catch` handlers, database transactions, `Halt` rollbacks, `abort()` invocations, query failure endpoints, and failure-path side effects across the FoneX Store codebase.

---

## 1. Custom Exceptions Map

| Exception Class | Defined At | Thrown Where | Catching Location | User / Admin Experience |
| :--- | :--- | :--- | :--- | :--- |
| `InsufficientStockException` | `app/Exceptions/InsufficientStockException.php` | 1. `PlaceOrder.php:30` (empty cart)<br>2. `PlaceOrder.php:49` (item missing)<br>3. `PlaceOrder.php:55` (product invisible)<br>4. `PlaceOrder.php:63` (quantity exceeds stock) | `CheckoutController.php:75` | User redirected back to checkout form with input preserved; flash danger alert displays exact message: *"Stock for [Phone] (Grade [X]) is insufficient. Only N items remaining in stock."* |

---

## 2. Standard PHP & Domain Exceptions Map

| Exception Class | Thrown Where | Catching Location | Impact & Visibility |
| :--- | :--- | :--- | :--- |
| `InvalidArgumentException` | `ProductCondition.php:109` (quantity = 0)<br>`ProductCondition.php:113` (sale quantity >= 0)<br>`ProductCondition.php:117` (restock quantity <= 0)<br>`ProductCondition.php:121` (stock < 0) | Uncaught in callers (`CreateStockMovement`, internal commands) | In Filament, causes unhandled 500 error toast/page; in storefront, caught by `CheckoutController::store` generic `\Throwable` handler returning *"An unexpected error occurred..."*. |
| `Filament\Support\Exceptions\Halt` | `CreateOrder.php:66`<br>`CreateOrder.php:83` | Filament internal form lifecycle (`CreateRecord::create()`) | Rolls back outer Filament database transaction; displays red notification toast (`Insufficient Stock` or `Stock Error`); prevents invalid order row and items from saving. |

---

## 3. Comprehensive `try/catch` Audit

| File & Line | Caught Type | Actions Inside Catch Block | Side Effects / Swallowed Risks | Status |
| :--- | :--- | :--- | :--- | :---: |
| `TradeInController.php:108` | `\Throwable $mailException` | `Log::error("Failed sending trade-in notification emails...", ['exception' => $mailException])` | **Mail Resilience:** Non-fatal. Does not interrupt customer redirect to confirmation screen. Trade-in is successfully recorded in DB. | **SAFE** |
| `CheckoutController.php:66` | `\Throwable $mailException` | `Log::error("Failed sending OrderPlaced email...", ['exception' => $mailException])` | **Mail Resilience:** Non-fatal. Customer checkout succeeds, inventory is deducted, session cart is cleared. Order is safe. | **SAFE** |
| `CheckoutController.php:75` | `InsufficientStockException $e` | `return back()->withInput()->with('error', $e->getMessage())` | Transaction rolls back automatically via `DB::transaction`. Form state preserved. | **SAFE** |
| `CheckoutController.php:77` | `\Throwable $e` | `Log::error('Order checkout failed: '.$e->getMessage(), ['payload' => $request->except(['_token'])]); return back()->withInput()->with('error', 'An unexpected error occurred...')` | **PII Security Risk (A-010):** Payload contains customer name, phone, email, and street address written in plaintext to `laravel.log`. | **WARNING** |
| `ConvertProductImagesToMedia.php:71`| `\Throwable $e` | `$this->error("Failed to convert image for Product #{$product->id}: {$e->getMessage()}")` | Non-fatal per-item catch. Continues looping through remaining products. | **SAFE** |

---

## 4. Database Transaction Boundaries & Mid-Flight Failure Analysis

### 4.1 `PlaceOrder::execute()` (`DB::transaction`)
- **Boundary:** Lines 33–145 of `app/Actions/Checkout/PlaceOrder.php`.
- **Operations:**
  1. Row lock via `lockForUpdate()` on all `ProductCondition` models in cart.
  2. Stock availability re-verification.
  3. Customer find or create (`Customer::firstOrCreate`).
  4. Order row insertion (`Order::create`).
  5. Line item insertions (`$order->items()->create()`).
  6. Atomic stock deductions via `recordStockMovement(SALE, -$qty)`.
- **Mid-Flight Failure Analysis:** If any item fails stock validation or database constraint fails, `DB::transaction` rolls back completely. Zero orphaned orders, zero stock deductions occur.
- **Side Effect Audit:** Email dispatch (`Mail::to`) is positioned outside and after the transaction in `CheckoutController.php:65`. No phantom emails sent on rollback.

### 4.2 `Order::booted()` Updated Hook (`DB::transaction`)
- **Boundary:** Lines 43–57 of `app/Models/Order.php`.
- **Operations:** Iterates over `$order->items` and invokes `recordStockMovement(RESTOCK, $item->quantity)`. Sets `restocked_at = now()`.
- **Failure Risk:** If an exception occurs midway during restock (e.g. database lock), transaction rolls back and `restocked_at` remains `null`. However, if staff changes status back to `PENDING` or `PAID`, no re-deduction occurs (**Finding A-007**).

### 4.3 `TradeInValuation::ingestIntoStock()` (`DB::transaction`)
- **Boundary:** Lines 76–110 of `app/Models/TradeInValuation.php`.
- **Operations:** Calls `recordStockMovement(EXCHANGE_IN, 1)`, updates condition `cost_price` if null, creates `DeviceUnit`, updates trade-in to `COMPLETED` with `stock_ingested_at = now()`.
- **Concurrency Defect (A-005):** The idempotency check `if ($this->stock_ingested_at !== null)` occurs outside `DB::transaction` and without `lockForUpdate()`. Concurrent requests can enter the block simultaneously and double-ingest stock.

### 4.4 Filament `CreateOrder` Outer Transaction
- **Boundary:** Filament's `AdminPanelProvider` has `->databaseTransactions()` enabled.
- **Operations:** Creates order row, persists repeater items, executes `CreateOrder::afterCreate()`.
- **Failure Handling:** If locked stock check fails, throws `(new Halt)->rollBackDatabaseTransaction()`. Outer Filament transaction rolls back completely.

---

## 5. Unhandled Exceptions & Error-Escaping Endpoints

| Location | Method / Operation | Potential Exception | Escape Consequence |
| :--- | :--- | :--- | :--- |
| `CartController.php:35,77` | `ProductCondition::findOrFail()` | `ModelNotFoundException` | Throws HTTP 404. Handled by Laravel default error handler. |
| `BlogController.php:25` | `Post::where(...)->firstOrFail()` | `ModelNotFoundException` | Throws HTTP 404. |
| `CreateStockMovement.php:16`| `ProductCondition::findOrFail()` | `ModelNotFoundException` | In Filament admin, displays 404 error page. |
| `TradeInValuationResource:157`| `ProductCondition::findOrFail()`| `ModelNotFoundException` | In Filament modal action, displays error toast. |
| `ProductController.php:103` | `abort_if(! $product->is_visible, 404)` | `NotFoundHttpException` | Throws HTTP 404. |
| `CheckoutController.php:94` | `session('last_order_id') != $order->id` | None (Redirect) | Redirects to `home` with warning. Safe against IDOR. |
| `TradeInController.php:124` | `session('last_valuation_id') != $valuation->id` | None (Redirect) | Redirects to `home` with warning. Safe against IDOR. |

---

## 6. HTTP Error Handling & User Experience

| HTTP Status | Condition | Handling Implementation | Audit Assessment |
| :---: | :--- | :--- | :--- |
| **404** | Route missing or `findOrFail()` on hidden product | Default Laravel error template | **MISSING CUSTOM VIEW (A-017):** `resources/views/errors/404.blade.php` does not exist; renders unstyled framework page. |
| **419** | CSRF token expiration on storefront forms | Default Laravel error template | **MISSING CUSTOM VIEW (A-017):** Renders raw "Page Expired" framework page. |
| **429** | Rate limit hit (`throttle:5,1` on checkout, `throttle:3,1` on reviews/trade-in) | Default Laravel error template | **MISSING CUSTOM VIEW (A-017):** Renders raw "Too Many Requests" framework page. |
| **500** | Uncaught server/database exception | Default Laravel error template | **MISSING CUSTOM VIEW (A-017):** `resources/views/errors/500.blade.php` does not exist. |

---

## 7. Console Commands Exit Code & Failure Handling

| Command | Signature | Normal Exit Code | Failure Exit Code | Failure Behavior |
| :--- | :--- | :---: | :---: | :--- |
| `ExpireUnconfirmedOrders` | `shop:expire-unconfirmed-orders` | `0` (`self::SUCCESS`) | `0` | If no orders found or after auto-cancellation, returns 0. Any database failure bubbles up to scheduler. |
| `ConvertProductImagesToMedia`| `products:convert-images` | `0` (`self::SUCCESS`) | `0` | Traps individual image conversion exceptions in `try/catch`, logs to console, and continues. Returns 0. |
| `MakeAdmin` | `user:make-admin {email}` | `0` (`self::SUCCESS`) | `1` (`self::FAILURE`) | If user email not found, prints error message and returns `1`. |
