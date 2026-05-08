# Feature Specification: Ticket System

**Feature Branch**: `006-ticket-system`
**Created**: 2026-04-29
**Status**: Draft
**Input**: Phase 6 of the Dayem ERP implementation plan — internal support ticket system with chat-style messaging, status workflow (Open → In Review → Replied → Closed), admin replies, and file attachments.

## Clarifications

### Session 2026-04-29

- Q: FR-010 mentions filtering by "assignee" but assumptions say no admin assignment — how should tickets be filtered? → A: Filter by ticket creator (employee) only — no admin assignment, any admin can respond. The "assignee" filter in admin view is actually a filter by ticket creator (employee name).
- Q: Can employees send messages on their own "Replied" tickets or only "Open"? → A: Employees can message on Open and Replied tickets — they can always add follow-up messages unless the ticket is Closed.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Create a Support Ticket (Priority: P1)

As an employee, I want to create a support ticket with a title, description, category, and optional file attachments so that I can request help or report an issue to the admin team. The ticket is created with status "مفتوح" (Open) and I can immediately see it in my ticket list.

**Why this priority**: Ticket creation is the core entry point for the module. Without it, no other feature (messaging, status changes, admin replies) can function.

**Independent Test**: Can be fully tested by logging in as an employee, navigating to the tickets page, creating a ticket with all fields, and verifying it appears in the ticket list. Delivers immediate value by enabling employees to submit requests.

**Acceptance Scenarios**:

1. **Given** an employee is logged in, **When** they navigate to the tickets page and fill in the title, description, and category, then submit, **Then** a new ticket is created with status "مفتوح" (Open) and appears in their ticket list.
2. **Given** an employee is creating a ticket, **When** they attach one or more files (up to 10, each up to 5MB), **Then** the files are uploaded and linked to the ticket.
3. **Given** an employee is creating a ticket, **When** they submit without a title, **Then** a validation error is shown in Arabic indicating the title is required.
4. **Given** an employee is creating a ticket, **When** they attach a file exceeding 5MB or an unsupported file type, **Then** a validation error is shown indicating the file constraint.
5. **Given** an admin is logged in, **When** they view the tickets page, **Then** they see all tickets from all employees, not just their own.

---

### User Story 2 - Chat-Style Messaging on a Ticket (Priority: P1)

As a ticket creator (employee) or admin, I want to send and receive messages within a ticket in a chat-style interface so that we can discuss the issue in context. Each message shows the sender name, timestamp, and content. Messages appear in chronological order with the newest at the bottom.

**Why this priority**: Messaging is the core communication mechanism — it is the primary way tickets are resolved and provides the most value to users.

**Independent Test**: Can be tested by creating a ticket, then having the employee post a message, then an admin replying, and verifying both messages appear in the conversation thread.

**Acceptance Scenarios**:

1. **Given** a ticket exists, **When** the ticket creator types a message and submits, **Then** the message appears in the ticket conversation with the sender's name and timestamp.
2. **Given** a ticket has messages, **When** an admin opens the ticket, **Then** they see all messages in chronological order in a chat-style layout.
3. **Given** an admin replies to a ticket, **When** the reply is submitted, **Then** the message appears in the conversation and the ticket status changes to "تم الرد" (Replied).
4. **Given** a ticket is closed, **When** a user tries to send a message, **Then** the message input is disabled and a notice indicates the ticket is closed.
5. **Given** a ticket has status "تم الرد" (Replied), **When** the ticket creator (employee) sends a follow-up message, **Then** the message is added to the conversation and the ticket remains in "تم الرد" status.

---

### User Story 3 - View and Filter Tickets (Priority: P2)

As an admin, I want to view all tickets across all employees with filtering by status, category, and employee name (creator) so I can quickly find and manage tickets. As an employee, I want to see only my own tickets with the same filtering options.

**Why this priority**: Navigation and filtering is critical for usability once tickets exist, but creation and messaging must come first.

**Independent Test**: Admin filters tickets by status "مفتوح" and only open tickets appear. Employee views their own tickets filtered by category.

**Acceptance Scenarios**:

1. **Given** multiple tickets exist across statuses, **When** an admin filters by status "مفتوح", **Then** only open tickets are displayed.
2. **Given** multiple tickets exist across categories, **When** an admin or employee filters by category, **Then** only tickets in that category are shown.
3. **Given** an employee is logged in, **When** they view the ticket list, **Then** they only see tickets they created — not tickets from other employees.
4. **Given** a large number of tickets exist, **When** the user views the ticket list, **Then** results are paginated (15 per page).
5. **Given** an admin is on the ticket list, **When** they search by ticket title or employee name, **Then** matching tickets are shown.

---

### User Story 4 - Update Ticket Status (Priority: P2)

As an admin, I want to change a ticket's status through its workflow: Open → In Review → Replied → Closed. As an employee, I want to see status changes on my tickets and be able to send follow-up messages on my open or replied tickets. Admins can move tickets to any status; employees can only add messages to their own non-closed tickets.

**Why this priority**: Status management is essential for tracking ticket progress but depends on tickets existing first.

**Independent Test**: Admin changes a ticket from "مفتوح" to "قيد المراجعة" (In Review) and the status badge updates. Admin then replies and status changes to "تم الرد" (Replied). Admin closes the ticket and it shows "مغلق" (Closed).

**Acceptance Scenarios**:

1. **Given** an admin views a ticket with status "مفتوح", **When** they change the status to "قيد المراجعة", **Then** the ticket status is updated and visible to the ticket creator.
2. **Given** an admin replies to an open ticket, **When** the reply is submitted, **Then** the ticket status automatically changes to "تم الرد".
3. **Given** an admin changes a ticket status to "مغلق", **When** the change is saved, **Then** the ticket is marked as closed, no further messages can be sent, and the ticket creator is informed.
4. **Given** an employee views their own ticket, **When** they attempt to change the status directly, **Then** the system denies the action — only admins can change ticket status.
5. **Given** an admin reopens a closed ticket, **When** they change the status back to "مفتوح", **Then** the ticket becomes active again and messaging is enabled.

---

### User Story 5 - Admin Reply with File Attachments (Priority: P2)

As an admin, I want to reply to a ticket with text and optional file attachments so I can provide detailed responses with supporting documents. The reply updates the ticket status to "تم الرد" (Replied) and the employee is notified in the ticket view.

**Why this priority**: Admin replies are the primary resolution mechanism and make the ticket system functional. Without replies, tickets are just submissions.

**Independent Test**: Can be tested by an admin opening a ticket, typing a reply, attaching a file, and verifying the reply appears with the attachment in the conversation.

**Acceptance Scenarios**:

1. **Given** an admin views a ticket, **When** they type a reply and submit, **Then** the reply appears in the conversation and the ticket status changes to "تم الرد".
2. **Given** an admin is replying to a ticket, **When** they attach a file (up to 10 files, each up to 5MB), **Then** the file is uploaded and linked to the reply message.
3. **Given** an admin sends a reply with an attachment, **When** the employee views the ticket, **Then** they can see the reply message and download the attached file.

---

### User Story 6 - Close and Reopen Tickets (Priority: P3)

As an admin, I want to close a ticket when the issue is resolved and optionally reopen it if the problem persists. Closed tickets are visible in the ticket list when filtering by status but cannot receive new messages unless reopened.

**Why this priority**: Closing and reopening tickets is important for lifecycle management but depends on the core workflow being established first.

**Independent Test**: Admin closes a resolved ticket and verifies no new messages can be sent. Admin then reopens it and verifies messaging is re-enabled.

**Acceptance Scenarios**:

1. **Given** an admin views a ticket in "تم الرد" status, **When** they close it, **Then** the ticket status changes to "مغلق" and no further messages can be sent.
2. **Given** a closed ticket, **When** an admin reopens it by changing status to "مفتوح", **Then** the ticket becomes active again and messages can be sent.
3. **Given** an employee views their closed ticket, **When** they try to send a message, **Then** they see a notice indicating the ticket is closed and cannot receive new messages.

---

### Edge Cases

- What happens when an employee tries to access another employee's ticket directly via URL? → The system denies access with a 403 response.
- What happens when a file attachment fails to upload (server error, file too large)? → The message is not sent and an Arabic error is shown indicating the file issue.
- What happens when a ticket has no messages besides the initial description? → The conversation view shows only the original ticket content as the first "message".
- What happens when an admin deletes a ticket? → The ticket is soft-deleted (hidden from active lists but preserved in the database).
- What happens when the attached file type is not in the allowed list? → A validation error is shown indicating the supported file types.
- What happens when a ticket creator (employee) is terminated or suspended? → Their tickets remain accessible to admins; no new tickets can be created by the user.
- What happens when a message exceeds the maximum length? → A validation error indicates the maximum allowed length.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Employees MUST be able to create tickets with: title (required, max 200 chars), description (required, max 5000 chars), category (required: تقرير مشكلة / طلب صيانة / طلب معلومات / أخرى), and optional file attachments (up to 10 files, max 5MB each).
- **FR-002**: Tickets MUST be created with status "مفتوح" (Open) by default.
- **FR-003**: Ticket status MUST follow the workflow: مفتوح (Open) → قيد المراجعة (In Review) → تم الرد (Replied) → مغلق (Closed). Only admins can change status directly.
- **FR-004**: When an admin replies to a ticket, the status MUST automatically change to "تم الرد" (Replied).
- **FR-005**: System MUST support chat-style messaging within each ticket, showing sender name, timestamp, and message content in chronological order (newest at bottom).
- **FR-006**: File attachments MUST be supported on ticket creation and admin replies, with a maximum of 10 files per action and 5MB per file. Allowed types: images (jpg, png, gif), documents (pdf, doc, docx, xls, xlsx), and archives (zip).
- **FR-007**: Employees MUST only see and interact with tickets they created; admins MUST see all tickets across all employees.
- **FR-008**: Admin MUST be able to change any ticket's status to any valid status value (including reopening closed tickets).
- **FR-009**: Only Closed tickets MUST prevent new messages from being sent; employees can send follow-up messages on Open and Replied tickets. The input area on Closed tickets MUST be disabled with a notice in Arabic.
- **FR-010**: Ticket list MUST support filtering by: status, category, and ticket creator/employee name (admin view). Employee view filters by status and category only. Both views MUST support pagination (15 items per page).
- **FR-011**: Ticket list MUST support searching by ticket title or employee name (admin) / title only (employee).
- **FR-012**: System MUST soft-delete tickets (deleted_at column). Hard delete is forbidden.
- **FR-013**: All ticket and message actions (create, status change, reply, close, reopen, delete) MUST be logged in the audit log.
- **FR-014**: CSRF protection MUST be enforced on all ticket forms and AJAX requests.
- **FR-015**: All ticket-related pages and messages MUST be in Arabic (RTL layout, Egyptian colloquial tone).
- **FR-016**: Admins MUST be able to delete (soft-delete) any ticket; employees MUST NOT have delete permission.
- **FR-017**: System MUST prevent employees from accessing tickets created by other employees via direct URL (403 forbidden response).
- **FR-018**: File attachments MUST be stored in a secure directory outside the web root and served via a controlled download endpoint that checks permissions.
- **FR-019**: When an employee's status changes to suspended or terminated, their existing tickets MUST remain accessible to admins but the employee MUST NOT be able to create new tickets.
- **FR-020**: System MUST display a count of unread messages or status changes for each ticket in the ticket list (visual indicator for updated tickets).

### Key Entities

- **Ticket**: Represents a support request. Key attributes: title, description, category (تقرير مشكلة / طلب صيانة / طلب معلومات / أخرى), status (مفتوح / قيد المراجعة / تم الرد / مغلق), created_at, updated_at, deleted_at. Relationships: belongs to one Employee (creator), has many Messages, has many Attachments.
- **Message**: Represents a single message within a ticket conversation. Key attributes: content (max 5000 chars), sender_type (employee/admin), sender_id, created_at. Relationship: belongs to one Ticket.
- **Attachment**: Represents a file attached to a ticket or message. Key attributes: file_name, file_path, file_size, file_type, created_at. Relationship: belongs to one Ticket (optional) or one Message (optional).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An employee can create a ticket with description and attachments in under 2 minutes.
- **SC-002**: An admin can reply to a ticket and the employee sees the reply within 1 page refresh.
- **SC-003**: Ticket list with 500+ records loads within 2 seconds with pagination.
- **SC-004**: 100% of ticket actions (create, status change, reply, close, reopen, delete) are logged in the audit trail.
- **SC-005**: Employees can NEVER see or access tickets created by other employees — verified by both UI restrictions and backend enforcement.
- **SC-006**: File attachments upload and download within 5 seconds for files up to 5MB.
- **SC-007**: The chat-style conversation view displays all messages in correct chronological order with no missing or misordered messages.

## Assumptions

- The existing auth system (Phase 2) and employee module (Phase 4) are already complete and functional.
- Tickets are created by employees and managed by admins; there is no concept of "assigning" a ticket to a specific admin — any admin can respond.
- The ticket module reuses the existing sidebar, header, layout, and RBAC system.
- File storage uses the server filesystem with a secure directory structure; cloud storage is out of scope for MVP.
- Arabic language throughout, using Egyptian colloquial for labels and messages.
- Pagination follows the existing 15-items-per-page convention established in previous modules.
- Audit logging uses the same generic `logs` table already in place.
- Ticket categories are fixed (تقرير مشكلة, طلب صيانة, طلب معلومات, أخرى) and not user-configurable.
- Soft deletion is used for tickets — records are marked as deleted but preserved in the database.
- Only image, document, and archive file types are allowed for attachments ( jpg, png, gif, pdf, doc, docx, xls, xlsx, zip).
- The existing CSRF protection infrastructure from Phase 1 will be used for all ticket forms.
- Employee message length is limited to 5000 characters — no rich text or Markdown support in MVP.
- When an employee is suspended or terminated, they can still view their existing tickets but cannot create new ones or send messages in open tickets.
- Employees can send messages on their own tickets when the status is Open or Replied; only Closed tickets block messaging.