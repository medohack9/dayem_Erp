# Implementation Plan: Ticket System

**Branch**: `006-ticket-system` | **Date**: 2026-04-29 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `specs/006-ticket-system/spec.md`

## Summary

Build a Ticket System module for Dayem ERP following the established MVC patterns (native PHP, MySQL, Tailwind CSS, Vanilla JS, Arabic RTL). Employees can create support tickets with categories and attachments; admins can view all tickets, reply with chat-style messaging, change status (Open → In Review → Replied → Closed), and manage ticket lifecycle. The module integrates with the existing auth/RBAC system, employee model, and audit logging.

## Technical Context

**Language/Version**: PHP 8.x (native, no framework)
**Primary Dependencies**: Custom MVC framework (App\Core), MySQL InnoDB, Tailwind CSS (CDN), Vanilla JS (fetch API)
**Storage**: MySQL InnoDB (new `tickets`, `ticket_messages`, `ticket_attachments` tables via migration 008)
**Testing**: Manual testing via browser; no automated test framework
**Target Platform**: XAMPP (dev) / Hostinger shared hosting (prod)
**Project Type**: Web application (internal ERP)
**Performance Goals**: Ticket list loads < 2s with 500+ records; chat view loads < 1s; pagination at 15 per page
**Constraints**: No CLI access on prod; shared hosting; Arabic RTL only; session-based auth; file uploads ≤ 5MB each
**Scale/Scope**: ~50-200 employees, ~1000-5000 tickets

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| MVC strict separation | PASS | TicketController → TicketModel → views/tickets/ |
| Single entry point (public/index.php) | PASS | All routes via config/routes.php |
| Modular design | PASS | Tickets as independent module |
| password_hash for passwords | N/A | No password handling in tickets module |
| CSRF on all forms | PASS | All ticket forms and AJAX requests include CSRF token |
| AJAX for form submissions | PASS | Create/message/status/delete via fetch() JSON |
| Soft delete (deleted_at) | PASS | tickets table includes deleted_at |
| Audit fields (created_at, updated_at) | PASS | Included in all new tables |
| Arabic RTL, brand colors | PASS | All views Arabic RTL, #F4C400/#111111 |
| RBAC enforcement (backend) | PASS | Admin: full access; Employee: own tickets only, message on non-closed |
| PDO prepared statements | PASS | Using existing Model::query() with param binding |
| File storage organized by module | PASS | Attachments stored in /storage/uploads/tickets/ |
| File size ≤ 20MB (constitution) | PASS | Module enforces stricter 5MB per file; within constitution limit |
| Logging all actions | PASS | Using existing LogModel |

**No violations. Gate passed.**

## Project Structure

### Documentation (this feature)

```text
specs/006-ticket-system/
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
│   └── TicketController.php
├── models/
│   ├── TicketModel.php
│   ├── TicketMessageModel.php
│   └── TicketAttachmentModel.php
├── views/
│   └── tickets/
│       ├── index.php        # Ticket list (admin: all, employee: own)
│       ├── create.php       # Create ticket form (employee)
│       └── show.php         # Ticket detail / chat view (both roles)
public/
└── js/
    └── tickets.js           # AJAX handlers for ticket CRUD + messaging
config/
└── routes.php               # New ticket routes added
database/
└── migrations/
    └── 008_tickets.sql       # Tickets, messages, attachments tables
```

**Structure Decision**: Follows the established pattern used by tasks module. Ticket-related code is isolated in its own controller, models, and views directory. The show.php view serves as the chat/messaging interface (both employee and admin), replacing the conventional edit.php since tickets use a conversation-based interaction model.

## Complexity Tracking

No constitution violations to justify.