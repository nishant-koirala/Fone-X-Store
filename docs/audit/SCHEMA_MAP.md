# Schema & Model Architectural Map

This document cross-references all database tables, columns, foreign keys, indexes, enums, Eloquent models, casts, fillable attributes, and bidirectional relationships across FoneX Store.

---

## 1. Table-by-Table Column & Model Attribute Matrix

### 1.1 `customers`
- **Migration Source:** `2026_08_07_000005_create_customers_table.php`, `2026_10_26_221000_add_unique_index_to_customers_phone.php`
- **Model:** `App\Models\Customer`
- **Columns:**
  | Column | DB Type | Nullable | Default | Model $fillable | Model $casts | Factory Default |
  | :--- | :--- | :---: | :--- | :---: | :--- | :--- |
  | `id` | `BIGINT UNSIGNED` | NO | Auto | NO | — | — |
  | `name` | `VARCHAR(255)` | NO | None | YES | `string` | `fake()->name()` |
  | `phone` | `VARCHAR(50)` | NO | None | YES | `string` | `98XXXXXXXX` |
  | `email` | `VARCHAR(255)` | YES | NULL | YES | `string` | `fake()->safeEmail()` |
  | `address` | `TEXT` | YES | NULL | YES | `string` | `Street, City, Nepal` |
  | `created_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
  | `updated_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
- **Indexes & Constraints:**
  - `PRIMARY KEY (id)`
  - `UNIQUE customers_phone_unique (phone)`
- **Relationships:**
  - `orders()`: `HasMany<Order>` (Defined)
  - `tradeInValuations()`: `HasMany<TradeInValuation>` (Defined)
- **Mismatches / Gotchas:**
  - `phone` column is `VARCHAR(50)`. While standard Nepali mobile numbers are 10 digits (`98XXXXXXXX`), the column allows up to 50 characters, though `NepaliPhone` validation rule normalizes inputs to 10 digits before saving.

---

### 1.2 `orders`
- **Migration Source:** `2026_08_07_000006_create_orders_table.php`, `2026_10_26_214001_add_user_id_and_shipping_snapshot_to_orders_table.php`, `2026_10_26_221001_add_soft_deletes_and_restocked_at.php`, `2026_10_26_224000_convert_enum_columns_to_strings.php`, `2026_11_02_160949_add_soft_deletes_to_orders_table.php`, `2026_11_02_165902_add_delivery_zone_to_orders_table.php`, `2026_11_03_093636_add_confirmed_at_to_orders_table.php`
- **Model:** `App\Models\Order`
- **Columns:**
  | Column | DB Type | Nullable | Default | Model $fillable | Model $casts | Factory Default |
  | :--- | :--- | :---: | :--- | :---: | :--- | :--- |
  | `id` | `BIGINT UNSIGNED` | NO | Auto | NO | — | — |
  | `customer_id` | `BIGINT UNSIGNED` | NO | None | YES | `integer` | `Customer::factory()` |
  | `user_id` | `BIGINT UNSIGNED` | YES | NULL | YES | `integer` | NULL |
  | `status` | `VARCHAR(20)` | NO | `'pending'` | YES | `OrderStatusEnum::class` | `'pending'` |
  | `delivery_zone` | `VARCHAR(50)` | NO | `'inside_valley'`| YES | `string` | `'inside_valley'` |
  | `total` | `DECIMAL(10,2)` | NO | `0.00` | YES | `decimal:2` | Random 30,000-250,000 |
  | `shipping_name` | `VARCHAR(255)` | YES | NULL | YES | `string` | `fake()->name()` |
  | `shipping_phone` | `VARCHAR(50)` | YES | NULL | YES | `string` | `98XXXXXXXX` |
  | `shipping_email` | `VARCHAR(255)` | YES | NULL | YES | `string` | `fake()->safeEmail()` |
  | `shipping_address` | `TEXT` | YES | NULL | YES | `string` | `Street, City, Nepal` |
  | `confirmed_at` | `TIMESTAMP` | YES | NULL | YES | `datetime` | NULL |
  | `restocked_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | NULL |
  | `deleted_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | NULL |
  | `created_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
  | `updated_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
- **Foreign Keys & Deletion Constraints:**
  - `FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE` (**CRITICAL DEFECT: A-002**)
  - `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL`
- **Indexes:**
  - `PRIMARY KEY (id)`
  - `INDEX orders_status_index (status)`
  - `INDEX orders_user_id_index (user_id)`
  - `INDEX orders_confirmed_at_index (confirmed_at)`
- **Relationships:**
  - `customer()`: `BelongsTo<Customer>` (Inverse of `Customer::orders()`)
  - `user()`: `BelongsTo<User>` (Inverse of `User::orders()`)
  - `items()`: `HasMany<OrderItem>` (Inverse of `OrderItem::order()`)
- **Mismatches / Gotchas:**
  - `customer_id` has `cascadeOnDelete()`. Deleting a customer record cascades and deletes all historical sales orders and invoices.
  - `restocked_at` is excluded from `$fillable` intentionally, updated via `forceFill()` during cancellation restock.

---

### 1.3 `order_items`
- **Migration Source:** `2026_08_07_000007_create_order_items_table.php`, `2026_10_26_221001_add_soft_deletes_and_restocked_at.php`, `2026_11_03_092754_add_cost_price_to_product_conditions_and_order_items.php`
- **Model:** `App\Models\OrderItem`
- **Columns:**
  | Column | DB Type | Nullable | Default | Model $fillable | Model $casts | Factory Default |
  | :--- | :--- | :---: | :--- | :---: | :--- | :--- |
  | `id` | `BIGINT UNSIGNED` | NO | Auto | NO | — | — |
  | `order_id` | `BIGINT UNSIGNED` | NO | None | YES | `integer` | `Order::factory()` |
  | `product_condition_id` | `BIGINT UNSIGNED` | NO | None | YES | `integer` | `ProductCondition::factory()` |
  | `quantity` | `INTEGER` | NO | None | YES | `integer` | 1-3 |
  | `price_at_purchase` | `DECIMAL(10,2)`| NO | None | YES | `decimal:2` | Random 35,000-200,000 |
  | `cost_price_at_purchase` | `DECIMAL(10,2)`| YES | NULL | YES | `decimal:2` | NULL / Condition cost |
  | `created_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
  | `updated_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
- **Foreign Keys & Deletion Constraints:**
  - `FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE`
  - `FOREIGN KEY (product_condition_id) REFERENCES product_conditions(id) ON DELETE RESTRICT` (Protected in migration `2026_10_26_221001`)
- **Relationships:**
  - `order()`: `BelongsTo<Order>`
  - `productCondition()`: `BelongsTo<ProductCondition>`
  - `deviceUnits()`: `HasMany<DeviceUnit>`
  - `deviceUnit()`: `HasOne<DeviceUnit>`

---

### 1.4 `product_conditions`
- **Migration Source:** `2026_08_07_000003_create_product_conditions_table.php`, `2026_08_09_065944_add_original_price_to_product_conditions_table.php`, `2026_10_26_221001_add_soft_deletes_and_restocked_at.php`, `2026_10_26_223000_add_performance_indexes.php`, `2026_10_26_224000_convert_enum_columns_to_strings.php`, `2026_11_03_092754_add_cost_price_to_product_conditions_and_order_items.php`
- **Model:** `App\Models\ProductCondition`
- **Columns:**
  | Column | DB Type | Nullable | Default | Model $fillable | Model $casts | Factory Default |
  | :--- | :--- | :---: | :--- | :---: | :--- | :--- |
  | `id` | `BIGINT UNSIGNED` | NO | Auto | NO | — | — |
  | `product_id` | `BIGINT UNSIGNED` | NO | None | YES | `integer` | `Product::factory()` |
  | `grade` | `VARCHAR(10)` | NO | `'new'` | YES | `GradeEnum::class` | `'new'`, `'A'`, `'B'`, `'C'` |
  | `price` | `DECIMAL(10,2)` | NO | None | YES | `decimal:2` | NPR 20,000-220,000 |
  | `cost_price` | `DECIMAL(10,2)` | YES | NULL | YES | `decimal:2` | NULL |
  | `original_price` | `DECIMAL(10,2)` | YES | NULL | YES | `decimal:2` | `price * 1.10` |
  | `quantity_in_stock` | `INTEGER` | NO | `0` | YES | `integer` | 3-20 |
  | `deleted_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | NULL |
  | `created_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
  | `updated_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
- **Foreign Keys & Deletion Constraints:**
  - `FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE`
- **Indexes:**
  - `PRIMARY KEY (id)`
  - `INDEX product_conditions_product_id_index (product_id)`
  - `INDEX product_conditions_grade_index (grade)`
  - `INDEX product_conditions_stock_index (quantity_in_stock)`
  - `INDEX idx_prod_cond_perf (product_id, grade, quantity_in_stock)`
- **Relationships:**
  - `product()`: `BelongsTo<Product>`
  - `stockMovements()`: `HasMany<StockMovement>`
  - `orderItems()`: `HasMany<OrderItem>`
  - `deviceUnits()`: `HasMany<DeviceUnit>`
  - `availableDeviceUnits()`: `HasMany<DeviceUnit>` (scoped to `status = 'available'`)
- **Mismatches / Gotchas:**
  - `quantity_in_stock` is a signed `INTEGER` without an unsigned constraint or database check constraint (`quantity_in_stock >= 0`). If concurrent operations bypass in-memory validation, stock can become negative. (**Finding A-013**).

---

### 1.5 `device_units`
- **Migration Source:** `2026_11_03_094246_create_device_units_table.php`
- **Model:** `App\Models\DeviceUnit`
- **Columns:**
  | Column | DB Type | Nullable | Default | Model $fillable | Model $casts | Factory Default |
  | :--- | :--- | :---: | :--- | :---: | :--- | :--- |
  | `id` | `BIGINT UNSIGNED` | NO | Auto | NO | — | — |
  | `product_condition_id` | `BIGINT UNSIGNED` | NO | None | YES | `integer` | `ProductCondition::factory()` |
  | `imei` | `VARCHAR(25)` | YES | NULL | YES | `string` | 15-digit number string |
  | `serial_number` | `VARCHAR(50)` | YES | NULL | YES | `string` | Alphanumeric string |
  | `battery_health` | `TINYINT UNSIGNED`| YES | NULL | YES | `integer` | 80-100 |
  | `cost_price` | `DECIMAL(10,2)` | YES | NULL | YES | `decimal:2` | 25,000-90,000 |
  | `status` | `VARCHAR(20)` | NO | `'available'` | YES | `DeviceUnitStatusEnum::class` | `'available'` |
  | `order_item_id` | `BIGINT UNSIGNED` | YES | NULL | YES | `integer` | NULL |
  | `trade_in_valuation_id` | `BIGINT UNSIGNED` | YES | NULL | YES | `integer` | NULL |
  | `notes` | `TEXT` | YES | NULL | YES | `string` | Diagnostic text |
  | `sold_at` | `TIMESTAMP` | YES | NULL | YES | `datetime` | NULL |
  | `created_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
  | `updated_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
- **Foreign Keys & Deletion Constraints:**
  - `FOREIGN KEY (product_condition_id) REFERENCES product_conditions(id) ON DELETE CASCADE`
  - `FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE SET NULL`
  - `FOREIGN KEY (trade_in_valuation_id) REFERENCES trade_in_valuations(id) ON DELETE SET NULL`
- **Indexes:**
  - `PRIMARY KEY (id)`
  - `INDEX device_units_imei_index (imei)`
  - `INDEX device_units_serial_number_index (serial_number)`
  - `INDEX device_units_status_index (status)`
- **Relationships:**
  - `productCondition()`: `BelongsTo<ProductCondition>`
  - `orderItem()`: `BelongsTo<OrderItem>`
  - `tradeInValuation()`: `BelongsTo<TradeInValuation>`
- **Mismatches / Gotchas:**
  - `order_item_id` is never populated during customer storefront checkout (`PlaceOrder`) or admin order creation (`CreateOrder`). The link between sold orders and physical serialized units is completely severed in production workflows. (**Finding A-003**).

---

### 1.6 `trade_in_valuations`
- **Migration Source:** `2026_08_07_000008_create_trade_in_valuations_table.php`, `2026_10_26_214002_add_user_id_to_trade_in_valuations_table.php`, `2026_10_26_224000_convert_enum_columns_to_strings.php`, `2026_11_03_093223_add_product_condition_id_to_trade_in_valuations_table.php`
- **Model:** `App\Models\TradeInValuation`
- **Columns:**
  | Column | DB Type | Nullable | Default | Model $fillable | Model $casts | Factory Default |
  | :--- | :--- | :---: | :--- | :---: | :--- | :--- |
  | `id` | `BIGINT UNSIGNED` | NO | Auto | NO | — | — |
  | `customer_id` | `BIGINT UNSIGNED` | YES | NULL | YES | `integer` | `Customer::factory()` |
  | `product_condition_id` | `BIGINT UNSIGNED` | YES | NULL | YES | `integer` | NULL |
  | `user_id` | `BIGINT UNSIGNED` | YES | NULL | YES | `integer` | NULL |
  | `device_brand` | `VARCHAR(100)` | NO | None | YES | `string` | Apple / Samsung |
  | `device_model` | `VARCHAR(255)` | NO | None | YES | `string` | iPhone 12 / S21 |
  | `condition_description`| `TEXT` | NO | None | YES | `string` | Condition summary |
  | `estimated_value` | `DECIMAL(10,2)`| YES | NULL | YES | `decimal:2` | NULL / 45,000 |
  | `status` | `VARCHAR(20)` | NO | `'pending'` | YES | `TradeInStatusEnum::class` | `'pending'` |
  | `stock_ingested_at` | `TIMESTAMP` | YES | NULL | YES | `datetime` | NULL |
  | `created_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
  | `updated_at` | `TIMESTAMP` | YES | NULL | NO | `datetime` | Managed by Eloquent |
- **Foreign Keys & Deletion Constraints:**
  - `FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL`
  - `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL`
  - `FOREIGN KEY (product_condition_id) REFERENCES product_conditions(id) ON DELETE SET NULL`
- **Indexes:**
  - `PRIMARY KEY (id)`
  - `INDEX trade_in_valuations_status_index (status)`
- **Relationships:**
  - `customer()`: `BelongsTo<Customer>`
  - `user()`: `BelongsTo<User>`
  - `productCondition()`: `BelongsTo<ProductCondition>`
  - `deviceUnit()`: `HasOne<DeviceUnit>`

---

### 1.7 `products` & `categories`
- **Models:** `App\Models\Product`, `App\Models\Category`
- **Relationships:**
  - `Product::category()`: `BelongsTo<Category>`
  - `Category::products()`: `HasMany<Product>`
  - `Product::conditions()`: `HasMany<ProductCondition>`
  - `Product::reviews()`: `HasMany<Review>`
- **Mismatches / Gotchas:**
  - `products` table has `softDeletes()`. Deleting a product soft-deletes the product row, but `product_conditions` are also soft-deleted. Hard-deleting a product or condition cascades or restricts depending on migration history.

---

## 2. Foreign Key Cascade & Deletion Risk Analysis

| Foreign Key | Parent Table | Child Table | On Delete Rule | Behavioral Consequence & Risk Assessment |
| :--- | :--- | :--- | :--- | :--- |
| `customer_id` | `customers` | `orders` | **CASCADE** | **CRITICAL DATA LOSS RISK (A-002)**: Admin deleting a customer record immediately purges all customer order history, sales transactions, and invoice items. |
| `order_id` | `orders` | `order_items` | **CASCADE** | Expected: Deleting an order deletes its line items. Guarded by soft deletes on `orders`. |
| `product_condition_id` | `product_conditions` | `order_items` | **RESTRICT** | Safe: Hard deleting a product condition is rejected by DB if sales orders exist. |
| `product_condition_id` | `product_conditions` | `device_units` | **CASCADE** | Destructive: Deleting a condition record cascades to delete physical device units and historical IMEIs. |
| `order_item_id` | `order_items` | `device_units` | **SET NULL** | Acceptable: Deleting an order item unlinks unit, preserving serialized device record. |
| `trade_in_valuation_id` | `trade_in_valuations` | `device_units` | **SET NULL** | Orphan risk: Deleting a trade-in clears `trade_in_valuation_id` on the unit, losing device origin audit trail. |
| `customer_id` | `customers` | `trade_in_valuations`| **SET NULL** | Safe: Deleting customer leaves valuation history intact with `customer_id = null`. |

---

## 3. Enum Consistency Matrix

| Enum Class | Backed Cases | Migrations Storage | Form / Validation Rules | Filament Form Options | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| `DeviceUnitStatusEnum` | `available`, `reserved`, `sold`, `defective`, `returned` | `VARCHAR(20)` default `'available'` | Scoped in model queries | `DeviceUnitStatusEnum::class` | **CONSISTENT** |
| `GradeEnum` | `new`, `A`, `B`, `C` | `VARCHAR(10)` default `'new'` | Checked in `ProductController` (`in_array`) | `GradeEnum::class` | **CONSISTENT** |
| `OrderStatusEnum` | `pending`, `paid`, `fulfilled`, `cancelled` | `VARCHAR(20)` default `'pending'` | Checked in Order booted hook | `OrderStatusEnum::class` | **CONSISTENT** |
| `StockMovementTypeEnum` | `restock`, `sale`, `exchange_in`, `adjustment` | `VARCHAR(20)` | Enforced in `recordStockMovement()` | Used in Filament forms | **CONSISTENT** |
| `TradeInStatusEnum` | `pending`, `reviewed`, `completed` | `VARCHAR(20)` default `'pending'` | Checked in `ingestIntoStock` | `TradeInStatusEnum::class` | **CONSISTENT** |

---

## 4. Query & Eager Loading Analysis (N+1 Risks)

1. **`PlaceOrder::execute()`**: Eager loads `with('product')` when locking conditions: `ProductCondition::with('product')->whereIn('id', $conditionIds)->lockForUpdate()->get()`. Safe.
2. **`ProductController::index()`**: Eager loads `with(['product' => ..., 'product.category'])`. Safe.
3. **`CartService::getItems()`**: Eager loads `with(['product', 'product.category'])`. Safe.
4. **`OrderResource::table()`**:
   - Column `Tables\Columns\TextColumn::make('gross_profit')` invokes `$record->calculateProfit()`.
   - Inside `calculateProfit()`, it calls `$this->loadMissing('items')`.
   - On the Filament orders list page with 25 records, if `items` is not eagerly loaded by the Filament table query, this executes 25 individual queries (`N+1`).
   - **Recommendation:** Add `->modifyQueryUsing(fn ($query) => $query->with('items'))` to `OrderResource::table()`.
