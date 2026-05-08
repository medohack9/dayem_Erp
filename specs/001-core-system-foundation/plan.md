# Implementation Plan: Core System Foundation

**Branch**: `001-core-system-foundation` | **Date**: 2026-04-23 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `specs/001-core-system-foundation/spec.md`

## Summary

Build the foundational architecture for Dayem ERP: a native PHP MVC framework with centralized routing through a single entry point, PDO-based MySQL database connectivity, session management with 30-minute timeout, environment-aware error handling, and a branded RTL Arabic base layout with header, sidebar, and reusable UI components using Tailwind CSS.

## Technical Context

**Language/Version**: PHP 8.x (native, no framework)
**Primary Dependencies**: Tailwind CSS (via CDN for shared hosting compatibility), Vanilla JavaScript (fetch API for AJAX)
**Storage**: MySQL (InnoDB engine) via PDO with prepared statements
**Testing**: Manual browser testing; PHP built-in development server for local; XAMPP for full-stack local testing
**Target Platform**: Web application — Development: XAMPP on Windows; Production: Hostinger shared hosting
**Project Type**: Web application (internal ERP system)
**Performance Goals**: < 2 seconds page load, 50 concurrent users without degradation
**Constraints**: Shared hosting (Hostinger) — no CLI daemons, no Composer autoload in some configurations, limited PHP extensions; file size uploads ≤ 20MB
**Scale/Scope**: < 100 users (single company), ~10 modules planned, Arabic-only RTL interface

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| # | Constitution Rule | Spec Alignment | Status |
|---|-------------------|----------------|--------|
| 1 | MVC Structure (mandatory) — Controllers handle HTTP, Models handle DB, Views handle UI only | FR-004: System MUST separate request handling, data logic, and presentation into distinct layers | PASS |
| 2 | Single Entry Point — All requests through `/public/index.php` | FR-001: System MUST route all incoming HTTP requests through a single entry point | PASS |
| 3 | Modular System Design — Independent, maintainable, extendable modules | FR-012: System MUST organize into modular folder structure for independent module development | PASS |
| 4 | Feature-First Development — DB + Backend + Frontend per feature | Spec covers all three layers: database connection, MVC backend, base layout frontend | PASS |
| 5 | Naming Convention — DB: snake_case, PHP/JS: camelCase | Implementation detail — will enforce in data model and code structure | NOTED |
| 6 | Security — PDO prepared statements, CSRF protection, session timeout 30min | FR-005 (parameterized queries), FR-009 (30-min session timeout), Assumptions (CSRF infrastructure) | PASS |
| 7 | Frontend — RTL, Arabic, Yellow #F4C400 / Black #111111, reusable components | FR-008 (RTL + brand colors), FR-013 (reusable UI components) | PASS |
| 8 | Error Handling — Dev: detailed errors, Prod: hide errors, log internally | FR-010 (environment-aware display), FR-011 (internal logging regardless of mode) | PASS |
| 9 | Soft Delete — `deleted_at` column on major entities | Not applicable this phase — no entity deletion. Will enforce in future phases. | N/A |
| 10 | Audit Fields — `created_at`, `updated_at` | Will include in data model for any tables created this phase | NOTED |
| 11 | AJAX/JSON Communication — fetch API, minimize page reloads | FR-013 references interactive components; base infrastructure supports AJAX | PASS |
| 12 | Logging — All actions logged | FR-011 covers error logging; full action logging is Phase 9 scope | PARTIAL — acceptable for this phase |
| 13 | Version Control — Git, private repo, clear commit messages | Branch created, git workflow established | PASS |

**Gate Result**: PASS — No violations. All applicable constitution rules are satisfied by the spec.

## Project Structure

### Documentation (this feature)

```text
specs/001-core-system-foundation/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (HTTP route contracts)
└── tasks.md             # Phase 2 output (created by /speckit.tasks)
```

### Source Code (repository root)

```text
public/
├── index.php            # Single entry point — all requests routed here
├── css/                 # Compiled/static CSS
├── js/                  # Static JavaScript files
└── .htaccess            # URL rewriting rules

app/
├── controllers/         # Request handlers (one per module)
│   └── HomeController.php
├── models/              # Database logic (one per entity)
│   └── BaseModel.php
├── views/               # UI templates
│   ├── layouts/
│   │   └── main.php     # Base layout (header + sidebar + content area)
│   ├── components/      # Reusable UI components (buttons, tables, forms, modals)
│   ├── errors/          # Error pages (404, 500, generic)
│   └── home/
│       └── index.php
└── core/                # Framework internals
    ├── Router.php       # URL-to-controller mapping
    ├── Controller.php   # Base controller class
    ├── Model.php        # Base model with PDO connection
    ├── Database.php     # PDO connection singleton
    ├── Session.php      # Session management with timeout
    ├── ErrorHandler.php # Environment-aware error handling
    └── App.php          # Application bootstrap

config/
├── app.php              # Application settings (environment mode, app name)
├── database.php         # Database connection parameters
└── routes.php           # Route definitions

storage/
├── logs/                # Error logs
└── uploads/             # File uploads (organized by module in future phases)
```

**Structure Decision**: Single web application following the PHP MVC pattern with clear separation: `public/` as the web root (single entry point), `app/` for application code organized by MVC layers, `config/` for settings, `storage/` for runtime data. This aligns with the constitution's modular design — each future module adds its own controller, model(s), and views without touching the core framework.

## Complexity Tracking

No constitution violations to justify. All design decisions align with the established rules.
