# Implementation Plan: Departments Module

**Branch**: `003-departments` | **Date**: 2026-04-25 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/003-departments/spec.md`

## Summary

Build the Departments CRUD module for Dayem ERP allowing admins to create, view, edit, and soft-delete departments with employee reassignment. Employees can view departments in read-only mode. Each department has a name (unique, case-insensitive), an optional manager (one-to-one with employees), an optional default salary, and a computed employee count. Delete uses a modal dialog for reassignment. All forms use AJAX with CSRF protection. Arabic RTL UI throughout.

## Technical Context

**Language/Version**: PHP 8.x (native, no framework)
**Primary Dependencies**: Custom MVC (Router, Controller, Model, Database PDO), Tailwind CSS (CDN), Vanilla JS (fetch API)
**Storage**: MySQL 8.x (InnoDB, utf8mb4)
**Testing**: Manual verification via XAMPP browser testing
**Target Platform**: XAMPP (dev) / Hostinger shared hosting (prod)
**Project Type**: Web application (server-rendered PHP + AJAX)
**Performance Goals**: Department list < 2s with 100+ records, search < 1s
**Constraints**: No Composer, no JS libraries, no CLI daemons, single-server
**Scale/Scope**: Internal HR tool, ~50 departments max, ~500 employees max

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Rule | Status | Notes |
|------|--------|-------|
| MVC strict separation | PASS | Departments follow Controller→Model→View pattern |
| Single entry point | PASS | All routes via /public/index.php |
| Modular system design | PASS | New DepartmentModel, DepartmentController, views |
| Feature-first development | PASS | DB + backend + frontend delivered together |
| snake_case DB, camelCase PHP | PASS | `departments` table, `DepartmentModel` class |
| No duplicated logic | PASS | Reuse Model base class, Auth, Helpers |
| Password hashing | N/A | No password handling in this module |
| Prepared statements (PDO) | PASS | All queries via Model::query() with parameters |
| CSRF protection | PASS | All forms include CSRF token |
| Input validation/sanitization | PASS | Server-side validation on create/edit |
| Soft delete (deleted_at) | PASS | FR-005/FR-007 require soft delete |
| Audit fields (created_at, updated_at) | PASS | Included in departments table |
| Foreign keys | PASS | manager_id FK to users.id |
| AJAX (fetch API) | PASS | All create/edit/delete via AJAX |
| RTL Arabic UI | PASS | All labels in Arabic, RTL layout |
| Admin/employee RBAC | PASS | Admin manages, employee view-only |
| Logging all actions | PASS | FR-013 requires audit logging |

**No violations. Gate passed.**

## Project Structure

### Documentation (this feature)

```text
specs/003-departments/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── http-routes.md   # HTTP route contracts
├── checklists/
│   └── requirements.md  # Quality checklist
└── tasks.md             # Phase 2 output (by /speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── controllers/
│   └── DepartmentController.php    # CRUD + list + delete/reassign
├── models/
│   ├── DepartmentModel.php          # DB queries for departments
│   └── LogModel.php                 # Audit logging (if not existing)
├── views/
│   └── departments/
│       ├── index.php                # Department list (admin + employee)
│       ├── create.php               # Create form
│       ├── edit.php                 # Edit form
│       └── delete-modal.php         # Delete confirmation modal partial
├── core/
│   └── (existing: App, Auth, Controller, Database, ErrorHandler, Model, Router, Session)
├── middleware/
│   └── AuthMiddleware.php           # (existing)
└── helpers.php                      # (existing)

config/
└── routes.php                       # Department routes added

public/
└── js/
    └── departments.js               # AJAX handlers for CRUD + delete modal

database/
└── migrations/
    └── 003_departments.sql           # departments table + indexes
```

**Structure Decision**: Follows the existing MVC pattern established in Phases 1-2. No new directories needed beyond `views/departments/`.

## Complexity Tracking

No constitution violations. Table not needed.