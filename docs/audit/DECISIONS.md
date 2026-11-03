# Audit Decisions & Judgment Calls

This document records the safest operational assumptions and judgment calls made during the complete repository audit of FoneX Store (Laravel 13 + Filament v5 + Tailwind), as mandated by the audit protocol.

---

### D-001: Branch Isolation & Safety Guarantees
- **Decision:** Perform all audit work, documentation, and commits strictly on branch `audit/full-system-review`, created from updated `origin/main` (commit `5aae2b1`).
- **Rationale:** Strict compliance with Branch Rules. Never commit to main, never merge, never force-push, and never rewrite history.

---

### D-002: Mode A (Read-Only) Enforcement
- **Decision:** Maintain zero application code modifications during the audit phase. All investigation is strictly read-only.
- **Rationale:** The prompt establishes a strict two-mode protocol. Mode A permits file creation solely under `docs/audit/`. No migrations, models, services, configs, views, or tests may be modified prior to the full audit being written.

---

### D-003: Database Rollback Verification Environment
- **Decision:** Conduct migration, fresh seeding, and rollback tests on the local SQLite development database (`database/database.sqlite`), followed by an immediate fresh re-seed to return the environment to a clean, valid state.
- **Rationale:** Preserves data safety while testing destructive migration down methods (`migrate:rollback --step=999`).

---

### D-004: Classification of Migration Defect A-001 (CRITICAL)
- **Decision:** Migration `2026_10_26_114437_create_media_table.php` completely lacks a `down()` method, causing fatal failure when rolling back and re-migrating. Documented as finding `A-001` (CRITICAL), but **NOT** modified in Mode B.
- **Rationale:** The prompt explicitly states: *"EVERYTHING ELSE is documented only and never fixed, especially: anything touching migrations or schema... Never edit an already-run migration."*

---

### D-005: Classification of Customer Cascade Delete A-002 (CRITICAL)
- **Decision:** Migration `2026_08_07_000006_create_orders_table.php` establishes `customer_id` with `cascadeOnDelete()`. Documented as finding `A-002` (CRITICAL), but **NOT** modified in Mode B.
- **Rationale:** Fixing foreign key behavior requires a database schema migration. Mode B explicitly prohibits schema or migration changes.

---

### D-006: Device Unit and Stock Synchronization Bugs A-003 & A-005 (HIGH)
- **Decision:** Documented inventory drift bugs where editing or deleting a `DeviceUnit` does not adjust `ProductCondition->quantity_in_stock`, and concurrent trade-in ingestion lacks row locking. Documented as findings `A-003` and `A-005` (HIGH), but left open for architectural redesign.
- **Rationale:** Modifying inventory sync logic alters core business and stock rules, exceeding the strict 10-line minor-fix limit and violating Mode B constraints.

---

### D-007: Shipping Fee Discrepancy A-006 (HIGH)
- **Decision:** `CartService` calculates delivery fees for accessories, while `PlaceOrder` computes total solely from item lines without adding delivery fees or storing shipping costs. Documented as finding `A-006` (HIGH).
- **Rationale:** Altering how orders calculate grand total and delivery charges impacts financial and order transaction logic, which must be documented only.

---

### D-008: Scope of Mode B Minor Fixes
- **Decision:** Only apply fixes that strictly satisfy all criteria:
  1. Confined to 1 file (plus test), roughly <= 10 lines.
  2. Requires no migration or schema change.
  3. Does not change business rules, stock behavior, pricing, authorization, or policy wording.
  4. Is provable with a tool output (Pint, PHPStan, PHPUnit).
- **Candidates Identified:**
  - Missing docblock property in `PlaceOrder.php` (PHPStan `nullCoalesce.offset`).
  - Unused imports in `CreateOrder.php`, `DeviceUnitResource.php`, `clean_legacy_trade_in_placeholder_addresses.php`.
  - Redundant nullsafe operator in `ExpireUnconfirmedOrders.php` and `TradeInValuation.php`.
  - Non-nullable return type declaration in `OrderResource.php` line 210.
  - Pint whitespace/separation adjustments across qualifying files.
