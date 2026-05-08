# Tasks: Ticket System

**Input**: Design documents from `/specs/006-ticket-system/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: No automated test framework — manual testing via browser per project convention.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Database schema and file system setup for the tickets module

- [x] T001 Create migration file `database/migrations/008_tickets.sql` with `tickets`, `ticket_messages`, `ticket_attachments`, and `ticket_reads` tables per data-model.md
- [x] T002 [P] Create upload directory `storage/uploads/tickets/` for file attachments
- [x] T003 Add ticket routes to `config/routes.php` per contracts/http-routes.md

**Checkpoint**: Migration executed, upload directory exists, routes registered

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core models that ALL user stories depend on. MUST be complete before any user story work begins.

- [x] T004 Create `app/models/TicketModel.php` with CRUD methods: findAllActive (with search/filter/pagination by status, category, created_by), findById, generateTicketCode, create, update, softDelete — following TaskModel pattern
- [x] T005 [P] Create `app/models/TicketMessageModel.php` with methods: findByTicketId (chronological order), create, countByTicketId
- [x] T006 [P] Create `app/models/TicketAttachmentModel.php` with methods: findByTicketId, findById, create, deleteById, countByTicketId
- [x] T007 [P] Create `app/models/TicketReadModel.php` with methods: findByTicketAndUser, upsert (mark as read), countUnreadForUser

**Checkpoint**: All four models created and queryable. User story implementation can begin.

---

## Phase 3: User Story 1 - Create a Support Ticket (Priority: P1) 🎯 MVP

**Goal**: Employees can create tickets with title, description, category, and optional file attachments. Admins see all tickets.

**Independent Test**: Login as employee → create a ticket with all fields → verify it appears in your ticket list. Login as admin → verify ticket appears in admin ticket list.

### Implementation for User Story 1

- [x] T008 [US1] Create `app/controllers/TicketController.php` with `createForm` method that renders the create ticket form with category dropdown and file upload area
- [x] T009 [US1] Create `app/views/tickets/create.php` with RTL Arabic form: title input, description textarea, category dropdown (تقرير مشكلة / طلب صيانة / طلب معلومات / أخرى), file upload input (max 10, each max 5MB), CSRF token, submit button
- [x] T010 [US1] Add `create` method to `app/controllers/TicketController.php` handling multipart/form-data: validate title (required, max 200), description (required, max 5000), category (required, valid enum), validate file constraints (max 10 files, max 5MB each, allowed MIME types), generate ticket_code (TKT-NNN format), save ticket, process attachments via TicketAttachmentModel, log action, return JSON response
- [x] T011 [US1] Create `public/js/tickets.js` with AJAX handler for ticket creation form submission: collect form data + files, validate client-side, show Arabic error messages, handle success/error responses
- [x] T012 [US1] Add file upload processing to `app/controllers/TicketController.php`: validate MIME type against allowed list, generate unique stored_name, move to `storage/uploads/tickets/{ticket_id}/`, save metadata via TicketAttachmentModel

**Checkpoint**: Employee can create a ticket with attachments and see it saved. Admin can view all tickets. File uploads work and are stored correctly.

---

## Phase 4: User Story 2 - Chat-Style Messaging on a Ticket (Priority: P1)

**Goal**: Employees and admins can send and receive messages within a ticket in a chat-style interface. Admin replies auto-set status to "تم الرد". Closed tickets block messaging.

**Independent Test**: Create a ticket, have the employee post a message, then admin replies — both messages appear in the conversation. Verify admin reply changes status to "تم الرد". Verify closed ticket blocks messaging.

### Implementation for User Story 2

- [x] T013 [US2] Create `app/views/tickets/show.php` with chat-style layout: ticket header (title, category, status badge, created date), conversation thread showing messages with sender name, sender role badge (admin/employee), timestamp, and visual distinction between admin and employee messages, message input area at bottom (disabled when ticket is closed with Arabic notice), file upload area for reply attachments
- [x] T014 [US2] Add `show` method to `app/controllers/TicketController.php`: load ticket by ID, enforce RBAC (employee can only see own tickets — 403 otherwise), load messages via TicketMessageModel::findByTicketId, load attachments via TicketAttachmentModel::findByTicketId, mark ticket as read via TicketReadModel, render show view with ticket, messages, attachments data
- [x] T015 [US2] Add `addMessage` method to `app/controllers/TicketController.php`: validate CSRF, verify ticket exists and is not closed (reject with 403 + Arabic message if closed and sender is employee), validate content (required, max 5000), if sender is admin auto-set ticket status to "تم الرد", save message via TicketMessageModel, process reply attachments (max 10 files), mark ticket as read for sender via TicketReadModel, log action, return JSON with message and attachments
- [x] T016 [US2] Add message submission AJAX handler to `public/js/tickets.js`: collect message content + files, submit via fetch(), append new message to conversation thread in real-time, handle file upload progress, handle closed-ticket rejection with Arabic error toast

**Checkpoint**: Employee can send messages on their own Open/Replied tickets. Admin can reply and status auto-changes to "تم الرد". Closed tickets show disabled input with Arabic notice. Messages display in chat-style chronological order.

---

## Phase 5: User Story 3 - View and Filter Tickets (Priority: P2)

**Goal**: Admin sees all tickets with filters by status, category, and employee name. Employee sees only their own tickets with status and category filters. Both views support search and pagination.

**Independent Test**: Admin filters by status "مفتوح" and only open tickets appear. Employee views their own tickets. Pagination works at 15 per page.

### Implementation for User Story 3

- [x] T017 [US3] Add `index` method to `app/controllers/TicketController.php`: load tickets via TicketModel::findAllActive with search, status, category, and created_by filters, enforce RBAC (employee: filter to Auth::id(), admin: see all with employee name filter), load departments for filter dropdowns, render index view with pagination data
- [x] T018 [US3] Create `app/views/tickets/index.php` with RTL Arabic ticket list: search bar, filter dropdowns (status: all/مفتوح/قيد المراجعة/تم الرد/مغلق, category: all/تقرير مشكلة/طلب صيانة/طلب معلومات/أخرى, employee name [admin only]), status badges with colors (مفتوح=blue, قيد المراجعة=yellow, تم الرد=green, مغلق=gray), unread message indicator per ticket, pagination controls, create button (employee), ticket rows linking to show page
- [x] T019 [US3] Add ticket list AJAX handlers to `public/js/tickets.js`: filter changes trigger page reload with query params, search with real-time debounce, pagination click handlers, unread badge animation

**Checkpoint**: Admin ticket list shows all tickets with all filters working. Employee ticket list shows only their own tickets. Pagination works. Unread indicators display correctly.

---

## Phase 6: User Story 4 - Update Ticket Status (Priority: P2)

**Goal**: Admin can change ticket status (Open → In Review → Replied → Closed, including reopening). Employees can only send messages, not change status directly. Status changes are logged.

**Independent Test**: Admin changes status from مفتوح to قيد المراجعة — badge updates. Admin closes a ticket — messaging is blocked. Admin reopens — messaging enabled.

### Implementation for User Story 4

- [x] T020 [US4] Add `updateStatus` method to `app/controllers/TicketController.php`: validate CSRF, admin-only (403 for employees), validate status value against allowed enum (مفتوح/قيد المراجعة/تم الرد/مغلق), update via TicketModel, log action via LogModel, return JSON response
- [x] T021 [US4] Add status change UI component to `app/views/tickets/show.php`: admin-only status dropdown in ticket header area, status options (مفتوح/قيد المراجعة/تم الرد/مغلق), confirmation modal for close action, visual status change feedback
- [x] T022 [US4] Add status change AJAX handler to `public/js/tickets.js`: submit status change via fetch PUT, update status badge in real-time, show confirmation toast, disable/enable message input area based on status (closed = disabled)

**Checkpoint**: Admin can change ticket status to any value. Status badge updates in real-time. Closing a ticket disables the message input. Reopening enables it. Employees cannot change status.

---

## Phase 7: User Story 5 - Admin Reply with File Attachments (Priority: P2)

**Goal**: Admin can reply to tickets with text and file attachments. Reply auto-sets status to "تم الرد". Employee can see and download attachments.

**Independent Test**: Admin opens a ticket, types a reply, attaches a file → reply appears in conversation with download link. Employee views the ticket and can download the attachment.

### Implementation for User Story 5

- [x] T023 [US5] Add `serveAttachment` method to `app/controllers/TicketController.php`: validate user permissions (admin = any ticket, employee = own ticket only), find attachment by ID via TicketAttachmentModel, verify file exists in `storage/uploads/tickets/`, serve file with appropriate Content-Type header, return 403 if unauthorized, return 404 if not found
- [x] T024 [US5] Update `app/views/tickets/show.php` to display file attachments in messages: show file icon based on type, original filename, file size, download link using `/tickets/attachment/{id}/{filename}` route, attachment preview for images (thumbnail)
- [x] T025 [US5] Update `public/js/tickets.js` with file upload progress indicator for reply messages: show upload percentage, validate file type and size client-side before upload (max 5MB each, max 10 files, allowed types), show Arabic error for rejected files, handle partial upload failures gracefully

**Checkpoint**: Admin can reply with file attachments. Files are stored securely and served via controlled endpoint. Employee can download their own ticket attachments. File validation works both client-side and server-side.

---

## Phase 8: User Story 6 - Close and Reopen Tickets (Priority: P3)

**Goal**: Admin can close a ticket (blocking messaging) and reopen a closed ticket. Employee sees a notice on closed tickets preventing messages.

**Independent Test**: Admin closes a ticket → message input is disabled with Arabic notice. Admin reopens → messaging re-enabled. Employee sees closed notice on their own closed ticket.

### Implementation for User Story 6

- [x] T026 [US6] Add close/reopen logic to `app/controllers/TicketController.php` updateStatus method (already created in T020 — extend it): when status changes to مغلق, log the close action; when reopens from مغلق to مفتوح, log the reopen action; ensure message input state is communicated in the ticket data response
- [x] T027 [US6] Update `app/views/tickets/show.php` to conditionally render message input: if ticket status is مغلق, show disabled textarea with Arabic notice "هذه التذكرة مغلقة ولا يمكن إرسال رسائل جديدة"; admin sees reopen button; hidden message form entirely for employees on closed tickets
- [x] T028 [US6] Add close confirmation modal and reopen button to `public/js/tickets.js`: admin clicks close → confirmation modal with Arabic text "هل أنت متأكد من إغلاق هذه التذكرة؟" → confirms → status changes; admin clicks reopen → status changes to مفتوح → message input re-enables; toast notification for both actions

**Checkpoint**: Admin can close and reopen tickets. Closed tickets block messaging with Arabic notice. Employees see the notice and cannot send messages on closed tickets.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Soft delete, audit logging, RBAC enforcement, and sidebar integration

- [x] T029 [P] Add `delete` method to `app/controllers/TicketController.php`: admin-only, CSRF validation, soft-delete via TicketModel::softDelete, log action, return JSON
- [x] T030 [P] Add `unreadCount` method to `app/controllers/TicketController.php`: return JSON count of tickets with unread messages for the current user
- [x] T031 [P] Add `markRead` method to `app/controllers/TicketController.php`: upsert ticket_reads record for current user and ticket, return JSON success
- [x] T032 [P] Add ticket links to the sidebar navigation in the shared layout: add "التذاكر" link with ticket icon, badge showing unread count (call /api/tickets/unread-count), ensure employee and admin both see the link
- [x] T033 [P] Add audit logging calls to all TicketController mutation methods: create → logAction('ticket', $id, 'create'), addMessage → logAction('ticket', $id, 'reply'), updateStatus → logAction('ticket', $id, 'status_change', from/to), delete → logAction('ticket', $id, 'delete')
- [x] T034 Run quickstart.md verification checklist: migration 008 executed, employee ticket creation works, admin view all tickets, messaging works, status changes work, file uploads and downloads work, soft delete works, CSRF on all forms, Arabic RTL throughout

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Foundational — no dependencies on other stories
- **US2 (Phase 4)**: Depends on Foundational + US1 (needs ticket to exist for messaging)
- **US3 (Phase 5)**: Depends on Foundational + US1 (needs tickets for listing)
- **US4 (Phase 6)**: Depends on Foundational + US2 (status display in show view)
- **US5 (Phase 7)**: Depends on Foundational + US2 (attachment display in message view)
- **US6 (Phase 8)**: Depends on Foundational + US4 (uses status change mechanism)
- **Polish (Phase 9)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (Create Ticket)**: Can start after Foundational — No dependencies on other stories
- **US2 (Chat Messaging)**: Depends on US1 (needs tickets to message on)
- **US3 (View & Filter)**: Depends on US1 (needs tickets to list)
- **US4 (Status Update)**: Depends on US2 (status controls appear in show view)
- **US5 (Admin Reply + Attachments)**: Depends on US2 (adds attachments to messaging)
- **US6 (Close & Reopen)**: Depends on US4 (uses status mechanism)

### Within Each User Story

- Models before services
- Controller methods before views
- Views before JavaScript handlers
- Story complete before moving to next priority

### Parallel Opportunities

- T002, T003 can run in parallel with T001
- T005, T006, T007 can run in parallel (different model files)
- T023, T024, T025 can run in parallel within US5
- T029, T030, T031, T032, T033 can run in parallel in Polish phase

---

## Parallel Example: Foundational

```text
# After migration runs, all models can be created in parallel:
Task T004: Create TicketModel.php
Task T005: Create TicketMessageModel.php
Task T006: Create TicketAttachmentModel.php
Task T007: Create TicketReadModel.php
```

## Parallel Example: User Story 1

```text
# Models are done, controller and view can be parallelized:
Task T008: Create TicketController::createForm
Task T009: Create tickets/create.php view
# Then controller logic depends on view structure:
Task T010: Add TicketController::create method
Task T011: Create tickets.js AJAX handlers
Task T012: Add file upload processing
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (migration + routes + upload directory)
2. Complete Phase 2: Foundational (all 4 models)
3. Complete Phase 3: User Story 1 (create ticket + list)
4. **STOP and VALIDATE**: Employee can create a ticket, admin sees it
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Create tickets with attachments → Test → Deploy (MVP!)
3. Add User Story 2 → Chat messaging → Test → Deploy
4. Add User Story 3 → View & filter → Test → Deploy
5. Add User Stories 4, 5, 6 → Status management, attachments, close/reopen → Test → Deploy
6. Add Polish → Audit logging, sidebar, delete → Final deploy

### Parallel Team Strategy

With multiple developers after Foundational phase:
- Developer A: US1 (Create Ticket) → then US3 (View & Filter)
- Developer B: US2 (Chat Messaging) → then US4 (Status Update)
- Developer C: US5 (Attachments) → then US6 (Close & Reopen)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Manual testing per project convention (no automated test framework)
- All views must be Arabic RTL with Dayem brand colors (#F4C400/#111111)
- File validation must be both client-side (JS) and server-side (PHP)