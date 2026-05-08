# Research: Ticket System

**Feature**: 006-ticket-system
**Date**: 2026-04-29

## Research Tasks

### 1. Chat-Style Messaging Architecture

**Decision**: Store messages in a separate `ticket_messages` table linked to `tickets.id` via foreign key.

**Rationale**: Mirrors the existing `task_notes` pattern already in the codebase (task_notes table with task_id FK). Keeps messages independent from the ticket record itself, enabling efficient pagination of conversation history and clean separation of concerns. The ticket's `description` field serves as the initial "message" and subsequent replies go in `ticket_messages`.

**Alternatives considered**:
- Storing messages as JSON in the ticket table: Rejected — makes querying, pagination, and audit logging difficult; violates constitution's data integrity rules.
- Separate chat microservice: Rejected — overkill for an internal ERP with <200 users; adds unnecessary complexity.

### 2. Ticket Status Workflow

**Decision**: Implement a linear status workflow: مفتوح (Open) → قيد المراجعة (In Review) → تم الرد (Replied) → مغلق (Closed). Admin can set any status directly. Employees can only send messages on Open and Replied tickets; Closed tickets block messaging entirely.

**Rationale**: Follows the spec clarifications. The "In Review" status lets admins signal they're looking at a ticket before replying. Admin replies automatically set status to "Replied" per FR-004. Employees can send follow-up messages on Replied tickets without needing the admin to reopen.

**Alternatives considered**:
- Free-form status (no workflow enforcement): Rejected — would create data inconsistency and make filtering unreliable.
- Employee can only message on Open: Rejected — clarified during spec review; employees need to respond on Replied tickets.

### 3. File Attachment Storage

**Decision**: Store attachments in `storage/uploads/tickets/` directory with subfolders per ticket ID. Serve files through a controlled download endpoint (TicketController::serveAttachment) that checks permissions before serving.

**Rationale**: Follows the constitution's file storage rules (organized by module) and mirrors the pattern established by `DocumentController::serve` in the employee module. The controlled endpoint prevents direct URL access and enforces RBAC. A `ticket_attachments` table stores metadata (original name, stored name, file size, mime type) with FK to the ticket or message.

**Alternatives considered**:
- Database BLOB storage: Rejected — performance concerns for files up to 5MB; constitution specifies file system storage.
- Cloud storage (S3): Rejected — out of scope for MVP; constitution specifies local filesystem.

### 4. RBAC Pattern for Ticket Access

**Decision**: Admin sees all tickets across all employees. Employee sees only their own tickets (created_by = Auth::id()). Backend enforcement via controller checks; employee attempting to access another employee's ticket gets 403.

**Rationale**: Mirrors the established pattern in TaskController where `Auth::isAdmin()` determines view scope and employee is restricted to `Auth::id()`. Consistent with constitution's RBAC enforcement at the backend level.

**Alternatives considered**:
- None — this is the established project pattern.

### 5. Attachment Upload Constraints

**Decision**: Allow up to 10 files per submission (create or reply), each max 5MB. Allowed MIME types: jpg, png, gif, pdf, doc, docx, xls, xlsx, zip. Validate both client-side and server-side.

**Rationale**: Spec specifies these constraints (FR-006). The 5MB limit per file is within the constitution's 20MB total limit. The allowed types cover common document and image formats needed for support tickets while excluding executables. Server-side validation prevents bypass of client-side checks.

**Alternatives considered**:
- Allowing all file types up to 20MB: Rejected — security risk with executable files; spec explicitly limits types.

### 6. Unread Message Indicators

**Decision**: Track unread status using a `last_read_at` timestamp on the ticket for each user role. When a ticket has messages newer than the user's `last_read_at`, display an unread indicator. Admin and employee each have their own read tracking via `ticket_reads` table (user_id, ticket_id, last_read_at).

**Rationale**: FR-020 requires unread indicators. A junction table is the simplest approach that supports per-user read status without adding columns to the tickets table for each role. Lightweight query: `WHERE last_read_at < (SELECT MAX(created_at) FROM ticket_messages WHERE ticket_id = ?)`.

**Alternatives considered**:
- Adding read counters to the tickets table: Rejected — doesn't scale for multi-user read tracking.
- Storing read status in session: Rejected — lost on logout; not persistent.

### 7. Message Input on Closed Tickets

**Decision**: Frontend disables the message input area and shows an Arabic notice when ticket status is مغلق (Closed). Backend also rejects message submissions on closed tickets with appropriate error response.

**Rationale**: Dual enforcement (frontend UX + backend validation) per spec FR-009. Prevents accidental submissions and ensures consistent behavior regardless of client.

**Alternatives considered**:
- Backend-only enforcement: Rejected — poor UX; users would type a message and then get an error.