# Quickstart: Ticket System

**Feature**: 006-ticket-system
**Date**: 2026-04-29

## Prerequisites

- Phases 1-5 complete and migrated (core, auth, departments, employees, tasks)
- Database `dayem_erp` exists with `users`, `departments`, `logs` tables
- XAMPP running, DocumentRoot pointing to `public/`

## Setup Steps

1. **Run migration**:
   ```sql
   -- Execute: database/migrations/008_tickets.sql
   -- via phpMyAdmin or mysql CLI
   ```

2. **Add routes** to `config/routes.php`:
   ```php
   'GET /tickets' => ['TicketController', 'index', 'auth' => true],
   'GET /tickets/create' => ['TicketController', 'createForm', 'auth' => true, 'roles' => ['employee', 'admin']],
   'POST /tickets' => ['TicketController', 'create', 'auth' => true, 'roles' => ['employee', 'admin']],
   'GET /tickets/{id}' => ['TicketController', 'show', 'auth' => true],
   'POST /tickets/{id}/messages' => ['TicketController', 'addMessage', 'auth' => true],
   'PUT /tickets/{id}/status' => ['TicketController', 'updateStatus', 'auth' => true, 'roles' => ['admin']],
   'DELETE /tickets/{id}' => ['TicketController', 'delete', 'auth' => true, 'roles' => ['admin']],
   'GET /tickets/attachment/{id}/{filename}' => ['TicketController', 'serveAttachment', 'auth' => true],
   'GET /api/tickets/unread-count' => ['TicketController', 'unreadCount', 'auth' => true],
   'POST /tickets/{id}/mark-read' => ['TicketController', 'markRead', 'auth' => true],
   ```

3. **Create files**:
   - `app/controllers/TicketController.php`
   - `app/models/TicketModel.php`
   - `app/models/TicketMessageModel.php`
   - `app/models/TicketAttachmentModel.php`
   - `app/views/tickets/index.php`
   - `app/views/tickets/create.php`
   - `app/views/tickets/show.php`
   - `public/js/tickets.js`
   - `database/migrations/008_tickets.sql`

4. **Create upload directory**:
   ```bash
   mkdir -p storage/uploads/tickets
   ```

5. **Verify**: Login as employee → navigate to `/tickets` → create a ticket

## Key Files Reference

| File | Purpose |
|------|---------|
| `app/controllers/TicketController.php` | CRUD + messaging + status + attachments + RBAC |
| `app/models/TicketModel.php` | Ticket queries with search/filter/pagination |
| `app/models/TicketMessageModel.php` | Message CRUD for conversation thread |
| `app/models/TicketAttachmentModel.php` | Attachment metadata + file operations |
| `app/views/tickets/index.php` | Ticket list with filters, pagination, unread indicators |
| `app/views/tickets/create.php` | Create ticket form with file upload |
| `app/views/tickets/show.php` | Ticket detail + chat-style conversation view |
| `public/js/tickets.js` | AJAX handlers for create, message, status, delete |
| `database/migrations/008_tickets.sql` | Create tickets, ticket_messages, ticket_attachments, ticket_reads tables |

## Verification Checklist

- [ ] Migration 008 executed successfully
- [ ] Employee can create ticket with title, description, category, and attachments
- [ ] Admin can view all tickets; employee sees only own tickets
- [ ] Employee can send messages on Open and Replied tickets
- [ ] Employee CANNOT send messages on Closed tickets
- [ ] Admin reply auto-sets status to تم الرد (Replied)
- [ ] Admin can change status to any value (Open, In Review, Replied, Closed)
- [ ] Admin can reopen a Closed ticket
- [ ] Ticket list filters work (status, category, employee name for admin)
- [ ] Unread message indicator shows on ticket list
- [ ] File attachments upload and download correctly
- [ ] File type validation rejects unsupported formats
- [ ] File size validation rejects files > 5MB
- [ ] Soft delete works (ticket disappears from active list)
- [ ] Employee cannot access another employee's ticket (403)
- [ ] All mutations logged in audit log
- [ ] CSRF protection on all forms
- [ ] Arabic RTL throughout