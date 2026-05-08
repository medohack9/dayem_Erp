# Research: Tasks Management

**Feature**: 005-tasks
**Date**: 2026-04-25

## R-001: Task Status State Machine

**Decision**: Linear progression with admin override — جديد → قيد التنفيذ → مكتمل, admin can set any status directly, employees can only advance forward.

**Rationale**: Matches the spec requirement (FR-003) and is simple to implement. Admin override handles re-opening tasks (مكتمل → قيد التنفيذ) which is an explicit acceptance scenario. Employees advancing forward prevents accidental regression.

**Alternatives considered**:
- Free-form status (any to any): Too loose, no process enforcement
- Strict linear only (no backwards): Admin couldn't re-open completed tasks, violating US2 scenario 4
- Multiple custom statuses: Over-engineering for v1, can be added later

## R-002: Task-Department Relationship

**Decision**: Optional foreign key to departments (nullable `department_id`). Tasks can exist without a department. Department soft-delete clears `department_id` to NULL.

**Rationale**: The spec says department is optional (FR-001), and tasks should survive department deletion (Edge Case: "Tasks referencing that department remain with the last known department name" — but since we use FK, setting to NULL is cleaner than storing stale names).

**Alternatives considered**:
- Required department: Violates FR-001 which says department is optional
- Store department name at creation time: Denormalization, can become stale

## R-003: Task Assignment — Single Employee

**Decision**: One task is assigned to exactly one employee (`assigned_to` column, NOT NULL, FK to users). No multi-assignment in v1.

**Rationale**: Spec states "Task assignment is to individual employees, not teams or groups" (Assumptions). Single assignee is simpler, matches the spec exactly.

**Alternatives considered**:
- Junction table for multi-assignee: Over-engineering for spec that explicitly says individual
- Assign to department instead: Spec says assign to employee, not department

## R-004: Overdue Task Detection

**Decision**: Compare `due_date` (when not NULL) to `CURDATE()` in the query layer. Add a virtual `is_overdue` flag computed in the model's `findAllActive()` method. Visual indicator applied in the view via CSS class.

**Rationale**: Simple, no extra columns needed. Computed at query time ensures accuracy even if dates change. Consistent with the display-only nature of "overdue" — it's not a stored status.

**Alternatives considered**:
- Stored `is_overdue` column with cron update: Over-engineered, can become stale
- View-only calculation in JS: Risk of timezone mismatch, better on server side

## R-005: Employee vs Admin Access in Task List

**Decision**: Single controller + view with conditional rendering. `TaskController::index()` checks `Auth::role()`: admin gets all tasks, employee gets filtered `WHERE assigned_to = Auth::id()`. View conditionally shows/hides admin elements (create button, assignee filter, edit/delete actions).

**Rationale**: Matches the pattern used in departments (admin-only) and extends it. One view file with `Auth::isAdmin()` checks is cleaner than maintaining two separate views. The model handles data separation at the query level.

**Alternatives considered**:
- Separate controllers (AdminTaskController, EmployeeTaskController): Code duplication
- Middleware-level filtering: Too early — employee should access the route, just see different data

## R-006: Task Priority and Status Enum Storage

**Decision**: VARCHAR(20) with CHECK constraints for priority ('عاجل', 'متوسط', 'منخفض') and status ('جديد', 'قيد التنفيذ', 'مكتمل'). Arabic values stored directly for display convenience, matching how status is stored in users table.

**Rationale**: MySQL supports CHECK constraints on VARCHAR for enum-like validation. Storing Arabic values directly avoids translation mapping in every query/view. Matches the existing pattern where `users.status` stores 'active', 'suspended', 'terminated' as strings.

**Alternatives considered**:
- MySQL ENUM type: ALTER TABLE requires full rebuild, inflexible for adding values
- English codes with PHP mapping: Extra translation layer needed everywhere
- Lookup table: Over-engineering for 3-4 fixed values

## R-007: Task Number / Code Generation

**Decision**: Auto-generated task code using format `TSK-NNN` (sequential, never reused after soft delete). Follows the same pattern as employee codes (`EMP-NNN`).

**Rationale**: Consistent with employee_code pattern already implemented. Gives each task a human-readable reference number.

**Alternatives considered**:
- UUID: Not human-friendly for reference in conversations
- No code at all: Hard to reference tasks verbally or in other modules
- Department-prefixed codes: Unnecessary complexity, department is optional

## R-008: Completion Date Tracking

**Decision**: Add `completed_at` TIMESTAMP NULL column. Set to current timestamp when status changes to 'مكتمل', clear to NULL when status changes away from 'مكتمل'. Controller handles this logic.

**Rationale**: Clean, explicit tracking. Spec requirement (US2 scenario 2) says "completion date recorded". Clearing on status change back satisfies US2 scenario 4.

**Alternatives considered**:
- Derived from audit log: Requires complex log queries, slow
- Always populated (default NULL, no clearing): Doesn't satisfy "clear on reopen" scenario

## R-009: Alert for Suspended/Terminated Employee Tasks

**Decision**: In the task list query, LEFT JOIN users and include `u.status as employee_status`. In the view, when `employee_status` is 'suspended' or 'terminated', show a yellow warning badge "موظف موقوف" or "موظف منتهي الخدمة" next to the assignee name. No separate notification system in v1.

**Rationale**: Simple, visual alert that meets FR-011 without building a notification infrastructure. Can be enhanced later with a notifications module.

**Alternatives considered**:
- Email/push notifications: No notification infrastructure exists yet, out of scope for v1
- Blocking task creation for inactive employees: Spec says tasks "remain assigned" but admin should be "alerted" — not blocked