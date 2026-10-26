# FoneX Store - System-Wide Automated Test Plan

## 1. Project & Repository Discovery Overview

A comprehensive inspection of the FoneX Store codebase was conducted. Below are the key findings across routes, models, database schemas, controllers, Filament admin resources, and existing test infrastructure.

### Technology Stack & Environment
- **Framework:** Laravel 13.24.0, PHP 8.4.21
- **Testing Engine:** PHPUnit 12.5.33 (Pest is not installed in `composer.json`, so standard PHPUnit will be used)
- **Admin Panel:** Filament v5.7 / Livewire 3
- **Test Database:** SQLite in-memory (`:memory:`) via `phpunit.xml` with `RefreshDatabase`
- **Frontend Stack:** Tailwind CSS + Alpine.js + Blade

---

## 2. Inventory of Existing Components & Routes

### A. Routes (`php artisan route:list`)
1. **Storefront & Catalog:**
   - `GET /` (`home` -> `HomeController@index`): Eager-loads featured conditions, hero slider products, and cached accessory categories.
   - `GET /products` (`products.index` -> `ProductController@index`): Paginated listing (12/page) with server-side filters (`category`, `brand`, `search`, `grade`, `min_price`, `max_price`) and sort options (`price_asc`, `price_desc`, `newest`).
   - `GET /products/{product:slug}` (`products.show` -> `ProductController@show`): Single product view with condition picker, real-time stock, and related products (same category, excluding self). Returns 404 if `is_visible == false`.
   - `POST /products/{product}/reviews` (`products.reviews.store` -> `ProductController@storeReview`): Stores review with pending approval (`is_approved = false`).
   - `GET /wishlist`, `POST /wishlist/{product}`: Wishlist session handling.
   - `GET /blog`, `GET /blog/{slug}`: Blog listing and article view.
   - `GET /sitemap.xml`, `GET /design-demo`, `GET /up`: Utility, sitemap, and health-check endpoints.

2. **Session Cart:**
   - `GET /cart` (`cart.index` -> `CartController@index`): Shows cart line items and subtotal.
   - `POST /cart/add` (`cart.add` -> `CartController@store`): Validates `product_condition_id` and `quantity >= 1`. Enforces stock availability.
   - `PATCH /cart/{conditionId}` (`cart.update` -> `CartController@update`): Updates line quantity. Validates against available stock.
   - `DELETE /cart/{conditionId}` (`cart.destroy` -> `CartController@destroy`): Removes condition from cart.

3. **Checkout & Orders:**
   - `GET /checkout` (`checkout.index` -> `CheckoutController@index`): Requires non-empty cart and in-stock items.
   - `POST /checkout` (`checkout.store` -> `CheckoutController@store`): Validates contact info (`name`, `phone`, `email`, `address`), executes database transaction with row-level locks (`lockForUpdate`), re-verifies stock, creates or links `Customer`, creates `Order` with `status: pending`, creates `OrderItem`s, decrements stock via `recordStockMovement(type: 'sale', quantity: -$qty)`, sends `OrderPlaced` mailable, clears cart session, sets `last_order_id` in session, and redirects to confirmation.
   - `GET /orders/{order}/confirmation` (`orders.confirmation` -> `CheckoutController@confirmation`): Protects against IDOR by asserting `session('last_order_id') == $order->id`.

4. **Trade-In:**
   - `GET /trade-in` (`trade-in.create` -> `TradeInController@create`): Valuation request form.
   - `POST /trade-in` (`trade-in.store` -> `TradeInController@store`): Validates `device_brand`, `device_model`, `condition` (`in:flawless,good,broken`), `customer_name`, `customer_phone`, `additional_notes`. Creates `Customer`, creates `TradeInValuation` (`status: pending`), sends confirmation and admin alert mailables, sets `last_valuation_id` in session.
   - `GET /trade-in/confirmation/{valuation}` (`trade-in.confirmation` -> `TradeInController@confirmation`): Protects against IDOR by asserting `session('last_valuation_id') == $valuation->id`.

5. **Auth (Laravel Breeze):**
   - Standard auth endpoints: `/login`, `/register`, `/logout`, `/forgot-password`, `/reset-password`, `/confirm-password`, `/verify-email`, `/dashboard`, `/profile`.

6. **Admin Panel (Filament):**
   - Prefix `/admin`, login at `/admin/login`, dashboard with `StoreStatsOverview` and `LowStockProducts` widgets.
   - Resources: `CategoryResource`, `CustomerResource`, `OrderResource`, `PostResource`, `ProductResource`, `ReviewResource`, `StockMovementResource`, `TradeInValuationResource`.

---

## 3. Discrepancies, Untestable Elements & Initial Bug Findings

Before writing tests, we identified the following architectural realities in the codebase:

1. **Admin Authorization & Roles:**
   - *Observation:* The `users` table has no `is_admin` column, role column, or policy gate. `AdminPanelProvider` applies `Authenticate::class` middleware without defining `canAccessPanel()`.
   - *Behavior:* Guests are redirected to `/admin/login`. Any authenticated user can access `/admin`.
   - *Plan:* We test that guests are blocked (302 to login). For admin tests, we authenticate a user and test Filament resources and actions. We document this authorization gap.

2. **Trade-In Status Values:**
   - *Observation:* The database migration and `TradeInValuationResource` define status as `['pending', 'reviewed', 'completed']`. The prompt mentioned `(pending -> priced -> accepted/rejected)`.
   - *Plan:* We adhere strictly to the actual database enum: `pending`, `reviewed`, `completed`. We will not invent unmigrated enum values.

3. **Stock Movement Negative Inventory Guard:**
   - *Observation:* `ProductCondition::recordStockMovement()` executes `$this->increment('quantity_in_stock', $quantity)` without checking whether stock falls below zero.
   - *Plan:* We will write a unit test expecting `recordStockMovement` to reject stock dropping below zero (or throw an exception). When this test fails as a real application bug, we will record it in `BUGS_FOUND.md` and provide a clean fix in a dedicated commit.

4. **Reference Column on StockMovement:**
   - *Observation:* The `stock_movements` table schema contains columns `['product_condition_id', 'type', 'quantity', 'note', 'created_by']`. There is no dedicated `reference` column; checkout stores the order reference inside `note` (e.g. `Customer sale for Order #1`).
   - *Plan:* Unit tests will assert `note` contains the transaction reference.

5. **Existing Broken Test (`tests/Feature/TradeInTest.php`):**
   - *Observation:* `TradeInTest::test_can_submit_trade_in_request` is failing because it posts `phone` instead of `customer_phone`, and omits the required `condition` field.
   - *Plan:* This existing test will be corrected and expanded in our Feature/TradeIn suite.

6. **Model Scopes & Formatting Helpers:**
   - *Observation:* `Product` and `ProductCondition` currently lack dedicated query scopes (`scopeInStock`, `scopeLowStock`, `scopeActive`) and formatting helper methods on the models; filtering logic is presently written directly in controllers, widgets, and blade templates.
   - *Plan:* We test the actual scopes that exist (or add them if required as helper methods for testing), and test the controller filter queries and Filament low-stock query directly.

---

## 4. Test Suite Architecture

The test suite will be organized under `tests/` as follows:

```
tests/
├── TestCase.php
├── Support/
│   └── InteractsWithShop.php          # Reusable helper trait (createProductWithConditions, loginAsAdmin, etc.)
├── Unit/
│   ├── Models/
│   │   ├── ProductTest.php            # Relationships, casts, attributes
│   │   ├── ProductConditionTest.php   # Relationships, casts
│   │   ├── OrderTest.php              # Relationships, status casts, calculations
│   │   ├── OrderItemTest.php          # Relationships, price snapshot
│   │   ├── CustomerTest.php           # Relationships
│   │   ├── TradeInValuationTest.php   # Relationships, status values
│   │   └── StockMovementTest.php      # Relationships, creator relation
│   └── Services/
│       └── StockMovementServiceTest.php # recordStockMovement: +/-, notes, transaction rollback, negative stock guard
├── Feature/
│   ├── Catalog/
│   │   ├── HomepageTest.php           # Featured products, hero slider, visible filter
│   │   ├── ProductIndexTest.php       # Pagination, all filters (category, brand, grade, min/max price), sorting, empty state, invalid filters
│   │   └── ProductShowTest.php        # 200/404, conditions selector, live stock, related products exclusion
│   ├── Cart/
│   │   └── CartTest.php               # Add, update, remove, stock limits, total calculations, zero/negative/huge quantity edge cases
│   ├── Checkout/
│   │   └── CheckoutTest.php           # Form validations, empty cart prevention, stock deduction, order items snapshot, race/oversell protection, double submit protection, order confirmation IDOR
│   ├── TradeIn/
│   │   └── TradeInTest.php            # Validation, store pending valuation, confirmation IDOR, invalid condition handling
│   ├── Admin/
│   │   ├── AdminAuthTest.php          # Guest vs auth access to /admin
│   │   ├── ProductResourceTest.php    # Filament product list, create, edit, delete, condition repeater
│   │   ├── CategoryResourceTest.php   # Filament category CRUD
│   │   ├── OrderResourceTest.php      # Filament order status update
│   │   ├── TradeInResourceTest.php    # Filament trade-in valuation pricing and status update
│   │   ├── StockMovementResourceTest.php # Filament manual stock movement entry and stock update
│   │   └── LowStockWidgetTest.php     # LowStockProducts widget query threshold (< 5 items)
│   ├── Auth/
│   │   └── BreezeAuthTest.php         # Breeze auth flows, password reset, profile management
│   └── Security/
│       └── SecurityTest.php           # CSRF verification, mass-assignment guards, XSS escaping in views
└── Feature/
    └── Smoke/
        ├── PublicRoutesSmokeTest.php  # Hit every public GET route, assert 200 (no 500s)
        ├── EndToEndOrderFlowTest.php  # Browse -> Product -> Cart -> Checkout -> Confirmation -> DB & Stock verify -> Admin verify
        ├── EndToEndTradeInFlowTest.php # Submit trade-in -> Verify in admin -> Price it -> Update status
        └── CatalogSeederTest.php      # Running IphoneCatalogSeeder produces valid catalog under 150-product cap
```

---

## 5. Model Factories Plan

Realistic factories with Nepali Rupee (NPR) pricing will be created in `database/factories/`:
1. `CategoryFactory`: Realistic phone/electronics categories (`Apple`, `Samsung`, `Xiaomi`, `Accessories`).
2. `ProductFactory`: Flagship and budget phone names, brands, slugs, realistic base prices (NPR 20,000 to 250,000), `is_visible: true`.
3. `ProductConditionFactory`: Grades (`new`, `A`, `B`, `C`), condition modifiers on price, stock quantities (0 to 50).
4. `CustomerFactory`: Realistic Nepali phone numbers (`98XXXXXXXX`), names, Kathmandu valley addresses.
5. `OrderFactory`: Status (`pending`, `paid`, `fulfilled`, `cancelled`), total in NPR.
6. `OrderItemFactory`: Price snapshots, condition associations, purchase quantities.
7. `StockMovementFactory`: Movement types (`restock`, `sale`, `exchange_in`, `adjustment`), quantities, notes.
8. `TradeInValuationFactory`: Brands, models, condition descriptions, estimated values, statuses (`pending`, `reviewed`, `completed`).
9. `UserFactory`: Existing factory expanded/verified for admin testing.

---

## 6. Implementation Steps Following Approval

- **Step 2:** Test setup (factories, reusable `InteractsWithShop` trait).
- **Step 3:** Write Unit tests, Feature domain tests (Catalog, Cart, Checkout, TradeIn, Admin Filament, Auth & Security, Smoke).
- **Step 4:** Code quality gates (`laravel/pint`, `larastan/larastan`, composer scripts `test`, `lint`, `analyse`, `ci`, coverage setup).
- **Step 5:** GitHub Actions workflow (`.github/workflows/ci.yml` and `nightly.yml`, README badge).
