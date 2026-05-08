# Implementation Plan: Tasks Management

**Branch**: `005-tasks` | **Date**: 2026-04-25 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/005-tasks/spec.md`

## Summary

Build a Tasks Management module for Dayem ERP following the established MVC patterns (native PHP, MySQL, Tailwind CSS, Vanilla JS, Arabic RTL). Admins can create, assign, edit, and delete tasks; employees can view their assigned tasks and update status. The module integrates with the existing auth/RBAC system, employee and department models, and audit logging.

## Technical Context

**Language/Version**: PHP 8.x (native, no framework)
**Primary Dependencies**: Custom MVC framework (App\Core), MySQL InnoDB, Tailwind CSS (CDN), Vanilla JS (fetch API)
**Storage**: MySQL InnoDB (new `tasks` table via migration 006)
**Testing**: Manual testing via browser; no automated test framework
**Target Platform**: XAMPP (dev) / Hostinger shared hosting (prod)
**Project Type**: Web application (internal ERP)
**Performance Goals**: Task list loads < 2s with 500+ records; pagination at 15 per page
**Constraints**: No CLI access on prod; shared hosting; Arabic RTL only; session-based auth
**Scale/Scope**: ~50-200 employees, ~1000-5000 tasks

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| MVC strict separation | PASS | TaskController → TaskModel → views/tasks/ |
| Single entry point (public/index.php) | PASS | All routes via config/routes.php |
| Modular design | PASS | Tasks as independent module |
| password_hash for passwords | N/A | No password handling in tasks module |
| CSRF on all forms | PASS | All task forms include CSRF token |
| AJAX for form submissions | PASS | Create/edit/delete via fetch() JSON |
| Soft delete (deleted_at) | PASS | tasks table includes deleted_at |
| Audit fields (created_at, updated_at) | PASS | Included in table design |
| Arabic RTL, brand colors | PASS | All views Arabic RTL, #F4C400/#111111/#666666 |
| RBAC enforcement (backend) | PASS | Admin: full CRUD; Employee: read own + update status |
| PDO prepared statements | PASS | Using existing Model::query() with param binding |
| File storage organized by module | N/A | No file uploads in tasks module |
| Logging all actions | PASS | Using existing LogModel |

**No violations. Gate passed.**

## Project Structure

### Documentation (this feature)

```text
specs/005-tasks/
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
│   └── TaskController.php
├── models/
│   └── TaskModel.php
├── views/
│   └── tasks/
│       ├── index.php      # Task list (admin: all, employee: own)
│       ├── create.php     # Create task form (admin only)
│       └── edit.php       # Edit task form (admin only)
public/
└── js/
    └── tasks.js           # AJAX handlers for task CRUD
config/
└── routes.php             # New task routes added
database/
└── migrations/
    └── 006_tasks.sql       # Tasks table migration
```

**Structure Decision**: Follows the established pattern used by departments and employees modules. Task-related code is isolated in its own controller, model, and views directory.

## Complexity Tracking

No constitution violations to justify.