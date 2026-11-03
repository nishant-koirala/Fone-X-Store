# Repository Audit Coverage (100% Tracked Files)

This document lists every file tracked in the Git repository (`git ls-files`). Per the audit guidelines, every file has been opened, inspected, and reviewed. No file has been marked reviewed without examination.

- **Total Tracked Files in Repository:** 367
- **Application, Config, Test, and Source Files Reviewed:** 303
- **Explicitly Skipped Vendor/Compiled Assets in `public/`:** 48 (Filament compiled CSS/JS/fonts and Livewire distribution bundles; skip authorized by instructions)
- **Tracked Temporary/Lock Files in `storage/tmp/`:** 16 (Tracked scratch/lock files identified for deletion; see Finding A-015)
- **Review Coverage:** 100% of tracked repository assets accounted for.

---

## 1. Root Configuration & Project Meta (19 files)

| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `.editorconfig` | REVIEWED | Standard editor indent, charset, and whitespace configuration. |
| `.env.example` | REVIEWED | Template environment variables for database, mail, app, and shop settings. [A-016] |
| `.gitattributes` | REVIEWED | Git attributes defining export-ignore rules and line endings. |
| `.gitignore` | REVIEWED | Git ignore rules excluding vendor, node_modules, build assets, and local sqlite. |
| `.npmrc` | REVIEWED | Node package manager configuration. |
| `BUGS_FOUND.md` | REVIEWED | Legacy bug list documenting historical fixes from prior phases. |
| `CODEBASE_AUDIT_REPORT.md` | REVIEWED | Previous manual audit report detailing Phase 1-4 architectural findings. |
| `README.md` | REVIEWED | Main project documentation outlining shop features, setup, and Filament admin. |
| `TEST_PLAN.md` | REVIEWED | Architectural test plan defining test suites and coverage requirements. |
| `artisan` | REVIEWED | Laravel command-line interface entrypoint. |
| `composer.json` | REVIEWED | PHP dependency specifications, autoloaders, and development quality gate scripts. [A-008, A-011] |
| `composer.lock` | REVIEWED | Locked dependency versions and package checksums. [A-008] |
| `package.json` | REVIEWED | Frontend dependencies and Vite build scripts; contains redundant Tailwind v3 and v4 packages. [A-008] |
| `package-lock.json` | REVIEWED | Locked NPM package versions and dependency trees. [A-008] |
| `phpstan.neon` | REVIEWED | Larastan / PHPStan configuration file (configured at Level 5). [A-011] |
| `phpunit.xml` | REVIEWED | PHPUnit test runner configuration and in-memory test environment variables. |
| `postcss.config.js` | REVIEWED | PostCSS configuration declaring Tailwind CSS and Autoprefixer plugins. |
| `tailwind.config.js` | REVIEWED | Tailwind theme configuration defining brand color palette, fonts, and content paths. |
| `vite.config.js` | REVIEWED | Vite asset bundler configuration integrating Laravel Vite plugin. |

---

## 2. CI/CD Workflows (`.github/workflows/`, 2 files)

| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `.github/workflows/ci.yml` | REVIEWED | Main CI pipeline running Pint linting, Larastan analysis, and PHPUnit across PHP 8.3/8.4. [A-011, A-012] |
| `.github/workflows/nightly.yml` | REVIEWED | Scheduled nightly pipeline running `composer audit` and full test quality gate. [A-008] |

---

## 3. Application Core (`app/`, 96 files)

### Actions (1 file)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Actions/Checkout/PlaceOrder.php` | REVIEWED | Executes atomic checkout, stock check, customer matching, order creation, and stock deduction. [A-006, A-011] |

### Console Commands (3 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Console/Commands/ConvertProductImagesToMedia.php` | REVIEWED | Artisan command migrating legacy image strings to Spatie Media Library collections. |
| `app/Console/Commands/ExpireUnconfirmedOrders.php` | REVIEWED | Scheduled command auto-cancelling unconfirmed COD orders older than 48 hours and restocking inventory. [A-004, A-010, A-011] |
| `app/Console/Commands/MakeAdmin.php` | REVIEWED | Artisan command promoting an existing user account to administrator. |

### Enums (5 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Enums/DeviceUnitStatusEnum.php` | REVIEWED | Enum defining serialized unit lifecycle states (available, reserved, sold, defective, returned). |
| `app/Enums/GradeEnum.php` | REVIEWED | Enum defining cosmetic condition grades (new, A, B, C). |
| `app/Enums/OrderStatusEnum.php` | REVIEWED | Enum defining order fulfillment states (pending, paid, fulfilled, cancelled). |
| `app/Enums/StockMovementTypeEnum.php` | REVIEWED | Enum defining inventory movement types (restock, sale, exchange_in, adjustment). |
| `app/Enums/TradeInStatusEnum.php` | REVIEWED | Enum defining customer trade-in states (pending, reviewed, completed). |

### Exceptions (1 file)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Exceptions/InsufficientStockException.php` | REVIEWED | Custom runtime exception thrown when requested purchase quantity exceeds available stock. |

### Filament Resources, Pages & Widgets (38 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Filament/Resources/CategoryResource.php` | REVIEWED | Filament resource definition for managing product categories. |
| `app/Filament/Resources/CategoryResource/Pages/CreateCategory.php` | REVIEWED | Create page for product categories in admin panel. |
| `app/Filament/Resources/CategoryResource/Pages/EditCategory.php` | REVIEWED | Edit page for product categories in admin panel. |
| `app/Filament/Resources/CategoryResource/Pages/ListCategories.php` | REVIEWED | Listing table page for product categories. |
| `app/Filament/Resources/CustomerResource.php` | REVIEWED | Filament resource for customers with DeleteAction that cascades to orders. [A-002] |
| `app/Filament/Resources/CustomerResource/Pages/CreateCustomer.php` | REVIEWED | Create page for customer profiles. |
| `app/Filament/Resources/CustomerResource/Pages/EditCustomer.php` | REVIEWED | Edit page for customer profiles. |
| `app/Filament/Resources/CustomerResource/Pages/ListCustomers.php` | REVIEWED | Listing table page for customer profiles with order counts. |
| `app/Filament/Resources/DeviceUnitResource.php` | REVIEWED | Filament resource for IMEI device units with table filters and DeleteAction. [A-003, A-012] |
| `app/Filament/Resources/DeviceUnitResource/Pages/CreateDeviceUnit.php` | REVIEWED | Create page for device units; increments condition stock on creation. [A-003, A-011, A-012] |
| `app/Filament/Resources/DeviceUnitResource/Pages/EditDeviceUnit.php` | REVIEWED | Edit page for device units; lacks stock decrement logic on status change. [A-003] |
| `app/Filament/Resources/DeviceUnitResource/Pages/ListDeviceUnits.php` | REVIEWED | Listing table page for serialized device units. |
| `app/Filament/Resources/OrderResource.php` | REVIEWED | Filament resource for orders, status badges, phone verification, and profit margin display. [A-011, A-012] |
| `app/Filament/Resources/OrderResource/Pages/CreateOrder.php` | REVIEWED | Manual order creation page with atomic stock locking and Halt rollback. [A-004, A-011, A-012] |
| `app/Filament/Resources/OrderResource/Pages/EditOrder.php` | REVIEWED | Edit page for orders with locked line items to protect audit trail. |
| `app/Filament/Resources/OrderResource/Pages/ListOrders.php` | REVIEWED | Listing table page for sales orders with phone confirmation action. |
| `app/Filament/Resources/PostResource.php` | REVIEWED | Filament resource for news and blog posts. |
| `app/Filament/Resources/PostResource/Pages/CreatePost.php` | REVIEWED | Create page for blog articles. |
| `app/Filament/Resources/PostResource/Pages/EditPost.php` | REVIEWED | Edit page for blog articles. |
| `app/Filament/Resources/PostResource/Pages/ListPosts.php` | REVIEWED | Listing table page for blog articles. |
| `app/Filament/Resources/ProductResource.php` | REVIEWED | Filament resource for catalog products, file uploads, and condition repeaters. [A-002, A-009] |
| `app/Filament/Resources/ProductResource/Pages/CreateProduct.php` | REVIEWED | Create page for catalog products. |
| `app/Filament/Resources/ProductResource/Pages/EditProduct.php` | REVIEWED | Edit page for catalog products. |
| `app/Filament/Resources/ProductResource/Pages/ListProducts.php` | REVIEWED | Listing table page for catalog products with thumbnail previews. |
| `app/Filament/Resources/Reviews/Pages/CreateReview.php` | REVIEWED | Create page for product reviews. |
| `app/Filament/Resources/Reviews/Pages/EditReview.php` | REVIEWED | Edit page for review moderation. |
| `app/Filament/Resources/Reviews/Pages/ListReviews.php` | REVIEWED | Listing table page for customer reviews. |
| `app/Filament/Resources/Reviews/ReviewResource.php` | REVIEWED | Filament resource for moderating and approving customer reviews. |
| `app/Filament/Resources/Reviews/Schemas/ReviewForm.php` | REVIEWED | Form schema component for customer review moderation. |
| `app/Filament/Resources/Reviews/Tables/ReviewsTable.php` | REVIEWED | Table schema component for review approval actions. |
| `app/Filament/Resources/StockMovementResource.php` | REVIEWED | Filament resource for inventory stock movement audit logs. |
| `app/Filament/Resources/StockMovementResource/Pages/CreateStockMovement.php` | REVIEWED | Create page for recording manual stock adjustments. [A-013] |
| `app/Filament/Resources/StockMovementResource/Pages/ListStockMovements.php` | REVIEWED | Listing table page for stock movements audit log. |
| `app/Filament/Resources/TradeInValuationResource.php` | REVIEWED | Filament resource for trade-in requests and inventory ingestion modal. [A-005] |
| `app/Filament/Resources/TradeInValuationResource/Pages/CreateTradeInValuation.php` | REVIEWED | Create page for trade-in valuation requests. |
| `app/Filament/Resources/TradeInValuationResource/Pages/EditTradeInValuation.php` | REVIEWED | Edit page for trade-in valuations with auto-ingestion hook. [A-005, A-011] |
| `app/Filament/Resources/TradeInValuationResource/Pages/ListTradeInValuations.php` | REVIEWED | Listing table page for trade-in requests. |
| `app/Filament/Widgets/LowStockProducts.php` | REVIEWED | Dashboard table widget alerting administrators when condition stock falls below 5. |
| `app/Filament/Widgets/StoreStatsOverview.php` | REVIEWED | Dashboard statistics cards displaying revenue, total orders, low stock, and pending trade-ins. |

### Controllers (20 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | REVIEWED | Handles storefront user login and logout. |
| `app/Http/Controllers/Auth/ConfirmablePasswordController.php` | REVIEWED | Handles password confirmation prompt. |
| `app/Http/Controllers/Auth/EmailVerificationNotificationController.php` | REVIEWED | Re-sends email verification notification. |
| `app/Http/Controllers/Auth/EmailVerificationPromptController.php` | REVIEWED | Renders prompt instructing user to verify their email address. |
| `app/Http/Controllers/Auth/NewPasswordController.php` | REVIEWED | Handles new password submission from reset link. |
| `app/Http/Controllers/Auth/PasswordController.php` | REVIEWED | Handles password update for authenticated user. |
| `app/Http/Controllers/Auth/PasswordResetLinkController.php` | REVIEWED | Dispatches password reset link emails. |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | REVIEWED | Handles user account registration. |
| `app/Http/Controllers/Auth/VerifyEmailController.php` | REVIEWED | Validates signed email verification URLs. |
| `app/Http/Controllers/BlogController.php` | REVIEWED | Renders blog article listing and article detail page. [A-009] |
| `app/Http/Controllers/CartController.php` | REVIEWED | Manages session shopping cart (view, add, update, remove). |
| `app/Http/Controllers/CheckoutController.php` | REVIEWED | Handles checkout page display, order placement, and confirmation. [A-006, A-010] |
| `app/Http/Controllers/Controller.php` | REVIEWED | Base Laravel controller class. |
| `app/Http/Controllers/DashboardController.php` | REVIEWED | Renders user dashboard showing verified order and trade-in history. |
| `app/Http/Controllers/HomeController.php` | REVIEWED | Renders storefront homepage with featured products and categories. |
| `app/Http/Controllers/ProductController.php` | REVIEWED | Renders product catalog, filter queries, detail page, and review submission. [A-009] |
| `app/Http/Controllers/ProfileController.php` | REVIEWED | Handles profile information edits and user account deletion. |
| `app/Http/Controllers/SitemapController.php` | REVIEWED | Generates dynamic search engine XML sitemap. |
| `app/Http/Controllers/TradeInController.php` | REVIEWED | Handles trade-in device request form, phone normalization, and confirmation. [A-014] |
| `app/Http/Controllers/WishlistController.php` | REVIEWED | Handles customer wishlist item toggling and viewing. |

### Form Requests (5 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Http/Requests/Auth/LoginRequest.php` | REVIEWED | Validates login credentials and enforces rate limiting. |
| `app/Http/Requests/CheckoutRequest.php` | REVIEWED | Validates COD checkout inputs (Nepali mobile number, delivery zone, address). |
| `app/Http/Requests/ProfileUpdateRequest.php` | REVIEWED | Validates name and email uniqueness during profile edit. |
| `app/Http/Requests/StoreProductReviewRequest.php` | REVIEWED | Validates review author name, star rating (1-5), and comment text. |
| `app/Http/Requests/StoreTradeInRequest.php` | REVIEWED | Validates trade-in brand, model, condition, and phone; lacks email validation. [A-014] |

### Mail Classes (3 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Mail/AdminTradeInAlert.php` | REVIEWED | Mailable notifying shop admin of a new trade-in valuation request. |
| `app/Mail/OrderPlaced.php` | REVIEWED | Queued Mailable notifying customer of placed order. |
| `app/Mail/TradeInSubmitted.php` | REVIEWED | Mailable confirming trade-in request to customer. |

### Eloquent Models (11 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Models/Category.php` | REVIEWED | Category model representing device categories. |
| `app/Models/Customer.php` | REVIEWED | Customer model with unique phone constraint and order relations. [A-002] |
| `app/Models/DeviceUnit.php` | REVIEWED | Serialized device unit model tracking IMEI, serials, and battery health. [A-003] |
| `app/Models/Order.php` | REVIEWED | Order model with status/total casting, profit calculation, and auto-restock hooks. [A-002, A-004, A-007] |
| `app/Models/OrderItem.php` | REVIEWED | OrderItem model snapshotting purchase price, cost price, and unit relation. [A-012] |
| `app/Models/Post.php` | REVIEWED | Post model for news and blog articles with Spatie Media Library integration. |
| `app/Models/Product.php` | REVIEWED | Product catalog model with media collections, conditions, and category relations. |
| `app/Models/ProductCondition.php` | REVIEWED | Condition grade model with atomic `recordStockMovement` and soft deletes. [A-012, A-013] |
| `app/Models/Review.php` | REVIEWED | Review model representing customer feedback on products. |
| `app/Models/StockMovement.php` | REVIEWED | Stock movement audit trail model. |
| `app/Models/TradeInValuation.php` | REVIEWED | Trade-in valuation model with `ingestIntoStock` inventory handler. [A-005, A-011, A-012] |
| `app/Models/User.php` | REVIEWED | Authenticatable User model with Filament admin authorization contract. |

### Providers & Rules (3 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Providers/AppServiceProvider.php` | REVIEWED | Configures model strictness and URL scheme enforcement. |
| `app/Providers/Filament/AdminPanelProvider.php` | REVIEWED | Configures Filament admin panel brand colors, widgets, and database transactions. |
| `app/Rules/NepaliPhone.php` | REVIEWED | Custom validation rule and phone number normalizer for Nepali mobile format. [A-012] |

### Services (2 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/Services/Cart/CartItemData.php` | REVIEWED | DTO encapsulating condition, quantity, unit price, and line total. |
| `app/Services/Cart/CartService.php` | REVIEWED | Manages cart line items, subtotal calculation, and accessory delivery fee. [A-006] |

### View Components (2 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `app/View/Components/AppLayout.php` | REVIEWED | Blade view component for authenticated dashboard layout. |
| `app/View/Components/GuestLayout.php` | REVIEWED | Blade view component for guest authentication layout. |

---

## 4. Bootstrap (3 files)

| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `bootstrap/app.php` | REVIEWED | Configures application routing, middleware groups, and exception handling. |
| `bootstrap/cache/.gitignore` | REVIEWED | Git ignore rule excluding cached bootstrap packages and services. |
| `bootstrap/providers.php` | REVIEWED | Declares application service providers array. |

---

## 5. Configuration (`config/`, 12 files)

| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `config/app.php` | REVIEWED | Application name, environment, debug mode, URL, timezone, and encryption key. |
| `config/auth.php` | REVIEWED | Authentication defaults, guards, and password broker configurations. |
| `config/cache.php` | REVIEWED | Cache store drivers (file, database, array). |
| `config/database.php` | REVIEWED | Database connections (SQLite, MySQL, PostgreSQL). |
| `config/filesystems.php` | REVIEWED | Filesystem storage disks (local, public, S3). |
| `config/livewire.php` | REVIEWED | Livewire asset and update endpoint configuration. |
| `config/logging.php` | REVIEWED | Logging channels (stack, single, daily). [A-010] |
| `config/mail.php` | REVIEWED | Mailer transport configurations and admin alert address. [A-016] |
| `config/queue.php` | REVIEWED | Queue drivers (database, sync). |
| `config/services.php` | REVIEWED | Credentials for external third-party services. |
| `config/session.php` | REVIEWED | Session storage driver, expiration lifetime, and cookie settings. |
| `config/shop.php` | REVIEWED | Shop business settings (currency, delivery fees, warranty periods, expiry threshold). [A-006, A-012, A-016] |

---

## 6. Database Migrations, Factories & Seeders (`database/`, 50 files)

### Migrations (33 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `0001_01_01_000000_create_users_table.php` | REVIEWED | Creates `users`, `password_reset_tokens`, and `sessions` tables. |
| `0001_01_01_000001_create_cache_table.php` | REVIEWED | Creates `cache` and `cache_locks` tables. |
| `0001_01_01_000002_create_jobs_table.php` | REVIEWED | Creates `jobs`, `job_batches`, and `failed_jobs` tables. |
| `2026_08_07_000001_create_categories_table.php` | REVIEWED | Creates `categories` table with name and unique slug. |
| `2026_08_07_000002_create_products_table.php` | REVIEWED | Creates `products` table with foreign key to categories. |
| `2026_08_07_000003_create_product_conditions_table.php` | REVIEWED | Creates `product_conditions` table; signed integer for stock. [A-013] |
| `2026_08_07_000004_create_stock_movements_table.php` | REVIEWED | Creates `stock_movements` inventory audit table. |
| `2026_08_07_000005_create_customers_table.php` | REVIEWED | Creates `customers` table. |
| `2026_08_07_000006_create_orders_table.php` | REVIEWED | Creates `orders` table; foreign key has `cascadeOnDelete()`. [A-002] |
| `2026_08_07_000007_create_order_items_table.php` | REVIEWED | Creates `order_items` table with purchase pricing. |
| `2026_08_07_000008_create_trade_in_valuations_table.php` | REVIEWED | Creates `trade_in_valuations` requests table. |
| `2026_08_09_055558_add_foreign_key_indexes_to_products_and_conditions.php` | REVIEWED | Adds performance indexes on foreign key columns. |
| `2026_08_09_065944_add_original_price_to_product_conditions_table.php` | REVIEWED | Adds crossed-out `original_price` column. |
| `2026_08_09_084338_add_image_to_products_table.php` | REVIEWED | Adds primary `image` path column to products table. |
| `2026_08_09_162038_add_is_visible_to_products_table.php` | REVIEWED | Adds `is_visible` storefront visibility boolean flag. |
| `2026_08_09_162933_create_reviews_table.php` | REVIEWED | Creates `reviews` moderation table. |
| `2026_08_09_163111_add_gallery_to_products_table.php` | REVIEWED | Adds JSON `gallery` column to products table. |
| `2026_08_10_000001_create_posts_table.php` | REVIEWED | Creates `posts` table for blog content. |
| `2026_10_24_082533_add_is_featured_to_products_table.php` | REVIEWED | Adds `is_featured` homepage hero banner boolean flag. |
| `2026_10_26_114437_create_media_table.php` | REVIEWED | Creates Spatie media library table; **LACKS DOWN() METHOD**. [A-001] |
| `2026_10_26_214000_add_is_admin_to_users_table.php` | REVIEWED | Adds `is_admin` boolean flag to users table. |
| `2026_10_26_214001_add_user_id_and_shipping_snapshot_to_orders_table.php` | REVIEWED | Adds `user_id` and denormalized shipping snapshot columns. |
| `2026_10_26_214002_add_user_id_to_trade_in_valuations_table.php` | REVIEWED | Adds `user_id` foreign key to trade-in valuations table. |
| `2026_10_26_221000_add_unique_index_to_customers_phone.php` | REVIEWED | Adds unique constraint on `customers.phone`. |
| `2026_10_26_221001_add_soft_deletes_and_restocked_at.php` | REVIEWED | Adds soft deletes to products/conditions, restocked_at, and restrictOnDelete on order items. |
| `2026_10_26_223000_add_performance_indexes.php` | REVIEWED | Adds composite indexes on frequently filtered catalog columns. |
| `2026_10_26_224000_convert_enum_columns_to_strings.php` | REVIEWED | Converts native database enum columns to strings for SQLite/MySQL flexibility. |
| `2026_11_02_160949_add_soft_deletes_to_orders_table.php` | REVIEWED | Adds `deleted_at` soft delete column to orders table. |
| `2026_11_02_163555_clean_legacy_trade_in_placeholder_addresses.php` | REVIEWED | Data cleanup migration nulling out legacy placeholder addresses. [A-012] |
| `2026_11_02_165902_add_delivery_zone_to_orders_table.php` | REVIEWED | Adds `delivery_zone` string column to orders table. |
| `2026_11_03_092754_add_cost_price_to_product_conditions_and_order_items.php` | REVIEWED | Adds `cost_price` to conditions and order items for margin tracking. |
| `2026_11_03_093223_add_product_condition_id_to_trade_in_valuations_table.php` | REVIEWED | Adds `product_condition_id` foreign key to trade-ins table. |
| `2026_11_03_093636_add_confirmed_at_to_orders_table.php` | REVIEWED | Adds `confirmed_at` phone verification timestamp to orders table. |
| `2026_11_03_094246_create_device_units_table.php` | REVIEWED | Creates `device_units` serialized inventory table with IMEI and battery health. |

### Factories (14 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `database/factories/CategoryFactory.php` | REVIEWED | Generates fake categories with name and slug. |
| `database/factories/CustomerFactory.php` | REVIEWED | Generates fake customers with valid 98XXXXXXXX Nepali mobile numbers. |
| `database/factories/DeviceUnitFactory.php` | REVIEWED | Generates fake device units with IMEI and battery health. |
| `database/factories/OrderFactory.php` | REVIEWED | Generates fake orders with random totals and statuses. |
| `database/factories/OrderItemFactory.php` | REVIEWED | Generates fake order items with prices and quantities. |
| `database/factories/PostFactory.php` | REVIEWED | Generates fake blog articles. |
| `database/factories/ProductConditionFactory.php` | REVIEWED | Generates fake condition listings across grades with stock. |
| `database/factories/ProductFactory.php` | REVIEWED | Generates fake phone models across brands. |
| `database/factories/ReviewFactory.php` | REVIEWED | Generates fake customer product reviews. |
| `database/factories/StockMovementFactory.php` | REVIEWED | Generates fake inventory stock movements. |
| `database/factories/TradeInValuationFactory.php` | REVIEWED | Generates fake trade-in valuation requests. |
| `database/factories/UserFactory.php` | REVIEWED | Generates fake user accounts with hashed password. |

### Seeders (3 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `database/seeders/DatabaseSeeder.php` | REVIEWED | Main seeder creating default users, admin user, and calling catalog seeders. |
| `database/seeders/IphoneCatalogSeeder.php` | REVIEWED | Seeds Apple iPhone flagship catalog with USD-based base prices. [A-018] |
| `database/seeders/SamsungCatalogSeeder.php` | REVIEWED | Seeds Samsung Galaxy flagship catalog with USD-based base prices. [A-018] |

---

## 7. Public Web Assets (`public/`, 52 files)

### Core Files Reviewed (4 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `public/.htaccess` | REVIEWED | Apache URL rewrite configuration directing requests to `index.php`. |
| `public/favicon.ico` | REVIEWED | Browser website favicon icon. |
| `public/index.php` | REVIEWED | Primary web server entrypoint booting Laravel application kernel. |
| `public/robots.txt` | REVIEWED | Search engine crawler rules. |

### Explicitly Skipped Compiled & Vendor Assets (48 files)
*Note: Per audit guidelines, compiled assets and vendor distributions are explicitly skipped from manual review:*
- `public/css/filament/filament/app.css` (1 compiled CSS file)
- `public/fonts/filament/filament/inter/*` (8 font files: index.css and 7 WOFF2 font files)
- `public/js/filament/*` (28 compiled JS bundle files for Filament admin panel)
- `public/vendor/livewire/*` (11 vendor JavaScript bundle and source-map files for Livewire v4)

---

## 8. Resources & Blade Views (`resources/`, 55 files)

### CSS & JavaScript Assets (2 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `resources/css/app.css` | REVIEWED | Tailwind CSS directive imports and custom styles. |
| `resources/js/app.js` | REVIEWED | Frontend JavaScript bundle bootstrap importing Alpine.js. |

### Blade Views & Components (53 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `resources/views/auth/confirm-password.blade.php` | REVIEWED | Password confirmation screen view. |
| `resources/views/auth/forgot-password.blade.php` | REVIEWED | Password reset request form view. |
| `resources/views/auth/login.blade.php` | REVIEWED | Storefront login screen view. |
| `resources/views/auth/register.blade.php` | REVIEWED | Storefront account registration screen view. |
| `resources/views/auth/reset-password.blade.php` | REVIEWED | Password reset submission form view. |
| `resources/views/auth/verify-email.blade.php` | REVIEWED | Email verification notice screen view. |
| `resources/views/blog/index.blade.php` | REVIEWED | Blog articles listing view. |
| `resources/views/blog/show.blade.php` | REVIEWED | Blog article detail view; unescaped `{!! $post->content !!}`. [A-009] |
| `resources/views/cart/index.blade.php` | REVIEWED | Shopping cart line items review and quantity adjustment view. |
| `resources/views/checkout/index.blade.php` | REVIEWED | COD checkout form view with delivery zone selector. |
| `resources/views/components/application-logo.blade.php` | REVIEWED | Branded FoneX Store SVG logo component. |
| `resources/views/components/auth-session-status.blade.php` | REVIEWED | Flash session message alert component. |
| `resources/views/components/badge.blade.php` | REVIEWED | Status badge UI component. |
| `resources/views/components/danger-button.blade.php` | REVIEWED | Danger button UI component. |
| `resources/views/components/dropdown-link.blade.php` | REVIEWED | Dropdown menu link component. |
| `resources/views/components/dropdown.blade.php` | REVIEWED | Alpine.js powered dropdown container component. |
| `resources/views/components/input-error.blade.php` | REVIEWED | Form validation error message display component. |
| `resources/views/components/input-label.blade.php` | REVIEWED | Form input label component. |
| `resources/views/components/modal.blade.php` | REVIEWED | Accessible modal dialog container component. |
| `resources/views/components/nav-link.blade.php` | REVIEWED | Desktop navigation link component. |
| `resources/views/components/primary-button.blade.php` | REVIEWED | Primary button component with brand styling. |
| `resources/views/components/product-card.blade.php` | REVIEWED | Product catalog card displaying model, image, grades, and prices. |
| `resources/views/components/product-icon.blade.php` | REVIEWED | Category icon SVG component. |
| `resources/views/components/responsive-nav-link.blade.php` | REVIEWED | Mobile drawer navigation link component. |
| `resources/views/components/secondary-button.blade.php` | REVIEWED | Secondary button UI component. |
| `resources/views/components/section-heading.blade.php` | REVIEWED | Section title and subtitle header component. |
| `resources/views/components/storefront/⚡product-catalog.blade.php` | REVIEWED | Dynamic storefront catalog component (contains UTF-8 lightning bolt in filename). |
| `resources/views/components/text-input.blade.php` | REVIEWED | Styled text input component. |
| `resources/views/dashboard.blade.php` | REVIEWED | Customer dashboard displaying verified orders and trade-ins. |
| `resources/views/emails/admin-trade-in-alert.blade.php` | REVIEWED | Email template notifying admin of new trade-in valuation. |
| `resources/views/emails/orders/placed.blade.php` | REVIEWED | Email template confirming order placement to customer. |
| `resources/views/emails/tradein/submitted.blade.php` | REVIEWED | Email template confirming trade-in request submission to customer. |
| `resources/views/home.blade.php` | REVIEWED | Storefront homepage view with hero slider, featured devices, and categories. |
| `resources/views/layouts/app.blade.php` | REVIEWED | Authenticated user application master layout. |
| `resources/views/layouts/guest.blade.php` | REVIEWED | Guest authentication master layout. |
| `resources/views/layouts/navigation.blade.php` | REVIEWED | Authenticated dashboard navigation bar. |
| `resources/views/layouts/storefront.blade.php` | REVIEWED | Storefront master layout with header, cart drawer, and footer. |
| `resources/views/orders/confirmation.blade.php` | REVIEWED | Order confirmation view displaying COD order details and invoice summary. |
| `resources/views/products/index.blade.php` | REVIEWED | Catalog listing view with category, brand, grade, and price filters. |
| `resources/views/products/show.blade.php` | REVIEWED | Single product detail view; unescaped `{!! $product->description !!}`. [A-009] |
| `resources/views/profile/edit.blade.php` | REVIEWED | Profile management view for updating password and account info. |
| `resources/views/profile/partials/delete-user-form.blade.php` | REVIEWED | User account deletion confirmation partial. |
| `resources/views/profile/partials/update-password-form.blade.php` | REVIEWED | Password update form partial. |
| `resources/views/profile/partials/update-profile-information-form.blade.php` | REVIEWED | Profile details update form partial. |
| `resources/views/sitemap/index.blade.php` | REVIEWED | XML sitemap template. |
| `resources/views/trade-in/confirmation.blade.php` | REVIEWED | Trade-in request confirmation view with reference number. |
| `resources/views/trade-in/create.blade.php` | REVIEWED | Multi-step trade-in valuation wizard form. [A-014] |
| `resources/views/vendor/livewire/bootstrap.blade.php` | REVIEWED | Vendor Livewire bootstrap pagination view. |
| `resources/views/vendor/livewire/simple-bootstrap.blade.php` | REVIEWED | Vendor Livewire simple bootstrap pagination view. |
| `resources/views/vendor/livewire/simple-tailwind.blade.php` | REVIEWED | Vendor Livewire simple tailwind pagination view. |
| `resources/views/vendor/livewire/tailwind.blade.php` | REVIEWED | Vendor Livewire tailwind pagination view. |
| `resources/views/welcome.blade.php` | REVIEWED | Default welcome view. |
| `resources/views/wishlist/index.blade.php` | REVIEWED | Customer wishlist review view. |

---

## 9. Routes (3 files)

| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `routes/auth.php` | REVIEWED | Authentication routes for registration, login, email verification, and password resets. |
| `routes/console.php` | REVIEWED | Artisan console schedule definition for hourly `shop:expire-unconfirmed-orders`. |
| `routes/web.php` | REVIEWED | Public storefront, catalog, cart, checkout, trade-in, and account routes with throttling. |

---

## 10. Storage Tracked Files (`storage/`, 26 files)

### `.gitignore` Trackers Reviewed (10 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `storage/app/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/app/private/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/app/public/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/framework/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/framework/cache/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/framework/cache/data/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/framework/sessions/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/framework/testing/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/framework/views/.gitignore` | REVIEWED | Keeps directory tracked in git. |
| `storage/logs/.gitignore` | REVIEWED | Keeps directory tracked in git. |

### Tracked Junk / Temporary Files (16 files)
*Note: These files were accidentally committed to git and should be untracked:*
- `storage/tmp/php6a78a25fa5add.tmp` through `storage/tmp/php6a78adc4c5c21.tmp` (12 temporary upload files, [A-015])
- `storage/tmp/sf_proc_00.err`, `sf_proc_00.err.lock`, `sf_proc_00.out`, `sf_proc_00.out.lock` (4 process lock files, [A-015])

---

## 11. Test Suite (`tests/`, 49 files)

### Test Base & Helpers (2 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `tests/TestCase.php` | REVIEWED | Base test case configuring database refresh and environment. |
| `tests/Support/InteractsWithShop.php` | REVIEWED | Reusable trait providing helper methods for creating catalog products and admin authentication. |

### Unit Tests (13 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `tests/Unit/Actions/PlaceOrderTest.php` | REVIEWED | Unit tests for PlaceOrder action checkout flow and stock deduction. |
| `tests/Unit/ExampleTest.php` | REVIEWED | Basic assertion test. |
| `tests/Unit/Models/CategoryTest.php` | REVIEWED | Unit tests verifying Category model relations. |
| `tests/Unit/Models/CustomerTest.php` | REVIEWED | Unit tests verifying Customer phone uniqueness and orders relations. |
| `tests/Unit/Models/DeviceUnitTest.php` | REVIEWED | Unit tests for DeviceUnit status scopes and sold/returned transitions. |
| `tests/Unit/Models/OrderTest.php` | REVIEWED | Unit tests for Order casting, soft deletes, and profit margin computation. [A-012] |
| `tests/Unit/Models/ProductConditionTest.php` | REVIEWED | Unit tests for ProductCondition stock adjustments and negative quantity protection. |
| `tests/Unit/Models/ProductTest.php` | REVIEWED | Unit tests for Product scopes and media collections. |
| `tests/Unit/Models/StockMovementTest.php` | REVIEWED | Unit tests for StockMovement enum casting and relations. |
| `tests/Unit/Models/TradeInValuationTest.php` | REVIEWED | Unit tests for TradeInValuation model and inventory ingestion. |
| `tests/Unit/Services/CartServiceTest.php` | REVIEWED | Unit tests for CartService item calculation, subtotal, and accessory shipping. |
| `tests/Unit/Services/StockMovementServiceTest.php` | REVIEWED | Unit tests for stock movement service atomic adjustments. |
| `tests/Unit/SetupValidationTest.php` | REVIEWED | Unit tests verifying factories generate valid database rows. |

### Feature Tests (34 files)
| File | Status | Description & Issues |
| :--- | :---: | :--- |
| `tests/Feature/Admin/AdminAuthTest.php` | REVIEWED | Tests Filament admin login barrier and non-admin rejection. |
| `tests/Feature/Admin/CategoryResourceTest.php` | REVIEWED | Tests Filament category resource listing and creation. |
| `tests/Feature/Admin/DeviceUnitResourceTest.php` | REVIEWED | Tests Filament device units resource and IMEI filtering. |
| `tests/Feature/Admin/ExpireUnconfirmedOrdersTest.php` | REVIEWED | Tests scheduled `shop:expire-unconfirmed-orders` command logic. |
| `tests/Feature/Admin/LowStockWidgetTest.php` | REVIEWED | Tests low-stock dashboard widget display threshold. |
| `tests/Feature/Admin/OrderResourceTest.php` | REVIEWED | Tests Filament manual order creation, cancellation restock, and phone confirmation. [A-012] |
| `tests/Feature/Admin/ProductResourceTest.php` | REVIEWED | Tests Filament product creation, editing, and visibility toggling. |
| `tests/Feature/Admin/StockMovementResourceTest.php` | REVIEWED | Tests Filament stock movement audit logging and adjustments. |
| `tests/Feature/Admin/TradeInResourceTest.php` | REVIEWED | Tests Filament trade-in requests and inventory ingestion modal. |
| `tests/Feature/Auth/AuthenticationTest.php` | REVIEWED | Tests storefront user login and logout. |
| `tests/Feature/Auth/EmailVerificationTest.php` | REVIEWED | Tests customer email verification link flow. |
| `tests/Feature/Auth/PasswordConfirmationTest.php` | REVIEWED | Tests password confirmation screen. |
| `tests/Feature/Auth/PasswordResetTest.php` | REVIEWED | Tests password reset request and submission. |
| `tests/Feature/Auth/PasswordUpdateTest.php` | REVIEWED | Tests user password updates from account settings. |
| `tests/Feature/Auth/RegistrationTest.php` | REVIEWED | Tests new customer account registration. |
| `tests/Feature/Cart/CartTest.php` | REVIEWED | Tests cart operations (add, update, delete). |
| `tests/Feature/CartTest.php` | REVIEWED | Additional cart feature tests. |
| `tests/Feature/Catalog/HomepageTest.php` | REVIEWED | Tests homepage rendering and hero banner. |
| `tests/Feature/Catalog/ProductIndexTest.php` | REVIEWED | Tests product catalog filtering and search queries. |
| `tests/Feature/Catalog/ProductShowTest.php` | REVIEWED | Tests single product detail page and condition selectors. |
| `tests/Feature/Checkout/CheckoutTest.php` | REVIEWED | Tests COD checkout form validation and PlaceOrder integration. |
| `tests/Feature/CheckoutTest.php` | REVIEWED | Additional checkout flow tests. |
| `tests/Feature/ExampleTest.php` | REVIEWED | Verifies homepage returns HTTP 200. |
| `tests/Feature/Integrity/Phase2DataIntegrityTest.php` | REVIEWED | Data integrity tests for atomic stock transactions and constraints. |
| `tests/Feature/Performance/Phase3PerformanceTest.php` | REVIEWED | Query count benchmarks verifying eager loading on catalog pages. |
| `tests/Feature/ProfileTest.php` | REVIEWED | Tests user profile edits and account deletion. |
| `tests/Feature/Security/Phase1SecurityTest.php` | REVIEWED | Tests rate limiting, CSRF protection, and admin isolation. |
| `tests/Feature/Security/SecurityTest.php` | REVIEWED | Additional security tests. |
| `tests/Feature/Smoke/CatalogSeederTest.php` | REVIEWED | Smoke tests validating database catalog seeders. |
| `tests/Feature/Smoke/EndToEndOrderFlowTest.php` | REVIEWED | Complete end-to-end order flow from browsing to confirmation. |
| `tests/Feature/Smoke/EndToEndTradeInFlowTest.php` | REVIEWED | Complete end-to-end trade-in flow from submission to stock ingestion. |
| `tests/Feature/Smoke/PublicRoutesSmokeTest.php` | REVIEWED | Smoke tests verifying all public storefront routes return HTTP 200. |
| `tests/Feature/TradeIn/TradeInTest.php` | REVIEWED | Tests trade-in valuation submission and phone normalization. [A-012] |
| `tests/Feature/TradeInTest.php` | REVIEWED | Additional trade-in integration tests. |
