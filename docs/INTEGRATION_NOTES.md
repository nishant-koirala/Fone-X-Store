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

*(To be populated in Step 2)*

---

## Step 3 - Profit Cost Inconsistency Fix

*(To be populated in Step 3)*

---

## Step 4 - Delivery Fee Policy Check

*(To be populated in Step 4)*
