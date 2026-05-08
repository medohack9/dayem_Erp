# Tasks: Tasks Management

**Input**: Design documents from `/specs/005-tasks/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Not explicitly requested — no test tasks included.

**Organization**: Tasks grouped by user story for independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Database migration and route configuration for tasks module

- [x] T001 Create tasks table migration in database/migrations/006_tasks.sql with all columns, indexes, foreign keys per data-model.md
- [x] T002 Add task routes to config/routes.php (8 routes: GET /tasks, GET /tasks/create, POST /tasks, GET /tasks/{id}/edit, PUT /tasks/{id}, PUT /tasks/{id}/status, DELETE /tasks/{id}, GET /api/tasks/active-employees)
- [x] T003 Create empty TaskController scaffold in app/controllers/TaskController.php with constructor injecting TaskModel, EmployeeModel, DepartmentModel, LogModel
- [x] T004 Create empty TaskModel scaffold in app/models/TaskModel.php extending App\Core\Model

**Checkpoint**: Migration can be executed, routes resolve to controller (will 500 until methods exist)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core model and controller logic that ALL user stories depend on

- [x] T005 Implement TaskModel::create with auto-generate task_code (TSK-NNN pattern) in app/models/TaskModel.php
- [x] T006 Implement TaskModel::findAllActive with search, filter (status, priority, department_id, assigned_to), pagination, and overdue computation in app/models/TaskModel.php
- [x] T007 Implement TaskModel::findById in app/models/TaskModel.php
- [x] T008 Implement TaskModel::updateTask and TaskModel::softDelete in app/models/TaskModel.php
- [x] T009 Execute migration 006_tasks.sql on the database and verify table creation

**Checkpoint**: TaskModel supports all CRUD operations. Migration verified. User story implementation can begin.

---

## Phase 3: User Story 1 - Create and Assign Tasks (Priority: P1) 🎯 MVP

**Goal**: Admin can create tasks with title, description, priority, status, due date, assigned employee, and optional department. Employee sees only their own tasks.

**Independent Test**: Admin logs in → navigates to /tasks → clicks "إضافة مهمة جديدة" → fills form → submits → task appears in list with status "جديد". Employee logs in → sees only their assigned tasks.

### Implementation for User Story 1

- [x] T010 [US1] Create task list view (admin variant) in app/views/tasks/index.php with search bar, filter dropdowns (status, priority, department, assignee), pagination, and "إضافة مهمة جديدة" button (admin only), overdue badge styling, and employee-status alert badges
- [x] T011 [US1] Implement TaskController::index with role-based data filtering in app/controllers/TaskController.php — admin sees all tasks with all filters; employee sees only assigned_to = Auth::id() with limited filters; pass employees/departments lists to view
- [x] T012 [P] [US1] Create task creation form view in app/views/tasks/create.php following Figma-matched pattern (two-column layout, personal info section, job info section) with fields: title, description, priority dropdown, due date, assigned_to employee dropdown, department dropdown; Arabic RTL; CSRF token; include pageScripts for tasks.js
- [x] T013 [US1] Implement TaskController::createForm to load active employees and departments for dropdowns in app/controllers/TaskController.php
- [x] T014 [US1] Implement TaskController::create with full validation (title required max 200, description max 2000, priority in values, assigned_to must be active employee, department_id optional must exist, due_date optional must be valid date), task code generation, and audit logging in app/controllers/TaskController.php
- [x] T015 [US1] Create tasks.js in public/js/tasks.js with AJAX handler for create-employee-form submit (JSON POST to /tasks), error display, and success redirect to /tasks
- [x] T016 [US1] Implement TaskController::activeEmployees API endpoint returning active employees (role=employee, status=active, deleted_at IS NULL) as JSON for the assignee dropdown in app/controllers/TaskController.php

**Checkpoint**: Admin can create tasks and see them in the task list. Employee can see only their assigned tasks. MVP functional.

---

## Phase 4: User Story 2 - Update Task Status (Priority: P2)

**Goal**: Employee can update their own task status forward (جديد → قيد التنفيذ → مكتمل). Admin can change any task's status including regression. Completion date is recorded/cleared.

**Independent Test**: Employee marks a task as "قيد التنفيذ" → status updates. Admin changes task to "مكتمل" → completed_at is set. Admin reopens task → completed_at is cleared.

### Implementation for User Story 2

- [x] T017 [US2] Implement TaskController::updateStatus with RBAC (employee: own tasks only, forward progression only; admin: any task, any status) plus completed_at logic in app/controllers/TaskController.php
- [x] T018 [US2] Add status update functionality to task list view in app/views/tasks/index.php — inline status dropdown for each task row; admin gets all status options; employee gets forward-only options for their own tasks
- [x] T019 [US2] Add status change AJAX handler to public/js/tasks.js — PUT /tasks/{id}/status with CSRF token; success: update status badge in row; error: show Arabic error message; include employee restriction (cannot change others' tasks, cannot regress status)
- [x] T020 [US2] Add completed_at handling to TaskModel::updateTask — when status changes to مكتمل set completed_at to NOW(), when status changes away from مكتمل set completed_at to NULL in app/models/TaskModel.php

**Checkpoint**: Employees can update their own task status forward only. Admins can set any status on any task with completed_at tracking.

---

## Phase 5: User Story 3 - View and Filter Tasks (Priority: P2)

**Goal**: Admin filters tasks by status, priority, department, and assignee. Employee filters their own tasks by status and priority. All lists paginated at 15 per page.

**Independent Test**: Admin filters by status "مكتمل" → only completed tasks appear. Employee filters by priority "عاجل" → only their urgent tasks shown. Search by title returns matching results.

### Implementation for User Story 3

- [x] T021 [US3] Implement search and filter logic in TaskModel::findAllActive — support search (title or task_code LIKE), status filter, priority filter, department_id filter, assigned_to filter, is_overdue computed field via CASE WHEN, and employee_status from JOIN on users table in app/models/TaskModel.php
- [x] T022 [US3] Update TaskController::index to accept and pass all filter params (search, status, priority, department_id, assigned_to, page) to model and view in app/controllers/TaskController.php — for employees, force assigned_to filter to Auth::id()
- [x] T023 [US3] Update task list view app/views/tasks/index.php with full filter bar: search input, status dropdown (الكل/جديد/قيد التنفيذ/مكتمل), priority dropdown (الكل/عاجل/متوسط/منخفض), department dropdown (from departments list), assignee dropdown (admin only, from employees list), and pagination controls matching existing pattern from employees/index.php
- [x] T024 [US3] Add overdue visual indicator to task list rows — if task is_overdue and status != مكتمل, show a yellow "متأخر" badge next to the due date in app/views/tasks/index.php

**Checkpoint**: All filters work for admin. Employees see filtered view of only their own tasks with limited filter options. Overdue tasks are visually highlighted.

---

## Phase 6: User Story 4 - Edit and Delete Tasks (Priority: P3)

**Goal**: Admin can edit all task fields and reassign tasks. Admin can soft-delete tasks. Employees cannot edit or delete.

**Independent Test**: Admin edits task title → change persists. Admin reassigns task from employee A to B → B sees it, A doesn't. Admin deletes task → task gone from active list.

### Implementation for User Story 4

- [x] T025 [US3] Implement TaskController::editForm that loads task by ID, verifies it exists, loads employees and departments for dropdowns in app/controllers/TaskController.php
- [x] T026 [P] [US4] Create task edit form view in app/views/tasks/edit.php with pre-filled fields for title, description, priority, status (admin-only dropdown with all values), due date, assigned_to employee dropdown, department dropdown; include status display showing current status with badge; include completed_at display if مكتمل
- [x] T027 [US4] Implement TaskController::update with full validation (same as create but exclude self from uniqueness checks where applicable), and audit logging in app/controllers/TaskController.php
- [x] T028 [US4] Add edit AJAX handler to public/js/tasks.js — PUT /tasks/{id} with all form fields, CSRF token, error display, success redirect
- [x] T029 [US4] Implement TaskController::delete with CSRF validation, admin-only check, soft delete (set deleted_at), and audit logging in app/controllers/TaskController.php
- [x] T030 [US4] Add delete confirmation modal and AJAX handler to app/views/tasks/index.php and public/js/tasks.js — modal with Arabic text "هل أنت متأكد من حذف هذه المهمة؟", DELETE request with CSRF, redirect on success
- [x] T031 [US4] Add edit and delete action buttons to task list rows in app/views/tasks/index.php — admin-only (checked via Auth::isAdmin()), pencil icon for edit linking to /tasks/{id}/edit, trash icon for delete with data-id and data-title attributes for modal

**Checkpoint**: Admin can edit and delete tasks. Delete is soft. Edit form pre-fills correctly. Delete modal works with AJAX.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: RBAC verification, audit logging confirmation, Arabic text verification, PHP syntax checks, and quickstart validation

- [x] T032 [P] Verify RBAC on all task routes — admin routes inaccessible to employees, status update route accessible to both but with role-based logic in app/controllers/TaskController.php and config/routes.php
- [x] T033 [P] Verify audit logging for all task mutations (create, update, updateStatus, delete) in app/controllers/TaskController.php — check LogModel::logAction calls with entity_type='task', correct entity_id, and action strings
- [x] T034 [P] Verify all Arabic text in task views — labels, error messages, placeholders, success messages, and modal text in app/views/tasks/*.php and app/controllers/TaskController.php
- [x] T035 [P] Run PHP syntax check (php -l) on all new files: TaskController.php, TaskModel.php, all task views, tasks.js
- [x] T036 Run quickstart.md validation — create a task as admin, verify employee can see it, verify filters work, verify status update works for both roles, verify edit/delete works for admin

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: No dependencies — start immediately
- **Phase 2 (Foundational)**: Depends on Phase 1 — BLOCKS all user stories
- **Phase 3 (US1)**: Depends on Phase 2 — MVP
- **Phase 4 (US2)**: Depends on Phase 3 (needs task list view from US1)
- **Phase 5 (US3)**: Depends on Phase 3 (filtering extends list view from US1)
- **Phase 6 (US4)**: Depends on Phase 3 (edit/delete extends list and needs model)
- **Phase 7 (Polish)**: Depends on all user stories complete

### User Story Dependencies

- **US1 (Create & Assign)**: No dependency on other stories — this IS the MVP
- **US2 (Status Update)**: Depends on US1 (needs existing tasks to update)
- **US3 (View & Filter)**: Depends on US1 (needs task list view to add filters to)
- **US4 (Edit & Delete)**: Depends on US1 (needs model and list view)

### Within Each User Story

- Model methods before controller logic
- Controller logic before views
- Views before JavaScript handlers
- Story complete before moving to next priority

### Parallel Opportunities

- T012 and T016 can run in parallel (different concerns: view vs API endpoint)
- T032, T033, T034, T035 can all run in parallel (different verification concerns)

---

## Parallel Example: User Story 1

```text
# After T005-T009 (Foundational) complete:

# Parallel batch 1: View + API endpoint (different files)
Task: T010 — Task list view (app/views/tasks/index.php)
Task: T016 — Active employees API (TaskController method)

# Then sequential:
Task: T011 — TaskController::index (depends on T010 view structure)
Task: T012 — Create form view (app/views/tasks/create.php)
Task: T013 — TaskController::createForm (depends on T012)
Task: T014 — TaskController::create (depends on T005, T008)
Task: T015 — tasks.js AJAX handler (depends on T012, T014)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001-T004)
2. Complete Phase 2: Foundational (T005-T009)
3. Complete Phase 3: User Story 1 (T010-T016)
4. **STOP and VALIDATE**: Admin creates task → assigns to employee → employee sees task in list
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 → Task creation and assignment working → **MVP!**
3. Add US2 → Status updates working
4. Add US3 → Full filtering and search working
5. Add US4 → Full CRUD with edit/delete working
6. Polish → Production-ready

---

## Notes

- Tasks table uses task_code TSK-NNN (sequential, never reused after soft delete) — matches EMP-NNN pattern
- Status progression: جديد → قيد التنفيذ → مكتمل (employees forward only; admin can set any)
- completed_at set/cleared on status change to/from مكتمل
- Overdue computed at query time (due_date < CURDATE() AND status != مكتمل)
- Employee sees only their own tasks; admin sees all
- All mutations logged via LogModel with entity_type='task'