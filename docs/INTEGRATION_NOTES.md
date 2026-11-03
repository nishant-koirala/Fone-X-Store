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

*(To be populated in Step 3)*

---

## Step 4 - Delivery Fee Policy Check

*(To be populated in Step 4)*
