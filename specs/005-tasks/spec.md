# Feature Specification: Tasks Management

**Feature Branch**: `005-tasks`
**Created**: 2026-04-25
**Status**: Draft
**Input**: User description: "PHASE 5 — Tasks Management module for Dayem ERP"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Create and Assign Tasks (Priority: P1)

As an admin, I want to create tasks and assign them to employees so that work is organized and tracked across the company. Each task should have a title, description, priority, due date, and an assigned employee. The admin can see all tasks across all employees, while an employee only sees tasks assigned to them.

**Why this priority**: Task creation and assignment is the core value — without it, nothing else in the module functions. This is the MVP.

**Independent Test**: Admin creates a task, assigns it to an employee, and the task appears in that employee's task list. Employee can view only their assigned tasks.

**Acceptance Scenarios**:

1. **Given** an admin is on the tasks page, **When** they fill in task details (title, description, priority, due date, assignee) and submit, **Then** the task is created and appears in the task list with status "جديد" (New).
2. **Given** an admin creates a task assigned to employee Ahmed, **When** Ahmed logs in, **Then** he sees the task in his task list.
3. **Given** an employee is logged in, **When** they access the tasks page, **Then** they only see tasks assigned to them — not all company tasks.
4. **Given** an admin tries to create a task without a title or assignee, **When** they submit the form, **Then** validation errors are displayed in Arabic indicating required fields.

---

### User Story 2 - Update Task Status (Priority: P2)

As an employee, I want to update the status of my assigned tasks (New → In Progress → Completed) so that my manager can track progress. As an admin, I want to be able to change any task's status.

**Why this priority**: Status tracking is essential for task management but depends on task creation existing first.

**Independent Test**: Employee marks a task as "قيد التنفيذ" (In Progress) and the status updates. Admin changes a task status to "مكتمل" (Completed).

**Acceptance Scenarios**:

1. **Given** an employee has an assigned task with status "جديد", **When** they change the status to "قيد التنفيذ", **Then** the task status is updated and visible to the admin.
2. **Given** an admin views any task, **When** they change the status to "مكتمل", **Then** the task is marked as completed with the completion date recorded.
3. **Given** an employee tries to change a task not assigned to them, **Then** the system denies the action with an appropriate error.
4. **Given** a task is marked "مكتمل", **When** an admin changes it back to "قيد التنفيذ", **Then** the status updates and the completion date is cleared.

---

### User Story 3 - View and Filter Tasks (Priority: P2)

As an admin, I want to view all tasks with filtering by status, priority, department, and assignee so I can quickly find what I need. As an employee, I want to see my tasks with the same filtering.

**Why this priority**: Navigation and filtering is critical for usability once tasks exist, but creation must come first.

**Independent Test**: Admin filters tasks by status "مكتمل" and only completed tasks appear. Employee filters by priority "عاجل" (Urgent) and sees only their urgent tasks.

**Acceptance Scenarios**:

1. **Given** tasks exist across multiple statuses, **When** an admin filters by status "جديد", **Then** only tasks with status "جديد" are displayed.
2. **Given** tasks exist across departments, **When** an admin filters by department, **Then** only tasks in that department appear.
3. **Given** an employee views their tasks, **When** they filter by priority, **Then** only their tasks matching that priority are shown.
4. **Given** a large number of tasks exist, **When** the user views the task list, **Then** the results are paginated (15 per page).

---

### User Story 4 - Edit and Delete Tasks (Priority: P3)

As an admin, I want to edit task details (title, description, priority, due date, assignee) and delete tasks. When a task is deleted, it should be soft-deleted (recoverable in the future).

**Why this priority**: Editing and deleting are important but less critical than creation and status tracking.

**Independent Test**: Admin edits a task's priority from "متوسط" to "عاجل" and the change persists. Admin soft-deletes a task, and it no longer appears in the active list.

**Acceptance Scenarios**:

1. **Given** an admin is viewing a task, **When** they edit the title and save, **Then** the updated title appears in the task list.
2. **Given** an admin reassigns a task from employee A to employee B, **When** employee B logs in, **Then** they see the task; employee A no longer sees it.
3. **Given** an admin deletes a task, **When** they confirm deletion, **Then** the task is soft-deleted and no longer appears in the active task list.
4. **Given** an employee tries to delete a task, **Then** the system denies the action (only admins can delete).

---

### Edge Cases

- What happens when an assigned employee is suspended or terminated? → Their tasks remain but are reassignable by admin.
- What happens when a task's due date is in the past? → Displayed with a "متأخر" (Overdue) visual indicator.
- What happens when a department is deleted? → Tasks referencing that department remain with the last known department name.
- What happens when an admin assigns a task to an employee in a different department? → Allowed; tasks are not restricted by department.
- What happens when pagination reaches beyond available pages? → Redirect to last valid page.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin MUST be able to create tasks with: title (required, max 200 chars), description (optional, max 2000 chars), priority (required: عاجل/متوسط/منخفض), status (default: جديد), due date (optional), assignee (required, active employee), and department (optional).
- **FR-002**: Employee MUST only see tasks assigned to them; Admin MUST see all tasks across all employees.
- **FR-003**: Task status MUST follow the progression: جديد (New) → قيد التنفيذ (In Progress) → مكتمل (Completed), with admin able to set any status.
- **FR-004**: Employee MUST be able to update status on their own tasks; only admin can change assignee, priority, or delete tasks.
- **FR-005**: Task list MUST support filtering by: status, priority, department, and assignee (admin only). All filters MUST support pagination (15 items per page).
- **FR-006**: Task list MUST support searching by task title or employee code.
- **FR-007**: All task mutations (create, update status, edit, delete) MUST be logged in the audit log.
- **FR-008**: Tasks MUST use soft delete (deleted_at column). Hard delete is forbidden.
- **FR-009**: Task due dates in the past MUST display a visual "متأخر" (Overdue) indicator.
- **FR-010**: Admin MUST be able to reassign a task from one employee to another at any time.
- **FR-011**: When the assigned employee's status changes to suspended/terminated, the task MUST remain assigned but the admin MUST be alerted on the task list.
- **FR-012**: CSRF protection MUST be enforced on all task forms and AJAX requests.
- **FR-013**: All task-related pages and messages MUST be in Arabic (RTL layout, Egyptian colloquial tone).

### Key Entities

- **Task**: Represents a unit of work. Key attributes: title, description, priority (عاجل/متوسط/منخفض), status (جديد/قيد التنفيذ/مكتمل), due date, completion date, created at, updated at, deleted at. Relationships: belongs to one Employee (assignee), optionally belongs to one Department.
- **Employee (User)**: Existing user with role='employee'. Receives assigned tasks. Already exists in the system.
- **Department**: Existing department entity. Tasks can optionally be categorized by department. Already exists in the system.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin can create a new task and assign it to an employee in under 1 minute.
- **SC-002**: Employee can view their assigned tasks and update status within 2 clicks.
- **SC-003**: Task list with 500+ records loads within 2 seconds with pagination.
- **SC-004**: 100% of task mutations (create, edit, status change, delete) are logged in the audit trail.
- **SC-005**: Employees can NEVER see or modify tasks assigned to other employees — verified by both UI restrictions and backend enforcement.
- **SC-006**: Overdue tasks are visually distinguishable from on-track tasks within the task list view.

## Assumptions

- The existing auth system (Phase 2) and employee module (Phase 4) are already complete and functional.
- Task assignment is to individual employees, not teams or groups.
- Tasks are primarily managed by admins; employees have a read/update-status role for their own tasks.
- Arabic language throughout, using Egyptian colloquial for labels and messages.
- Pagination follows the existing 15-items-per-page convention established in employees/departments modules.
- Audit logging uses the same generic `logs` table already in place.
- The task module reuses the existing sidebar, header, and layout components.
- Task priority values: عاجل (Urgent/High), متوسط (Medium), منخفض (Low).
- Task status flow: جديد → قيد التنفيذ → مكتمل (with admin able to set any status directly).