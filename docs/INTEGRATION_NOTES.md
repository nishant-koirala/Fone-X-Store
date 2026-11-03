# Integration Notes: Feature Branch + Audit Fixes

This document records decisions, baseline status, and security/data audit observations during the integration of `origin/feature/unit-linkage-and-safety-fixes` (primary base) and selected non-overlapping fixes from `origin/audit/full-system-review`.

## Step 0 - Baseline Verification

- **Branch Created**: `integration/feature-plus-audit-fixes` (branched from `origin/feature/unit-linkage-and-safety-fixes`)
- **Composer Install**: Clean (0 errors)
- **Composer Run CI Baseline**:
  - Pint: Passed
  - Larastan (PHPStan Level 6/strict): Passed (0 errors)
  - PHPUnit: Passed (265 tests, 1702 assertions, 0 failures, 0 errors)
- **Database Migrations & Seeders Baseline**:
  - Command: `php artisan migrate:fresh --database=sqlite --seed --force`
  - Result: 35 migrations executed successfully; seeders `IphoneCatalogSeeder` and `SamsungCatalogSeeder` completed cleanly.
- **Baseline Health**: GREEN across all automated checks.

---

## Step 1 - Cherry-Picked Audit Commits

| Commit | Description | Conflict / Integration Resolution |
|---|---|---|
| `c1c3de7` | media table rollback + protect order history from customer deletion | Restored historical migration `2026_08_07_000006_create_orders_table.php` to `origin/main` (cannot mutate past migrations). Enforced customer protection via new migration `2026_11_03_200000_prevent_customer_cascade_delete_on_orders.php` for MySQL/PostgreSQL, plus cross-driver application hook in `Customer::booted` and Filament `CustomerResource` action cancellation. Passed CI (266 tests). |
| `ab386d4` | XSS views, PII in logs, trade-in email validation | Clean cherry-pick without conflict. Redacted customer phone/address/name from checkout failure logs, added `customer_email` validation rule to `StoreTradeInRequest`, and initial view escape. (Note: Step 2 will upgrade the views to use the robust `HtmlSanitizer`). Passed CI (266 tests). |
| `c3aece5` | untrack storage/tmp files, branded HTTP error pages, NPR seeder prices | Clean cherry-pick without conflict. Removed untracked temporary files from storage/tmp, added customized branded error views (404, 419, 429, 500) with shop branding and support details, and scaled seeder catalog pricing to realistic NPR values. Passed CI (266 tests). |
| `43bec70` | patch Composer/NPM vulnerabilities | Clean cherry-pick without conflict. `npm audit` returned 0 vulnerabilities. `npm run build` succeeded without error. `composer audit` noted existing framework/filament upstream advisories (no breaking package bumps allowed per ground rules). Full test suite passed (266 tests, 1706 assertions). |

### Dependency Audit Summary
- **NPM Audit**: 0 vulnerabilities found.
- **Vite Build**: Succeeded in 9.05s (`manifest.json`, `app.css`, `app.js` generated).
- **Composer Audit**: 6 upstream advisories identified in locked framework versions (`filament/filament` MFA password prompt, `laravel/framework` TokenGuard/unauthorized relation deletion/XSS in debug page, `league/flysystem` path normalization on malformed UTF-8, `livewire/livewire` authorize parameter order). Per ground rules ("No new Composer/NPM packages"), lockfile patches from audit branch applied cleanly without breaking changes.

---

## Step 2 - HTML Sanitizer & XSS Audit

### App\Support\HtmlSanitizer Implementation
- Built zero-dependency `App\Support\HtmlSanitizer` using PHP's native `DOMDocument` and `DOMXPath`.
- **Strict Tag Allowlist**: `['p', 'br', 'strong', 'em', 'b', 'i', 'u', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'blockquote', 'code', 'pre', 'hr', 'a', 'img']`.
- **Disallowed Elements Handling**:
  - Dangerous blocks and foreign content (`script`, `style`, `svg`, `iframe`, `object`, `embed`, `form`, `math`, `base`, `meta`, `link`, etc.) are pre-stripped and purged along with their contents, even if unclosed or malformed.
  - Safe formatting/container elements (e.g. `div`, `span`) have their tags and attributes stripped while safely preserving their children.
- **Strict Attribute Allowlist**: Only `a[href]`, `img[src]`, and `img[alt]` are permitted. All other attributes (`onclick`, `onerror`, `onload`, `style`, `class`, `id`, `srcset`, etc.) are stripped.
- **URL & Scheme Sanitization**:
  - `href`: Must match `http`, `https`, `mailto`, or `tel`, or be a local relative path (`/` or `#`). Protocol-relative `//` and dangerous schemes (`javascript:`, `data:`, `vbscript:`, including recursive HTML entities and embedded whitespace/control characters) are rejected.
  - External links automatically enforce `rel="noopener noreferrer nofollow"` and `target="_blank"`.
  - `img[src]`: Must match `http(s)://` or relative `/storage` or `/images` paths. Non-conforming images are purged.
- **Plain-Text Detection**: If no HTML tags are detected, input is safely rendered using `nl2br(e($text))` for readable paragraph formatting with line breaks.
- **Error Handling**: Gracefully handles invalid UTF-8 and malformed HTML without throwing, falling back to `nl2br(e($html))`.

### Views Updated
- `resources/views/blog/show.blade.php`: Replaced insecure `strip_tags()` with `{!! \App\Support\HtmlSanitizer::clean($post->content) !!}`. Also disarmed `meta_description`.
- `resources/views/products/show.blade.php`: Replaced `nl2br(e())` with `{!! \App\Support\HtmlSanitizer::clean($product->description) !!}`. Plain-text descriptions still render line breaks; rich descriptions render allowed HTML tags safely. Disarmed JSON-LD schema description.
- `resources/views/blog/index.blade.php`: Cleaned preview excerpt before applying `strip_tags()`.

### Comprehensive Grep of Unescaped Blade Echoes (`{!! !!}`)
Audit results across the entire repository:
1. `resources/views/blog/show.blade.php:28`: `{!! \App\Support\HtmlSanitizer::clean($post->content) !!}` (User-controlled blog content, strictly sanitized).
2. `resources/views/products/show.blade.php:490`: `{!! \App\Support\HtmlSanitizer::clean($product->description) !!}` (User-controlled product description, strictly sanitized).
3. `resources/views/products/show.blade.php:9`: `{!! json_encode([...]) !!}` (JSON-LD structured schema script, user input sanitized and json-encoded).
4. `resources/views/sitemap/index.blade.php:1`: `{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}` (Static XML header to bypass PHP short tag parsing; safe, non-user input).
5. `resources/views/vendor/livewire/*.blade.php`: Framework pagination translation strings (`{!! __('pagination.previous') !!}`, `{!! __('Showing') !!}`). Safe system strings.
*Conclusion*: No unescaped user-controlled output remains unprotected.

### Test Verification
- Unit test suite `tests/Unit/Support/HtmlSanitizerTest.php` passes all 16 test cases including all requested attack payloads (`<script>`, `<img onerror>`, `javascript:`, obfuscated entities, `data:`, `svg onload`, `iframe`, `onclick`, `style`, inline background `javascript:`, nested/unclosed tags, uppercase tags, safe links, plain text).
- Feature test suite `tests/Feature/Security/XssSanitizationViewTest.php` verifies that blog and product show views render zero executable attack attributes or tags while preserving safe content.

---

## Step 3 - Profit Cost Inconsistency Fix

### Problem Analysis
- Previously, `order_items.cost_price_at_purchase` held the total cost for unit-tracked items, but held a per-unit cost for untracked items.
- `OrderItem::getProfitAttribute()` checked whether device units were linked via `$this->deviceUnits()->exists()`, triggering N+1 queries per item and breaking profit calculations if units were unlinked/restocked after order cancellation.

### Resolution & Implementation
1. **New Migration**:
   - `2026_11_03_210000_add_cost_total_at_purchase_to_order_items_table.php` added nullable decimal column `order_items.cost_total_at_purchase`.
   - Included safe, idempotent chunked backfill: `cost_total = cost_price_at_purchase * quantity` for existing rows where `cost_price_at_purchase` was recorded per-unit and `cost_total` was null.
2. **Sales Actions Updated**:
   - `PlaceOrder` (storefront checkout) and `CreateOrder` (admin panel):
     - For untracked items: writes `cost_price_at_purchase = condition->cost_price`, `cost_total_at_purchase = condition->cost_price * quantity`.
     - For tracked items: writes `cost_total_at_purchase = sum(assigned unit costs)` (with existing condition fallback), and writes `cost_price_at_purchase = round(total / quantity, 2)` (average per unit for backwards compatibility).
3. **Decoupled Profit Formula**:
   - In `OrderItem::getProfitAttribute()`:
     - When `cost_total_at_purchase !== null`: `(price_at_purchase * quantity) - cost_total_at_purchase`.
     - Else when `cost_price_at_purchase !== null`: `(price_at_purchase - cost_price_at_purchase) * quantity` (safe fallback for legacy rows).
     - Else: `null`.
   - Completely eliminated queries to `device_units`. `Order::calculateProfit()` loads only `items` without `deviceUnits`.
4. **Verification & Testing**:
   - Verified across 7 distinct scenarios in `tests/Unit/Models/OrderItemProfitTest.php`:
     1. Untracked quantity 3.
     2. Tracked quantity 3 with differing unit costs.
     3. Mixed cart with tracked and untracked items.
     4. Null-cost unit handling (returns null profit).
     5. Legacy rows with `cost_total_at_purchase = null` continue to calculate correctly.
     6. Cancelled and restocked orders maintain stable profit snapshots without active unit linkage.
     7. Filament `gross_profit` margin column calculates without querying `device_units`.

---

## Step 4 - Delivery Fee Policy Check

A-006 resolved by policy: delivery payable on delivery

### Audit Findings & Verification
- **Policy Definition**: Delivery fares are paid by the customer directly to the courier upon delivery (Pathao inside Kathmandu Valley; registered courier nationwide). The website does not charge shipping fees.
- **Config & Environment Keys**:
  - `config/shop.php` inspected: No dead delivery fee config keys remain.
  - `.env.example` inspected: No dead delivery environment variables.
  - `README.md` inspected: Reflects Cash on Delivery without inaccurate shipping promises.
- **Storefront & Admin Consistency**:
  - `resources/views/checkout/index.blade.php`: Clearly notes delivery fees as "Pathao / Courier (Payable on Delivery)" with an order total matching pure item subtotal.
  - `resources/views/orders/confirmation.blade.php`: States that delivery fare is payable directly to the courier upon delivery.
## Step 5 - Integration Summary & Commit Log

All changes were performed exclusively on `integration/feature-plus-audit-fixes` branched from `origin/feature/unit-linkage-and-safety-fixes`. No changes, merges, or commits were made to `main`. Every commit includes a 2-3 line "why" explaining the rationale.

### Full Commit Log on `integration/feature-plus-audit-fixes`:

| Commit Hash | Subject | Mentor Explanation ("Why") |
|---|---|---|
| `58fe7fc` | `fix(database): fix media table rollback and protect order history from customer deletion` | Cherry-picked from audit branch to ensure media table rolls back cleanly with dropIfExists and begins customer deletion protection. |
| `a1fbddb` | `fix(database): restore historical orders migration and rely on app guard` | Reverts mutation of already-run migration `create_orders_table.php` back to `origin/main`. Protects customer deletion via new schema migration for MySQL/PG and application deleting hook on Customer model across all database drivers. |
| `d406d01` | `fix(security): sanitize blog/product views against XSS, protect customer PII in logs, and validate trade-in email` | Cherry-picked non-overlapping security fixes: redacts customer phone/address from checkout failure logs and validates trade-in emails. |
| `5b63fee` | `fix(shop): untrack temporary storage files, add branded HTTP error pages, and scale seeder prices to NPR` | Untracks temporary files from `storage/tmp/`, adds customized HTTP error pages (404, 419, 429, 500), and scales seeder catalogs to realistic NPR values. |
| `c5ff22c` | `fix(deps): patch security vulnerabilities in Composer and NPM dependencies` | Upgrades locked non-breaking dependencies to address known security advisories in NPM and Composer packages without breaking changes. |
| `9c4584f` | `docs: record cherry-picked audit commits and dependency audits` | Records baseline verification and dependency audit results in integration documentation. |
| `6ecf5bc` | `feat(security): implement robust DOMDocument HtmlSanitizer and secure views` | Implements DOMDocument-based strict allowlist HTML sanitizer removing all dangerous attributes/protocols (javascript:, data:, onclick, etc.) while allowing safe formatting. Disarms unclosed foreign tags and secures external links. |
| `04bc437` | `feat(finance): resolve profit cost inconsistency with cost_total_at_purchase column` | Adds `order_items.cost_total_at_purchase` migration with chunked backfill. Records total cost for tracked and untracked items and average per-unit cost for compatibility, completely eliminating N+1 device unit queries. |
| `8b1aa22` | `fix(delivery): enforce delivery fee payable on delivery policy across views and emails` | Standardizes all customer touchpoints (checkout, confirmation page, emails, admin panel) on the policy that delivery fares are paid by the customer directly to the courier on delivery. |

### Final Automated Quality Gate Verification
- `vendor/bin/pint --test`: PASSED (0 lint errors).
- `vendor/bin/phpstan analyse --memory-limit=2G`: PASSED (Level 6 strict, 0 errors).
- `vendor/bin/phpunit`: PASSED (286 tests, 1,782 assertions, 0 failures, 0 errors).
- `php artisan migrate:fresh --seed`: PASSED cleanly on throwaway database.

