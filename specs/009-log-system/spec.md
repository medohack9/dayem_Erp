# Feature Specification: Log System

**Feature Branch**: `009-log-system`
**Created**: 2026-05-01
**Status**: Draft
**Input**: Phase 9 of the Dayem ERP implementation plan — track all system activity with log viewing interface and CSV export capability.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View System Logs (Priority: P1) 🎯 MVP

As an admin, I want to view all system activity logs in a paginated list so I can monitor user actions and system events. I can see who performed each action, what entity was affected, when it happened, and from which IP address.

**Why this priority**: Log viewing is the core functionality — without it, the audit capability doesn't exist. This enables admins to monitor and investigate system activity.

**Independent Test**: Login as admin → navigate to logs page → see list of recent actions with user names, action types, timestamps, and IP addresses.

**Acceptance Scenarios**:

1. **Given** an admin is logged in, **When** they navigate to the logs page, **Then** they see a paginated list of all system logs ordered by most recent first.
2. **Given** an admin views the log list, **When** they look at each log entry, **Then** they see: user name, action type, entity type, entity ID, timestamp, and IP address.
3. **Given** there are more than 50 log entries, **When** the admin views the list, **Then** results are paginated (50 per page).
4. **Given** an employee tries to access the logs page, **When** they navigate to the URL, **Then** access is denied (403 — admin only).

---

### User Story 2 - Filter and Search Logs (Priority: P1)

As an admin, I want to filter logs by user, action type, entity type, and date range so I can quickly find specific activities. I can also search by entity ID to trace all actions on a specific record.

**Why this priority**: Without filtering, finding relevant logs in thousands of entries would be impractical. Essential for audit investigations.

**Independent Test**: Admin filters logs by user "Ahmed" and sees only Ahmed's actions. Admin filters by date range and sees only logs in that period.

**Acceptance Scenarios**:

1. **Given** an admin views the log list, **When** they select a user from the filter dropdown, **Then** only logs performed by that user are shown.
2. **Given** an admin views the log list, **When** they select an action type filter (create/update/delete/etc.), **Then** only logs with that action type are shown.
3. **Given** an admin views the log list, **When** they select an entity type filter (employee/department/task/ticket/etc.), **Then** only logs for that entity type are shown.
4. **Given** an admin views the log list, **When** they enter a date range (from/to), **Then** only logs within that period are shown.
5. **Given** an admin views the log list, **When** they enter an entity ID in the search field, **Then** all logs related to that specific record are shown.
6. **Given** an admin applies multiple filters, **When** they click "clear filters", **Then** all filters reset and the full list displays.

---

### User Story 3 - Export Logs to CSV (Priority: P2)

As an admin, I want to export filtered logs to a CSV file so I can share audit reports with stakeholders or archive them externally. The export respects the current filter settings.

**Why this priority**: Export capability completes the audit workflow but depends on log viewing and filtering being in place first.

**Independent Test**: Admin applies filters → clicks export → downloads CSV file with matching logs → opens in spreadsheet application.

**Acceptance Scenarios**:

1. **Given** an admin has applied filters to the log list, **When** they click the "Export CSV" button, **Then** a CSV file downloads containing all matching logs (not just current page).
2. **Given** an admin exports logs, **When** they open the CSV file, **Then** it contains columns: user name, action, entity type, entity ID, details, IP address, timestamp.
3. **Given** an admin exports logs with no filters applied, **When** the export completes, **Then** the CSV contains all logs up to a reasonable limit (e.g., 10,000 records).
4. **Given** an admin is on the logs page, **When** they click "Export Today's Logs", **Then** a CSV downloads with only today's logs.

---

### User Story 4 - View Log Details (Priority: P2)

As an admin, I want to click on a log entry to see full details including the JSON details field so I can understand exactly what changes were made (before/after values for updates, specific values for creates).

**Why this priority**: Details view provides deeper audit information but the list view is the primary tool.

**Independent Test**: Admin clicks on a log entry → sees a modal or detail view with expanded information including parsed JSON details.

**Acceptance Scenarios**:

1. **Given** an admin views the log list, **When** they click on a log entry, **Then** a modal opens showing full details.
2. **Given** a log entry has details (JSON), **When** the admin views the detail modal, **Then** the details are displayed in a readable format (key-value pairs).
3. **Given** a log entry has no details, **When** the admin views the detail modal, **Then** it shows "No additional details".

---

### Edge Cases

- What happens when there are no logs matching the filter? → Display message "لا توجد سجلات مطابقة" (No matching logs).
- What happens when export exceeds 10,000 records? → Show warning and export first 10,000 with note about limit.
- What happens when user in log was deleted? → Show "مستخدم محذوف" (Deleted user) with ID reference.
- What happens when entity in log was soft-deleted? → Show entity info with "محذوف" (Deleted) indicator.
- What happens if logs table is empty? → Show welcome message explaining logs will appear as system is used.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin MUST be able to view a paginated list of all system logs ordered by created_at DESC (50 per page).
- **FR-002**: Each log entry MUST display: user name (or "Deleted user" if user deleted), action, entity type, entity ID, timestamp (Arabic format), IP address.
- **FR-003**: Log viewing MUST be admin-only. Employees MUST NOT have access (403 forbidden).
- **FR-004**: System MUST support filtering by: user_id (dropdown), action (dropdown), entity_type (dropdown), date_from, date_to.
- **FR-005**: System MUST support searching by entity_id to find all actions on a specific record.
- **FR-006**: All filters MUST be combinable (AND logic). Clear filters button MUST reset all filters.
- **FR-007**: System MUST support CSV export of filtered results. CSV MUST include all matching records (not paginated) up to 10,000 limit.
- **FR-008**: CSV export MUST include columns: user_name, action, entity_type, entity_id, details, ip_address, created_at.
- **FR-009**: System MUST support quick export button for "Today's Logs" (preset filter + export).
- **FR-010**: System MUST display a detail view/modal when clicking a log entry, showing parsed JSON details if present.
- **FR-011**: All log-related pages and messages MUST be in Arabic (RTL layout, Egyptian colloquial tone).
- **FR-012**: Pagination MUST follow project convention (50 items per page for logs, higher density than other modules).
- **FR-013**: System MUST show total count of matching logs in the filter summary area.

### Key Entities

- **Log**: Already exists in database. Key attributes: id, user_id, action, entity_type, entity_id, details (JSON), ip_address, created_at. No modifications needed.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin can view and navigate 10,000+ logs with pagination in under 3 seconds.
- **SC-002**: Filtered results load within 2 seconds.
- **SC-003**: CSV export of 1,000 records completes within 5 seconds.
- **SC-004**: 100% of log actions across all modules are visible in the log viewer.
- **SC-005**: Admin can trace any entity's history by searching its ID.
- **SC-006**: Employees can NEVER access the logs page — verified by both UI restrictions and backend enforcement.

## Assumptions

- The existing `logs` table and `LogModel` are already complete and functional (created in Phase 1).
- All existing modules (departments, employees, tasks, tickets, salaries, files) are already logging actions via LogModel::logAction().
- Arabic language throughout, using Egyptian colloquial for labels and messages.
- Pagination: 50 items per page (higher density for logs given their nature).
- CSV export limit: 10,000 records maximum per export to prevent memory issues.
- No log deletion functionality in MVP — logs are retained permanently for audit compliance.
- IP addresses are already being captured in existing log implementation.
- The sidebar already has a logs link (admin only) — created in Phase 1.
- Action types are not predefined enum — they are free-form strings set by each module (create, update, delete, upload, download, reply, status_change, etc.).
- Entity types follow module names: department, employee, task, ticket, salary, file, user, etc.