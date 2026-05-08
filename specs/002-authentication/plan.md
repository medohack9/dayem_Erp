# Implementation Plan: Authentication

**Branch**: `002-authentication` | **Date**: 2026-04-25 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/002-authentication/spec.md`

## Summary

Build a secure authentication system for Dayem ERP: login via email or phone with bcrypt password hashing, role-based access control (admin/employee), session management with single-session enforcement, 30-minute timeout, CSRF protection, account lockout after 5 failed attempts, and a dedicated auth_logs audit trail. Extends the Phase 1 core MVC framework.

## Technical Context

**Language/Version**: PHP 8.x (native, no framework)
**Primary Dependencies**: Tailwind CSS (CDN), Vanilla JavaScript (fetch API), existing Phase 1 MVC core
**Storage**: MySQL (InnoDB) via PDO with prepared statements
**Testing**: Manual browser testing; PHP built-in server for local; XAMPP for full-stack testing
**Target Platform**: Web application — Development: XAMPP on Windows; Production: Hostinger shared hosting
**Project Type**: Web application (internal ERP system, authentication module)
**Performance Goals**: Login response < 3 seconds; 50 concurrent users without degradation
**Constraints**: Shared hosting (no CLI daemons, no Composer in some configurations); bcrypt 72-byte password limit
**Scale/Scope**: < 100 users (single company); single active session per user; Arabic-only RTL interface

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| # | Constitution Rule | Spec Alignment | Status |
|---|-------------------|----------------|--------|
| 1 | MVC Structure (mandatory) | AuthController handles HTTP, UserModel/AuthModel handle DB, views handle UI | PASS |
| 2 | Single Entry Point | All auth routes go through `/public/index.php` → Router → AuthController | PASS |
| 3 | Modular System Design | Authentication is an independent module with its own controller, model, and views | PASS |
| 4 | Feature-First Development | Auth includes DB (users + auth_logs tables), backend (login/logout/RBAC), frontend (login page) | PASS |
| 5 | Naming Convention | DB: snake_case (users, auth_logs, failed_login_attempts), PHP: camelCase (AuthController, UserModel) | PASS |
| 6 | Security | password_hash() with bcrypt, PDO prepared statements, CSRF tokens, 30-min timeout, account lockout | PASS |
| 7 | Frontend | RTL Arabic, Yellow #F4C400 / Black #111111, reusable form components from Phase 1 | PASS |
| 8 | Error Handling | Dev: detailed errors, Prod: friendly Arabic pages — uses existing ErrorHandler | PASS |
| 9 | Soft Delete | `users` table includes `deleted_at` column for soft delete | PASS |
| 10 | Audit Fields | `users` and `auth_logs` include `created_at`; `users` includes `updated_at` | PASS |
| 11 | AJAX/JSON Communication | Login form uses fetch API with JSON responses | PASS |
| 12 | Logging | All auth attempts logged to `auth_logs` table | PASS |
| 13 | Version Control | Git, branch `002-authentication`, clear commit messages | PASS |

**Gate Result**: PASS — No violations. All 13 constitution rules are satisfied by the spec.

## Project Structure

### Documentation (this feature)

```text
specs/002-authentication/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── http-routes.md   # Phase 1 output
└── tasks.md             # Phase 2 output (by /speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── controllers/
│   └── AuthController.php     # Login, logout, session handling
├── models/
│   ├── UserModel.php           # User CRUD + auth queries
│   └── AuthLogModel.php        # Login attempt logging
├── views/
│   └── auth/
│       └── login.php           # Login form view
├── core/
│   ├── App.php                 # Update: add auth middleware hook
│   ├── Auth.php                # NEW: authentication & authorization helper
│   ├── Controller.php          # Update: add requireAuth/requireGuest methods
│   ├── Router.php              # Update: add auth routes
│   └── Session.php             # Update: add user session data methods
├── middleware/                  # NEW directory
│   └── AuthMiddleware.php      # Route protection & role checking

config/
├── app.php                     # Update: add auth config (lockout settings)
└── routes.php                  # Update: add auth routes + middleware

database/migrations/
└── 002_authentication.sql       # users + auth_logs tables + admin seed

public/
├── js/
│   └── app.js                  # Update: add login AJAX handler
└── css/
    └── custom.css              # Update: add login page styles (if needed)

storage/uploads/                 # No changes (no file uploads in auth)
```

**Structure Decision**: Extends the existing Phase 1 MVC structure. New files are added within the same `app/controllers/`, `app/models/`, `app/views/` convention. A new `app/middleware/` directory is introduced for authorization middleware. The core framework files (`Auth.php`, `AuthMiddleware.php`, `AuthLogModel.php`) follow the existing namespace conventions (`App\Core`, `App\Middleware`, `App\Models`).

## Complexity Tracking

No constitution violations to justify. All design decisions align with the established rules.