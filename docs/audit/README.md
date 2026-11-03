# FoneX Store Repository Audit — Executive Summary

**Project:** FoneX Store (Laravel 13 + Filament v5 + Tailwind CSS)  
**Target Architecture:** Nepali smartphone & accessories storefront with Filament admin, serialized device units (IMEI), trade-in valuations, and COD order management.  
**Audit Branch:** `audit/full-system-review` (Branched from `origin/main` commit `5aae2b1`)  
**Audit Mode:** Complete, evidence-based repository audit.

---

## 1. Executive Summary

A comprehensive, evidence-based code and architectural audit of FoneX Store was conducted across all 367 tracked files. The codebase demonstrates strong foundational design in its public storefront presentation, transaction-wrapped checkout actions, and comprehensive feature test coverage (219 passing tests, 1,504 assertions).

However, the audit identified critical structural vulnerabilities in database migration rollback capabilities, dangerous foreign key cascade configurations that destroy financial order history, inventory drift between aggregate condition stocks and serialized device units, and race conditions during trade-in stock ingestion. Additionally, active security vulnerabilities in upstream dependencies (CVEs in `concurrently`/`shell-quote`, `vite`, `league/commonmark`, `symfony/http-foundation`) and static analysis quality gate failures (17 PHPStan Level 5 errors, 13 Pint failures) require immediate remediation.

---

## 2. Findings Summary by Severity

| Severity | Count | Primary Impact Areas |
| :--- | :---: | :--- |
| **CRITICAL** | **2** | Migration rollback crash (`create_media_table`); Customer cascade delete destroying orders. |
| **HIGH** | **6** | DeviceUnit stock drift; admin order auto-cancellation; trade-in race condition; delivery fee omission; cancelled order stock duplication; upstream package CVEs. |
| **MEDIUM** | **5** | Stored XSS risk via unescaped Blade output; customer PII dumped to log files; 17 PHPStan Level 5 errors; 13 Pint lint failures; negative stock database race condition. |
| **LOW** | **4** | Unvalidated email in trade-in request; 16 tracked temporary files in `storage/tmp/`; dead config keys in `shop.php`; missing custom HTTP error pages. |
| **INFO** | **1** | Catalog seeders use USD prices (Rs 150–1700) instead of realistic NPR market values. |
| **TOTAL** | **18** | **Comprehensive evidence documented in [FINDINGS.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/FINDINGS.md)** |

---

## 3. Top 10 Repository Risks

1. **Irreversible Financial History Loss on Customer Delete (A-002 - CRITICAL):**
   `orders.customer_id` is defined with `cascadeOnDelete()`. If an administrator deletes a customer row in `CustomerResource`, all historical orders, invoices, and sales transactions are permanently erased from the database.
2. **Broken Migration Rollback (A-001 - CRITICAL):**
   Migration `2026_10_26_114437_create_media_table.php` lacks a `down()` method, causing `migrate:rollback` followed by `migrate` to crash with fatal `table "media" already exists`.
3. **Serialized Device Unit vs Aggregate Stock Drift (A-003 - HIGH):**
   Marking a device unit as `DEFECTIVE` or deleting it via `DeviceUnitResource` does not decrement `quantity_in_stock`. Conversely, registering existing phones in `CreateDeviceUnit` increments condition stock, causing phantom stock inflation.
4. **Auto-Cancellation of Manual Admin Orders (A-004 - HIGH):**
   Manual orders created in Filament leave `confirmed_at = null`. After 48 hours, `shop:expire-unconfirmed-orders` cancels and restocks staff orders, assuming they were abandoned web COD checkouts.
5. **Trade-In Ingestion Race Condition (A-005 - HIGH):**
   `TradeInValuation::ingestIntoStock()` checks idempotency outside the transaction without row locking, allowing concurrent clicks to double-ingest stock and generate duplicate device units.
6. **Delivery Fee Omission in Order Totals (A-006 - HIGH):**
   `CartService` calculates shipping fees, but `PlaceOrder` computes `Order->total` solely from line items, omitting shipping. Furthermore, `SHOP_OUTSIDE_VALLEY_FEE` is unreferenced, giving away outside-valley courier delivery for free.
7. **Inventory Duplication on Cancelled Order Re-activation (A-007 - HIGH):**
   When an order is cancelled, inventory is restocked. If an admin edits the order back to `PAID` or `PENDING`, no inventory deduction occurs, permitting unrecorded inventory losses.
8. **Critical Vulnerabilities in Upstream Dependencies (A-008 - HIGH):**
   `shell-quote` command injection (CVSS 8.1 in `concurrently`), `vite` file disclosure on Windows, and `league/commonmark` DoS/XSS advisories fail security audits.
9. **Stored XSS via Unescaped Blade Echoes (A-009 - MEDIUM):**
   `resources/views/products/show.blade.php:471` and `blog/show.blade.php:28` render `{!! $product->description !!}` and `{!! $post->content !!}` without HTML purification.
10. **Plaintext Customer PII in Application Logs (A-010 - MEDIUM):**
    `CheckoutController::store()` dumps full customer name, phone number, email, and home address to `storage/logs/laravel.log` on checkout errors.

---

## 4. What is Healthy & Well-Architected

- **Test Suite Breadth:** 219 tests with 1,504 assertions pass cleanly across unit, feature, and end-to-end flows.
- **Storefront Checkout Atomicity:** `PlaceOrder::execute()` locks inventory rows with `lockForUpdate()`, validates visibility, and coordinates customer creation within an atomic database transaction.
- **No Direct `env()` Anti-Pattern:** Zero direct `env()` calls exist outside `config/` files. Caching commands (`config:cache`, `route:cache`, `view:cache`) execute flawlessly.
- **Filament Admin Panel Security:** Admin panel access is strictly guarded by `canAccessPanel()` checking `$user->is_admin`, and `User::$fillable` guards against mass-assignment privilege escalation.
- **Nepali Phone Normalization:** `NepaliPhone` validation rule and normalizer correctly enforce standard 10-digit formats (`98XXXXXXXX` / `97XXXXXXXX`) across web and trade-in forms.
- **Optimized Frontend Assets:** Vite builds cleanly in ~5 seconds, producing production bundles.
- **Resilient Email Handling:** Background mail dispatch in `CheckoutController` and `TradeInController` is safely isolated in `try/catch` blocks, preventing mail server outages from failing customer orders.

---

## 5. Audit Documentation Sitemap

- [COVERAGE.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/COVERAGE.md): 100% enumeration of all 367 tracked files with review notes.
- [TOOLING.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/TOOLING.md): Exact commands, exit codes, and verbatim outputs from all tools.
- [SCHEMA_MAP.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/SCHEMA_MAP.md): Detailed database table schema, foreign keys, indexes, and model maps.
- [CONFIG_ENV_MATRIX.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/CONFIG_ENV_MATRIX.md): Configuration keys and environment variables cross-check.
- [EXCEPTION_MAP.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/EXCEPTION_MAP.md): Custom exceptions, try/catch blocks, and transaction boundaries.
- [FINDINGS.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/FINDINGS.md): Detailed catalog of all 18 findings with evidence and reproduction steps.
- [DECISIONS.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/DECISIONS.md): Key audit decisions and safety boundaries.
- [FIXES_APPLIED.md](file:///c:/Users/Acer/Desktop/Fone-X-Store/docs/audit/FIXES_APPLIED.md): Log of verified minor fixes applied in Mode B.
