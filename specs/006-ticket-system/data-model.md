# Data Model: Ticket System

**Feature**: 006-ticket-system
**Date**: 2026-04-29

## Entity: Ticket

New table `tickets` to be created in migration `008_tickets.sql`.

### Fields

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT AUTO_INCREMENT | PRIMARY KEY | Ticket ID |
| `ticket_code` | VARCHAR(20) | NOT NULL, UNIQUE | Human-readable code (e.g., TKT-001) |
| `title` | VARCHAR(200) | NOT NULL | Ticket title |
| `description` | TEXT | NOT NULL | Ticket description (max 5000 chars enforced in app) |
| `category` | VARCHAR(30) | NOT NULL | تقرير مشكلة / طلب صيانة / طلب معلومات / أخرى |
| `status` | VARCHAR(20) | NOT NULL, DEFAULT 'مفتوح' | مفتوح / قيد المراجعة / تم الرد / مغلق |
| `created_by` | INT | NOT NULL | FK → users.id (employee who created) |
| `assigned_employee` | INT | NULL | FK → users.id (optional, for future use; NULL for MVP) |
| `deleted_at` | TIMESTAMP | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Creation time |
| `updated_at` | TIMESTAMP | NULL ON UPDATE | Last update time |

### Indexes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `uk_tickets_ticket_code` | ticket_code | UNIQUE | Ensure unique ticket codes |
| `idx_tickets_created_by` | created_by | INDEX | Filter tickets by employee |
| `idx_tickets_status` | status | INDEX | Filter by status |
| `idx_tickets_category` | category | INDEX | Filter by category |
| `idx_tickets_deleted_at` | deleted_at | INDEX | Soft delete filter |

### Foreign Keys

| Constraint | Columns | References | On Delete |
|-------------|---------|-----------|----------|
| `fk_tickets_created_by` | created_by | users(id) | CASCADE |
| `fk_tickets_assigned_employee` | assigned_employee | users(id) | SET NULL |

### Validation Rules

| Field | Rule |
|-------|------|
| title | Required, max 200 chars |
| description | Required, max 5000 chars |
| category | Required, one of: تقرير مشكلة, طلب صيانة, طلب معلومات, أخرى |
| status | Default مفتوح on create; transitions per workflow rules |
| created_by | Required, must reference active employee |

### State Transitions

```
مفتوح (Open)
  ↓ admin sets to قيد المراجعة
قيد المراجعة (In Review)
  ↓ admin replies → تم الرد (auto)
تم الرد (Replied)
  ↓ admin closes → مغلق
مغلق (Closed)
  ↓ admin reopens → مفتوح

Rules:
- Only admin can change status directly (FR-003)
- Admin reply auto-sets status to تم الرد (FR-004)
- Employee can message on Open and Replied tickets (not Closed)
- Admin can set any status at any time (including reopen Closed)
```

---

## Entity: TicketMessage

New table `ticket_messages` to be created in migration `008_tickets.sql`.

### Fields

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT AUTO_INCREMENT | PRIMARY KEY | Message ID |
| `ticket_id` | INT | NOT NULL | FK → tickets.id |
| `sender_id` | INT | NOT NULL | FK → users.id (admin or employee) |
| `sender_type` | VARCHAR(10) | NOT NULL | 'admin' or 'employee' — denormalized for quick filtering |
| `content` | TEXT | NOT NULL | Message content (max 5000 chars enforced in app) |
| `created_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Message timestamp |

### Indexes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `idx_ticket_messages_ticket_id` | ticket_id | INDEX | Get messages for a ticket |
| `idx_ticket_messages_sender_id` | sender_id | INDEX | Get messages by sender |
| `idx_ticket_messages_created_at` | created_at | INDEX | Chronological ordering |

### Foreign Keys

| Constraint | Columns | References | On Delete |
|-------------|---------|-----------|----------|
| `fk_ticket_messages_ticket` | ticket_id | tickets(id) | CASCADE |
| `fk_ticket_messages_sender` | sender_id | users(id) | CASCADE |

### Validation Rules

| Field | Rule |
|-------|------|
| content | Required, max 5000 chars |
| ticket_id | Must reference existing non-deleted ticket |
| sender_id | Must reference active user |

---

## Entity: TicketAttachment

New table `ticket_attachments` to be created in migration `008_tickets.sql`.

### Fields

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT AUTO_INCREMENT | PRIMARY KEY | Attachment ID |
| `ticket_id` | INT | NOT NULL | FK → tickets.id |
| `message_id` | INT | NULL | FK → ticket_messages.id (NULL if attached during creation) |
| `original_name` | VARCHAR(255) | NOT NULL | Original filename |
| `stored_name` | VARCHAR(255) | NOT NULL | Unique stored filename |
| `file_path` | VARCHAR(500) | NOT NULL | Relative path from storage root |
| `file_size` | INT | NOT NULL | File size in bytes |
| `mime_type` | VARCHAR(100) | NOT NULL | MIME type |
| `created_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Upload timestamp |

### Indexes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `idx_ticket_attachments_ticket_id` | ticket_id | INDEX | Get attachments for a ticket |
| `idx_ticket_attachments_message_id` | message_id | INDEX | Get attachments for a message |

### Foreign Keys

| Constraint | Columns | References | On Delete |
|-------------|---------|-----------|----------|
| `fk_ticket_attachments_ticket` | ticket_id | tickets(id) | CASCADE |
| `fk_ticket_attachments_message` | message_id | ticket_messages(id) | SET NULL |

### Validation Rules

| Field | Rule |
|-------|------|
| original_name | Required |
| file_size | Max 5MB (5,242,880 bytes) per file |
| mime_type | Must be one of: image/jpeg, image/png, image/gif, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/zip |
| max files | Max 10 files per submission |

---

## Entity: TicketRead (read tracking)

New table `ticket_reads` to be created in migration `008_tickets.sql`.

### Fields

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT AUTO_INCREMENT | PRIMARY KEY | Read record ID |
| `ticket_id` | INT | NOT NULL | FK → tickets.id |
| `user_id` | INT | NOT NULL | FK → users.id |
| `last_read_at` | TIMESTAMP | NOT NULL | Last time user read this ticket |

### Indexes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `uk_ticket_reads_ticket_user` | ticket_id, user_id | UNIQUE | One read record per user per ticket |
| `idx_ticket_reads_user_id` | user_id | INDEX | Find unread tickets for a user |

### Foreign Keys

| Constraint | Columns | References | On Delete |
|-------------|---------|-----------|----------|
| `fk_ticket_reads_ticket` | ticket_id | tickets(id) | CASCADE |
| `fk_ticket_reads_user` | user_id | users(id) | CASCADE |

---

## Entity Relationships

```
Ticket 1:N TicketMessage      — a ticket has many messages
Ticket 1:N TicketAttachment    — a ticket has many attachments (creation-level)
TicketMessage 1:N TicketAttachment — a message has many attachments (reply-level)
Ticket 1:N TicketRead         — a ticket has many read records
User (Employee) 1:N Ticket    — an employee creates many tickets (created_by)
Ticket N:1 User (created_by)  — each ticket belongs to one employee
```

### Relationship to Existing Entities

```
Ticket N:1 User (created_by)     — each ticket created by one employee
TicketMessage N:1 User (sender)   — each message sent by one user (admin or employee)
```

### Computed Fields (not stored)

| Field | Logic |
|-------|-------|
| `has_unread` | `last_read_at < (SELECT MAX(created_at) FROM ticket_messages WHERE ticket_id = ?)` OR ticket has no read record for the user |
| `is_closed` | `status = 'مغلق'` |
| `can_message` | `status != 'مغلق'` (employees can message on Open and Replied; Closed blocks messaging) |