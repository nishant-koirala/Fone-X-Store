# Fone-X-Store: Comprehensive Codebase Audit & Architecture Report

**Date:** November 2026  
**System:** Laravel 13 + Filament v5 + Tailwind CSS  
**Target Domain:** Real-world Nepali smartphone retailer (Brand-new, Pre-owned Grades A/B/C, Accessories, In-store Trade-Ins)  
**Currency:** Nepalese Rupee (NPR) only  
**Test Suite Status:** 195/195 tests passing (1,385 assertions)  

---

## 1. Executive Summary

Fone-X-Store has undergone four consecutive phases of hardening, repository hygiene cleanup, inventory drift remediation, and customer data consistency fixes. 

The application is in a stable, well-tested state with zero test regressions. However, because it represents a **real physical phone retail store in Nepal**, several domain-specific gaps remain between standard e-commerce software conventions and the day-to-day operational realities of Kathmandu phone retail (such as Cash on Delivery dispatch risks, unverified phone numbers, lack of device serialization/IMEI tracking for pre-owned stock, and missing purchase cost/margin tracking).

---

## 2. Comprehensive Codebase Analysis: The Good (Strengths)

```
┌────────────────────────────────────────────────────────────────────────┐
│                        CORE SYSTEM STRENGTHS                           │
├───────────────────┬───────────────────┬────────────────────────────────┤
│ Domain Integrity  │ Security & Abuse  │ Filament Operations            │
│ • Enums & DTOs    │ • Honeypots       │ • Soft Deletes                 │
│ • Stock Audits    │ • Rate Limiting   │ • Inventory Lock on Edit       │
│ • Idempotent Rest │ • IDOR Validation │ • Eager-loaded Relationships   │
└───────────────────┴───────────────────┴────────────────────────────────┘
```

### 2.1 Modern Laravel 13 Architecture
- **Backed Enums**: Core domain models consistently leverage strict string-backed PHP enums implementing Filament color and label contracts:
  - `OrderStatusEnum`: `pending`, `paid`, `fulfilled`, `cancelled`.
  - `GradeEnum`: `new`, `A`, `B`, `C`.
  - `StockMovementTypeEnum`: `restock`, `sale`, `exchange_in`, `adjustment`.
  - `TradeInStatusEnum`: `pending`, `reviewed`, `completed`.
- **Encapsulated Action & Service Layer**:
  - `PlaceOrder`: Encapsulates customer resolution, order creation, item snapshots, stock deduction, and email dispatch inside a strict database transaction.
  - `CartService` & `CartItemData`: Clean DTO-backed cart calculation, subtotaling, and out-of-stock item filtering.
- **Dedicated Form Requests**:
  - Controller actions are decoupled from validation logic via `CheckoutRequest`, `StoreTradeInRequest`, and `StoreProductReviewRequest`.

### 2.2 Inventory Data Integrity & Double-Entry Stock Movement
- **Audit Logging**: Stock is never mutated directly via raw increments/decrements in controllers. All adjustments flow through `ProductCondition::recordStockMovement()`, logging:
  - Movement type (`sale`, `restock`, `adjustment`, `exchange_in`).
  - Signed quantity (negative for outbound sales, positive for inbound inventory).
  - Staff user reference (`created_by`).
  - Narrative note referencing the specific Order ID or reason.
- **Oversell & Race-Condition Defense**:
  - `recordStockMovement()` checks `if ($this->quantity_in_stock + $quantity < 0)` inside the transaction and throws `\InvalidArgumentException`.
  - In `PlaceOrder`, concurrent purchases are rejected before inventory drops below zero, rolling back all order records.
- **Idempotent Restock on Cancellation**:
  - When an order transitions to `cancelled`, `Order::booted()` automatically restocks all items and sets `restocked_at = now()`. Toggling status back and forth will never double-restock inventory.

### 2.3 Automated Test Suite
- **195 Automated Tests (1,385 Assertions)**: Passing in ~26 seconds.
- **Comprehensive Coverage Categories**:
  - **Unit Tests**: Models, relationships, enum casting, cart DTO calculations, stock exceptions.
  - **Feature Tests**: Catalog browsing, search filtering, cart state management, checkout flows, trade-in submissions, reviews.
  - **Admin Tests**: Filament resource permissions, table listings, form saves, low-stock widgets.
  - **Integrity & Security**: CSRF enforcement, route rate limiters, honeypot spam detection, order IDOR authorization, overselling race conditions.

---

## 3. Comprehensive Codebase Analysis: The Bad (Gaps & Tech Debt)

```
┌────────────────────────────────────────────────────────────────────────┐
│                     REMAINING GAPS & TECH DEBT                         │
├───────────────────┬───────────────────┬────────────────────────────────┤
│ 1. Device Tracking│ 2. Financial Gaps │ 3. Nepal Logistics             │
│ • Fungible Grades │ • Zero Cost Price │ • Loose Phone Validation       │
│ • No Serial/IMEI  │ • No Margin Data  │ • Free Shipping Hardcoded      │
│ • Lost Trade-Ins  │ • No P&L View     │ • Immediate COD Deduction      │
└───────────────────┴───────────────────┴────────────────────────────────┘
```

### 3.1 Fungible Quantity vs. Serialized / Unit Tracking for Pre-Owned Stock
- **The Issue**: In `ProductCondition`, inventory is tracked as a single integer count: `quantity_in_stock`.
- **The Operational Problem**: 
  - For brand-new sealed accessories or boxed phones, quantity tracking is standard.
  - But for **pre-owned smartphones** (Grade A, Grade B, Grade C), every single device is an individual physical unit with:
    1. A unique **IMEI / Serial Number**.
    2. Specific **Battery Health %** (e.g., Device #1 has 88% battery, Device #2 has 94%).
    3. Specific **Cosmetic Notes** (e.g., faint bezel scuff on upper left).
    4. Sourcing origin (which customer sold it to the shop, at what trade-in valuation).
- **The Risk**: Without IMEI tracking, staff cannot verify if a customer returning a phone under the 7-day checking guarantee is returning the exact phone that was sold, or an identical-looking damaged board. Additionally, Nepal Telecommunications Authority (NTA) MDMS compliance requires IMEI verification.

### 3.2 Missing Cost / Purchase Price (No Profitability Tracking)
- **The Issue**: Tables `products`, `product_conditions`, `order_items`, and `stock_movements` contain `price` (selling price) and `original_price` (strike-through MSRP). There is **zero cost price** column.
- **The Operational Problem**:
  - The business owner cannot calculate Gross Margin (`Selling Price - Cost Price`).
  - Filament widgets cannot report daily or monthly gross profit.
  - When negotiating trade-in buy-in prices or running sales discounts, staff cannot see the minimum break-even price.

### 3.3 Trade-In Acceptance Does Not Add Sellable Stock
- **The Issue**: When shop staff inspects a customer's trade-in device in `TradeInValuationResource` and marks status as `COMPLETED` ("Exchange Completed"), no inventory movement occurs.
- **The Operational Problem**: 
  - `StockMovementTypeEnum::EXCHANGE_IN` exists in code but is never instantiated.
  - The phone taken in from the customer does not appear in inventory, requiring staff to manually remember to create a new `ProductCondition` or adjust stock elsewhere.

### 3.4 Nepal-Specific Logistics & Phone Number Validation
- **Loose Phone Validation**:
  - `CheckoutRequest` validates `'phone' => ['required', 'string', 'max:50']`.
  - A user can enter `phone = "123"` or `"abcdef"` and complete checkout.
  - In a Cash-on-Delivery (COD) business, unverified phone numbers result in dispatched delivery riders unable to contact customers, generating wasted delivery fees.
  - Nepali mobile numbers follow a strict format: 10 digits starting with `98` (NTC/Ncell) or `97` (NTC/SmartCell), optionally prefixed by `+977` or `977`.
- **Delivery Zone Disconnect**:
  - `config/shop.php` specifies Kathmandu Valley delivery (NPR 0) vs. Outside Valley delivery (NPR 200).
  - However, `PlaceOrder` calculates `$orderTotal` solely as item sum, hardcoding shipping as free.
  - Outside-valley orders (e.g. Biratnagar, Pokhara, Butwal) cost the shop NPR 150–250 per courier parcel, eroding margins on smaller orders.

### 3.5 Immediate Stock Deduction on Unconfirmed COD
- **The Issue**: An online order placed via COD immediately decrements live stock and creates an order in `pending`.
- **The Operational Problem**:
  - If a customer places an order at 2:00 AM and never answers their phone the next morning, that phone is locked and unavailable for in-store customers walking in with cash.
  - There is currently no `awaiting_confirmation` status or automated scheduled command to auto-cancel and restock abandoned COD orders past the configured 48-hour threshold.

---

## 4. Completed Work Audit (Phases 1–8: 100% Complete)

| Phase | Commit | Files Changed | Changes & Domain Implementation |
|---|---|---|---|
| **Phase 1: Repo Hygiene & Config** | `ab1f57b` | 10 files | • **Deleted** `reset_admin_pass.php` (plain text credentials) and `php.ini`.<br>• Centralized delivery fees, guarantee days, and order expiry in `config/shop.php`.<br>• Softened unverifiable storefront marketing claims. |
| **Phase 2: Order Soft Deletes & Serialized IMEI Tracking** | `619b587`, `b26044f` | 18 files | • Soft deletes on `orders` table with TrashedFilter and restore guards.<br>• Added `device_units` table, `DeviceUnit` model, and `DeviceUnitStatusEnum`.<br>• Full IMEI, serial number, and verified battery health % tracking for pre-owned phones (MDMS & 7-day guarantee returns).<br>• Dedicated Filament `DeviceUnitResource` with search and stock synchronization. |
| **Phase 3: Admin Stock Drift Remediation & Atomic Locking** | `f6f86f0`, `9ba0ae9` | 14 files | • Added atomic DB transaction with `lockForUpdate()` in Filament `CreateOrder`.<br>• Auto-restock guard in `Order::deleting` hook for unreturned items.<br>• Restricted table DeleteAction to cancelled orders only.<br>• Removed `admin@fonex.com` fallback and updated banner/footer contact info. |
| **Phase 4: Customer Data Consistency** | `ccf3ee2` | 5 files | • Eliminated placeholder address strings in trade-ins and prevented customer name overwriting.<br>• Migration cleaned legacy placeholder records.<br>• Auto-heals customer addresses upon checkout. |
| **Phase 5: Nepali Phone & Delivery Zone Hardening** | `8ccbcf2` | 10 files | • `NepaliPhone` validation rule strictly enforcing 10-digit mobile numbers with 98/97 prefixes and automatic normalization.<br>• Delivery zone selection (Kathmandu Valley Pathao vs. Outside Valley Courier with pay-on-delivery fees). |
| **Phase 6: Financial Visibility — Purchase Cost & Margin Tracking** | `9542b32` | 11 files | • Added `cost_price` to `product_conditions` and `cost_price_at_purchase` snapshot to `order_items`.<br>• Implemented `calculateProfit()` on `Order` and profit accessor on `OrderItem`.<br>• Added 'Est. Margin' column and line-item cost visibility in Filament `OrderResource`. |
| **Phase 7: Trade-In Stock Ingestion (exchange_in)** | `1e0b7dd` | 5 files | • Linked trade-in valuations to `product_conditions` with `stock_ingested_at` timestamp.<br>• Idempotent `ingestIntoStock()` method creating `StockMovementTypeEnum::EXCHANGE_IN` (+1 unit).<br>• Sourcing acquisition cost basis from valuation value.<br>• Filament 'Ingest to Stock' modal action with IMEI and battery health inputs. |
| **Phase 8: COD Lifecycle & Pre-Dispatch Confirmation** | `cd4b3ea` | 7 files | • Added `confirmed_at` timestamp to `orders` with 'Confirm Phone' action in Filament.<br>• Created `shop:expire-unconfirmed-orders` Artisan command with `--dry-run` and `--hours`.<br>• Auto-cancels and restocks abandoned unconfirmed COD orders.<br>• Scheduled hourly in `routes/console.php`. |

---

## 5. Verification & Final Status

- **Automated Tests**: **219 tests passing (1,504 assertions)** with 0 failures in ~26 seconds.
- **Findings Resolution Status**:
  - **F1 (Unbacked Storefront Claims)**: Resolved (claims softened, Pathao/Courier pay-on-delivery accurate copy, footer WhatsApp aligned).
  - **F2 (Per-Unit Inventory & IMEI)**: Resolved (full `DeviceUnit` tracking with IMEI, battery %, status, and MDMS/warranty support).
  - **F3 (Cost & Profit Margin Tracking)**: Resolved (`cost_price` & `cost_price_at_purchase` tracked, margins displayed in Filament).
  - **F4 (Trade-In Linking to Stock)**: Resolved (trade-ins ingest into sellable inventory via `EXCHANGE_IN` stock movements).
  - **F5 (Nepali Phone Validation & Normalization)**: Resolved (`NepaliPhone` rule, +977 stripping, 98/97 enforcement).
  - **F6 (COD Confirmation & Auto-Expiry)**: Resolved (pre-dispatch phone confirmation and `shop:expire-unconfirmed-orders` hourly cron).
  - **F7 (Repo Hygiene)**: Resolved (no hardcoded passwords/emails, cleaned config and gitignore).

