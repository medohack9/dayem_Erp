# Implementation Plan: Employee Management

**Branch**: `004-employees` | **Date**: 2026-04-25 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/004-employees/spec.md`

## Summary

Implement full CRUD for employee management, extending the existing `users` table with employee-specific fields (national_id, birth_date, salary, hire_date, employee_code). Admins can list/search/filter employees in a table view, create new employees with auto-generated codes, edit all fields including status changes, and soft-delete with manager reassignment handling. All operations are logged via the existing LogModel. UI matches the Figma EmployeesPage and AddEmployeePage designs with Arabic RTL layout.

## Technical Context

**Language/Version**: PHP 8.x (native, custom MVC)
**Primary Dependencies**: Tailwind CSS (CDN), Vanilla JavaScript (no frameworks), MySQL InnoDB
**Storage**: MySQL (existing `users` table extended with employee columns)
**Testing**: Manual testing via browser/XAMPP
**Target Platform**: XAMPP (dev) / Hostinger shared hosting (prod)
**Project Type**: Web application (server-rendered PHP MVC)
**Performance Goals**: List <2s for 500 employees, form submit <1s response
**Constraints**: No Composer, shared hosting (no CLI daemons), Arabic-only RTL UI
**Scale/Scope**: ~500 employees max, single-tenant, single-company

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| MVC separation | PASS | EmployeeController → EmployeeModel → views/employees/* |
| Single entry point | PASS | All routes via /public/index.php → Router |
| Modular design | PASS | Employees is an independent module |
| Feature-first dev | PASS | Migration + Model + Controller + Views + JS per feature |
| snake_case DB / camelCase PHP | PASS | employee_code, national_id in DB; generateEmployeeCode() in PHP |
| Soft delete required | PASS | Users already have deleted_at column |
| CSRF on all forms | PASS | Reuse existing CSRF token pattern |
| AJAX for form submits | PASS | Reuse departments.js pattern |
| Arabic RTL UI | PASS | Match Figma designs |
| Audit logging | PASS | Reuse LogModel |
| RBAC at backend | PASS | Admin-only routes via routes.php roles |
| Password hashing | PASS | password_hash() for new employee passwords |
| No plain text passwords | PASS | N/A for this phase (employees created without password initially) |
| Input validation | PASS | Server-side + client-side |

## Project Structure

### Documentation (this feature)

```text
specs/004-employees/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── http-routes.md   # Phase 1 output
└── checklists/
    └── requirements.md  # Already exists
```

### Source Code (repository root)

```text
app/
├── controllers/
│   ├── AuthController.php        # Existing
│   ├── DepartmentController.php  # Existing
│   ├── HomeController.php        # Existing
│   └── EmployeeController.php    # NEW
├── core/
│   ├── Auth.php                  # Existing (needs update for status check)
│   ├── Controller.php            # Existing
│   ├── Database.php               # Existing
│   ├── Model.php                  # Existing
│   └── Router.php                 # Existing
├── models/
│   ├── DepartmentModel.php       # Existing (add method for default salary)
│   ├── EmployeeModel.php         # NEW
│   ├── LogModel.php              # Existing
│   └── UserModel.php             # Existing (may need minor updates)
├── views/
│   ├── employees/
│   │   ├── index.php             # NEW - employee list
│   │   ├── create.php            # NEW - add employee form
│   │   └── edit.php              # NEW - edit employee form
│   ├── components/
│   │   ├── sidebar.php           # Existing (update active link)
│   │   └── header.php            # Existing
│   └── layouts/
│       └── main.php               # Existing
├── helpers.php                    # Existing

config/
└── routes.php                     # Existing (add employee routes)

database/
└── migrations/
    └── 004_employees.sql          # NEW (already created, needs fix for DB name)

public/
└── js/
    └── employees.js               # NEW - AJAX handlers
```

**Structure Decision**: Single project following existing MVC pattern. All employee files follow the same convention established by the departments module.

## Complexity Tracking

No constitution violations to justify.