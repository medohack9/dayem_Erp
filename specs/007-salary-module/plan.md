# Implementation Plan: Salary Module

**Branch**: `007-salary-module` | **Date**: 2026-04-29 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/007-salary-module/spec.md`

## Summary

Implement a Salary Module for Dayem ERP to track and manage payroll. Key features include monthly salary generation for all active employees, payment confirmation with audit tracking, individual salary configuration overrides (stored in a dedicated table), and a recalculation mechanism for unpaid records. The module will follow the project's native PHP MVC pattern, ensuring strict RBAC (Admin: full access, Employee: own history only) and full Arabic RTL support.

## Technical Context

**Language/Version**: PHP 8.x (native, no framework)  
**Primary Dependencies**: Custom MVC framework (App\Core), MySQL InnoDB, Tailwind CSS (CDN), Vanilla JS (fetch API)  
**Storage**: MySQL InnoDB (new `salaries` and `salary_configs` tables via migration 009)  
**Testing**: Manual testing via browser; no automated test framework  
**Target Platform**: XAMPP (dev) / Hostinger shared hosting (prod)
**Project Type**: Web application (internal ERP)  
**Performance Goals**: Salary list loads < 2s with 500+ records; generation for 100+ employees < 5s; pagination at 15 per page  
**Constraints**: No CLI access on prod; shared hosting; Arabic RTL only; session-based auth; single currency (EGP)  
**Scale/Scope**: ~50-200 employees, ~1200-2400 salary records per year

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| MVC strict separation | PASS | SalaryController → SalaryModel → views/salaries/ |
| Single entry point (public/index.php) | PASS | All routes via config/routes.php |
| Modular design | PASS | Salaries as independent module |
| password_hash for passwords | N/A | No password handling in this module |
| CSRF on all forms | PASS | All salary actions (generation, payment, recalc) include CSRF token |
| AJAX for form submissions | PASS | Payment confirmation and Recalculation via fetch() JSON |
| Soft delete (deleted_at) | PASS | `salaries` table includes `deleted_at` |
| Audit fields (created_at, updated_at) | PASS | Included in all new tables |
| Arabic RTL, brand colors | PASS | All views Arabic RTL, #F4C400/#111111 |
| RBAC enforcement (backend) | PASS | Admin: full access; Employee: own records only |
| PDO prepared statements | PASS | Using existing Model::query() with param binding |
| Logging all actions | PASS | Using existing LogModel for generation and payment |

**No violations. Gate passed.**

## Project Structure

### Documentation (this feature)

```text
specs/007-salary-module/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   └── http-routes.md
└── checklists/
    └── requirements.md
```

### Source Code (repository root)

```text
app/
├── controllers/
│   └── SalaryController.php
├── models/
│   ├── SalaryModel.php
│   └── SalaryConfigModel.php
├── views/
│   └── salaries/
│       ├── index.php        # Admin: All salaries + Summary; Employee: History
│       ├── config.php       # Admin: Manage employee salary overrides
│       └── components/      # Reusable UI components
public/
└── js/
    └── salaries.js          # AJAX handlers for payment and recalc
config/
└── routes.php               # New salary routes added
database/
migrations/
└── 009_salaries.sql         # New tables for salaries and configs
```

**Structure Decision**: Follows the established modular MVC pattern. Salary configuration is separated from the main employee list to avoid cluttering the Employee module, while still being linked by `user_id`.

## Complexity Tracking

No constitution violations to justify.
