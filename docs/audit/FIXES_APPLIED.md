# Minor Fixes Applied (Mode B Protocol)

This document tracks all single-file, provable bug fixes applied under **MODE B** following the completion of the full repository audit.

---

## Mode B Eligibility Rules
Every fix applied in this phase strictly satisfies all of the following:
1. **Scope:** Confined to one file (plus its accompanying test), roughly 10 lines or fewer.
2. **Schema:** Requires zero database migrations or schema alterations.
3. **Logic:** Does not change business rules, stock/inventory behavior, pricing models, authorization boundaries, or user-visible policy copy.
4. **Proof:** Directly proven with a test or tool output (`Pint`, `PHPStan`, `PHPUnit`, `php -l`).

---

## Log of Applied Fixes

| # | Commit | Finding Ref | Target File | Description of Fix | Verification & Proof |
|---|--------|-------------|-------------|---------------------|----------------------|
| 1 | `2be8ee4` | [A-011](file:///docs/audit/FINDINGS.md#a-011) | `app/Actions/Checkout/PlaceOrder.php` | Add `delivery_zone?: ?string` to `$data` PHPDoc array type | PHPStan Level 5 `nullCoalesce.offset` resolved; `PlaceOrderTest` passed (5 tests, 17 assertions) |
| 2 | `3c20719` | [A-011](file:///docs/audit/FINDINGS.md#a-011) | `app/Console/Commands/ExpireUnconfirmedOrders.php` | Replace redundant nullsafe `$order->customer?->name` with `$order->customer->name` on non-nullable relation | PHPStan Level 5 `nullsafe.neverNull` resolved; `ExpireUnconfirmedOrdersTest` passed (2 tests, 12 assertions) |
| 3 | `0f13457` | [A-011](file:///docs/audit/FINDINGS.md#a-011) | `app/Models/TradeInValuation.php` | Remove redundant nullsafe `$this->customer?->name` and import `DeviceUnitStatusEnum` | PHPStan Level 5 `nullsafe.neverNull` resolved; `TradeInValuationTest` passed (3 tests, 10 assertions) |
| 4 | `bc6a59d` | [A-011](file:///docs/audit/FINDINGS.md#a-011) | `app/Filament/Resources/OrderResource.php` | Specify `: string` return type on `gross_profit` table column closure | PHPStan Level 5 `return.type` resolved; `OrderResourceTest` passed (10 tests, 61 assertions) |
| 5 | `93fa3c6` | [A-011](file:///docs/audit/FINDINGS.md#a-011) | `app/Filament/Resources/OrderResource/Pages/CreateOrder.php` | Add `/** @var Order $order */` annotation on `$this->record`; remove unused `InsufficientStockException` and `DB` | PHPStan Level 5 `method.notFound` resolved; `OrderResourceTest` passed (10 tests, 61 assertions) |
| 6 | `8cd6416` | [A-011](file:///docs/audit/FINDINGS.md#a-011) | `app/Filament/Resources/DeviceUnitResource/Pages/CreateDeviceUnit.php` | Add `/** @var DeviceUnit $unit */` annotation on `$this->record`; fix string concatenation spacing | PHPStan Level 5 `property.notFound` resolved; `DeviceUnitResourceTest` passed (3 tests, 17 assertions) |
| 7 | `2ee356a` | [A-011](file:///docs/audit/FINDINGS.md#a-011) | `app/Filament/Resources/TradeInValuationResource/Pages/EditTradeInValuation.php` | Add `/** @var TradeInValuation $record */` annotation on `$this->record` | PHPStan Level 5 `method.notFound` resolved; `TradeInResourceTest` passed (6 tests, 29 assertions) |
| 8 | `162f6ca` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `app/Filament/Resources/DeviceUnitResource.php` | Remove unused `App\Enums\GradeEnum` import | Pint `no_unused_imports` resolved; `DeviceUnitResourceTest` passed (3 tests, 17 assertions) |
| 9 | `03cc296` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `app/Models/OrderItem.php` | Import `HasMany` and `HasOne` relations instead of FQCN in return types and docblocks | Pint `fully_qualified_strict_types` resolved; `OrderTest` & `CheckoutTest` passed (8 tests, 27 assertions) |
| 10 | `0809dd0` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `app/Models/ProductCondition.php` | Import `DeviceUnitStatusEnum` and use imported class in `availableDeviceUnits()` relation | Pint `fully_qualified_strict_types` & `ordered_imports` resolved; `ProductCondition` tests passed (4 tests, 7 assertions) |
| 11 | `6145dcb` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `app/Models/TradeInValuation.php` | Import `HasOne` relation instead of FQCN in `deviceUnit()` return type and docblock | Pint `fully_qualified_strict_types` & `ordered_imports` resolved; `TradeInTest` passed (3 tests, 11 assertions) |
| 12 | `f6b7d2b` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `app/Rules/NepaliPhone.php` | Import `PotentiallyTranslatedString` instead of FQCN in docblock type | Pint `fully_qualified_strict_types` resolved; `CheckoutTest` & `TradeInTest` passed (4 tests, 16 assertions) |
| 13 | `34f4fa4` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `config/shop.php` | Standardize binary operator spacing across configuration arrays | Pint `binary_operator_spaces` resolved; `config:cache` and `config:clear` succeeded |
| 14 | `eae6cc0` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `tests/Feature/Admin/OrderResourceTest.php` | Remove unused `ProductCondition` import, align operators, and add method separation blank line | Pint `class_attributes_separation`, `no_unused_imports` resolved; `OrderResourceTest` passed (10 tests, 61 assertions) |
| 15 | `7d58d44` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `tests/Feature/TradeInTest.php` | Import `Customer` model instead of inline FQCN instantiation | Pint `fully_qualified_strict_types`, `ordered_imports` resolved; `TradeInTest` passed (3 tests, 11 assertions) |
| 16 | `8dad519` | [A-012](file:///docs/audit/FINDINGS.md#a-012) | `tests/Unit/Models/OrderTest.php` | Add missing blank line between test methods | Pint `class_attributes_separation` resolved; `OrderTest` passed (7 tests, 22 assertions) |

---

## Non-Fixable Issues Retained as Open/Documented
The following issues were identified by static analysis and linting tools but deliberately **NOT fixed** under Mode B in strict compliance with the audit boundary rules:

1. **`database/migrations/2026_11_02_163555_clean_legacy_trade_in_placeholder_addresses.php` (Pint):**
   - *Reason:* Under audit rule "Never edit an already-run migration", migrations in git history must never be rewritten. Documented in [DECISIONS.md (D-004)](file:///docs/audit/DECISIONS.md).
2. **Schema & Constraint Alterations (e.g. `media` table rollback, `customer_id` cascade, signed stock integers):**
   - *Reason:* Require schema migrations and architectural reviews. Documented in [FINDINGS.md](file:///docs/audit/FINDINGS.md) as [A-001](file:///docs/audit/FINDINGS.md#a-001), [A-002](file:///docs/audit/FINDINGS.md#a-002), [A-013](file:///docs/audit/FINDINGS.md#a-013).
3. **Inventory & Order State Machines (e.g. unit status transitions, stock deduction, order cancellation):**
   - *Reason:* Alter stock/inventory business rules and transaction lifecycles. Documented in [FINDINGS.md](file:///docs/audit/FINDINGS.md) as [A-003](file:///docs/audit/FINDINGS.md#a-003), [A-004](file:///docs/audit/FINDINGS.md#a-004), [A-005](file:///docs/audit/FINDINGS.md#a-005), [A-007](file:///docs/audit/FINDINGS.md#a-007).
