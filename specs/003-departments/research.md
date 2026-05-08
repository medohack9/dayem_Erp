# Research: Departments Module

**Feature**: 003-departments
**Date**: 2026-04-25

## R-001: Department Table Schema Design

**Decision**: Single `departments` table with a nullable `manager_id` foreign key to `users.id` and a `default_salary` decimal column.

**Rationale**: The spec requires a one-to-one relationship between managers and departments. A nullable FK on the departments side is the simplest approach — adding a `managed_department_id` to the users table would also work but couples the entities and makes the department CRUD flow less natural (you'd need to update the users table when changing a department's manager). The FK on departments keeps all department data in one place.

**Alternatives considered**:
- Junction table (`department_managers`): Over-engineered for a strict one-to-one relationship; adds unnecessary JOINs
- Column on users table (`managed_department_id`): Inverts the ownership; department becomes a property of a user rather than the other way around, making CRUD flows awkward

## R-002: Employee Count — Computed vs. Cached

**Decision**: Compute employee count dynamically via SQL `COUNT()` with a LEFT JOIN on each list query.

**Rationale**: The spec explicitly states "Employee count per department is computed dynamically (not cached)." With a max of ~50 departments and ~500 employees, a COUNT with GROUP BY is trivially fast (< 5ms). Caching introduces staleness and invalidation complexity that isn't justified at this scale.

**Alternatives considered**:
- Cached `employee_count` column on departments: Requires triggers or application-level invalidation on every employee create/update/delete; unnecessary complexity at MVP scale
- Materialized view: MySQL doesn't natively support these; over-engineered

## R-003: Unique Department Names — Case-Insensitive

**Decision**: Enforce uniqueness via a case-insensitive collation (`utf8mb4_unicode_ci`) which is already the default on the table, plus a `UNIQUE` index on `name`. Validate in application layer by checking `LOWER(name)` before insert/update.

**Rationale**: MySQL's `utf8mb4_unicode_ci` collation already treats `HR` and `hr` as equal for uniqueness constraints. The UNIQUE index provides database-level enforcement, and the application check gives a user-friendly Arabic error message before the DB constraint fires.

**Alternatives considered**:
- Store a `name_lower` column: Redundant with the collation behavior
- Application-only check: Race condition possible without DB enforcement

## R-004: Soft Delete with Reassignment Implementation

**Decision**: Set `deleted_at = NOW()` on the department row and update all associated employees' `department_id` to the target reassignment department before soft-deleting. Prevent deleting the last active department with a COUNT query.

**Rationale**: Consistent with the constitution's soft-delete requirement. The reassignment is atomic: update employees' department_id first, then soft-delete the department. If reassignment fails (e.g., target department doesn't exist), the transaction rolls back and the department remains active.

**Alternatives considered**:
- Hard delete: Violates constitution rule "deleted_at column is required"
- Separate archive table: Adds complexity; soft-delete in-place is simpler and supports audit needs

## R-005: Delete Confirmation UI — Modal vs. Separate Page

**Decision**: Use a modal dialog rendered as a PHP partial, shown/hidden via vanilla JS. The modal contains a dropdown of active departments (excluding the one being deleted). On confirm, an AJAX POST sends the target department_id.

**Rationale**: Consistent with the clarification session (modal dialog chosen). Keeps the user on the list page, avoids full-page navigation. The modal partial is reusable for future confirmation patterns.

**Alternatives considered**:
- Separate confirmation page: Requires full page reload, breaks AJAX-only constitution rule
- Browser confirm(): Not customizable for dropdown selection

## R-006: Manager Dropdown — Active Employees Only

**Decision**: Populate the manager dropdown from `users WHERE role IN ('admin','employee') AND status = 'active' AND deleted_at IS NULL`, excluding any employee already assigned as manager to another department (enforcing the one-to-one constraint).

**Rationale**: The strict one-to-one constraint means an employee managing Department A cannot also manage Department B. The query must filter out employees who already have a `managed_department_id` pointing to a different department.

**Alternatives considered**:
- Show all employees and validate on submit: Poor UX — user might select an already-assigned manager and only see an error after submission
- No constraint enforcement: Would violate the one-to-one rule from the spec clarification

## R-007: Pagination Implementation

**Decision**: Server-side pagination via `LIMIT/OFFSET` in SQL queries, 15 items per page. The list view fetches page 1 by default, and AJAX requests fetch subsequent pages. A pagination component renders page links.

**Rationale**: Constitution requires "Use pagination for large data tables." Server-side pagination keeps payloads small. 15 items is a reasonable default that fits the RTL Arabic layout without excessive scrolling.

**Alternatives considered**:
- Client-side pagination (load all, paginate in JS): Not viable at scale; violates performance constitution rules
- Infinite scroll: Harder to navigate to specific pages; pagination links are more accessible for an admin tool

## R-008: Audit Logging for Department Actions

**Decision**: Use the existing `auth_logs` pattern but store department action logs in a dedicated `logs` table (to be built in Phase 9). For now, create a simple `DepartmentLogModel` or extend `Model` to insert into a `logs` table with columns: `id`, `user_id`, `action`, `entity_type`, `entity_id`, `details` (JSON), `created_at`.

**Rationale**: The constitution requires "All system actions MUST be logged" and "Logs MUST be stored in database." While Phase 9 handles the full logging system, FR-013 requires department actions to be logged now. Creating the `logs` table early with a generic structure serves all future modules (employees, tasks, etc.) and avoids rework. The table structure follows the constitution's log format: `(user_name, action, department, timestamp)`.

**Alternatives considered**:
- Store in `auth_logs`: Auth logs are specifically for authentication events; mixing department actions would pollute the data
- Defer all logging to Phase 9: Violates FR-013; the spec explicitly requires logging now

## R-009: Search Implementation for Department List

**Decision**: Server-side LIKE search on `departments.name` with wildcard matching, executed via AJAX on each keystroke (debounced 300ms). Arabic text search works naturally with `utf8mb4_unicode_ci` collation.

**Rationale**: Simple LIKE is sufficient for ~50 departments. Arabic text matching works because the collation normalizes Unicode comparisons. Debouncing prevents excessive requests.

**Alternatives considered**:
- Full-text search: Over-engineered for 50 records
- Client-side filter (load all, filter in JS): Not scalable; violates constitution pagination rule