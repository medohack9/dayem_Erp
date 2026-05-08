# Tasks: Departments Module

**Input**: Design documents from `/specs/003-departments/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, contracts/

**Tests**: Not explicitly requested in the feature specification. Test tasks are omitted.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup

**Purpose**: Database migration and route configuration

- [x] T001 Create database migration `database/migrations/003_departments.sql` — departments table, logs table, ALTER users ADD department_id, seed data, indexes
- [x] T002 Add department routes to `config/routes.php` — GET /departments, GET /departments/create, POST /departments, GET /departments/{id}/edit, PUT /departments/{id}, DELETE /departments/{id}, GET /api/departments/active, GET /api/employees/available-managers

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core models and controller skeleton that MUST be complete before any user story work

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T003 [P] Create `app/models/DepartmentModel.php` — CRUD methods (findAllActive, findById, findByName, create, update, softDelete, countActive, getEmployeeCount, reassignEmployees), with case-insensitive name uniqueness check
- [x] T004 [P] Create `app/models/LogModel.php` — Generic log model with logAction(entity_type, entity_id, action, details) method, supports all future modules
- [x] T005 Create `app/controllers/DepartmentController.php` — Skeleton with all method stubs (index, createForm, create, editForm, update, delete, activeList, availableManagers)
- [x] T006 Add `per_page` config — pagination default (15)

**Checkpoint**: Foundation ready — DepartmentModel, LogModel, and DepartmentController skeleton exist. User story implementation can now begin.

---

## Phase 3: US1 + US2 — Create Department & View List (Priority: P1) 🎯 MVP

**Goal**: Admin can create departments and view a paginated, searchable list. Employees can view a read-only list.

**Independent Test**: Log in as admin → create a department → verify it appears in the list → search for it → verify pagination. Log in as employee → verify list is read-only (no action buttons).

### Implementation for US1 + US2

- [x] T007 [US1] Implement `DepartmentController::createForm()` — render create form with manager dropdown data, redirect non-admins to 403
- [x] T008 [US1] Implement `DepartmentController::create()` — validate name (required, unique case-insensitive), manager_id (optional, active user, not already managing another dept), default_salary (optional, numeric), save via DepartmentModel, log action, return JSON response
- [x] T009 [P] [US1] Create `app/views/departments/create.php` — form with name input, manager dropdown, default salary input, CSRF token, AJAX submit
- [x] T010 [US2] Implement `DepartmentController::index()` — fetch paginated active departments with search, employee count, manager name; pass to view; handle page and search query params; employees see read-only view
- [x] T011 [P] [US2] Create `app/views/departments/index.php` — department list table with name, manager, default salary, employee count columns; search input with debounce; pagination links; create/edit/delete buttons (admin only); Arabic labels, RTL layout
- [x] T012 [P] [US1+US2] Create `public/js/departments.js` — AJAX handlers for create department form submission with validation error display, search with 300ms debounce, pagination links
- [x] T013 [US2] Implement `DepartmentController::activeList()` — return JSON array of active departments for dropdowns (id, name)
- [x] T014 [US2] Implement `DepartmentController::availableManagers()` — return JSON array of active users (role=admin|employee, status=active) not already managing a different department, for manager dropdown
- [x] T015 [US1+US2] Update sidebar link in `app/views/components/sidebar.php` — departments link should now work (route exists), active highlighting for 'departments' page

**Checkpoint**: Admin can create departments and see them in a searchable, paginated list. Employee sees read-only list. MVP complete.

---

## Phase 4: US3 — Edit Department (Priority: P2)

**Goal**: Admin can edit an existing department's name, manager, and default salary.

**Independent Test**: Log in as admin → click edit on a department → change name/manager/salary → save → verify changes in list.

### Implementation for US3

- [x] T016 [US3] Implement `DepartmentController::editForm()` — fetch department by id, verify not soft-deleted, load available managers, render edit form with pre-filled values, redirect non-admins to 403
- [x] T017 [US3] Implement `DepartmentController::update()` — validate department exists and not deleted; validate name (unique excluding self), manager_id (not managing another dept excluding current dept), default_salary; update via DepartmentModel; log action; return JSON
- [x] T018 [P] [US3] Create `app/views/departments/edit.php` — pre-filled form with name, manager dropdown, default salary; CSRF token; AJAX submit with error handling
- [x] T019 [US3] Update `public/js/departments.js` — add edit form AJAX handler with validation error display and success redirect

**Checkpoint**: Admin can edit department details. Changes reflect immediately in the list.

---

## Phase 5: US4 — Delete Department with Reassignment (Priority: P2)

**Goal**: Admin can soft-delete a department. If it has employees, a modal prompts reassignment to another department. Last department cannot be deleted.

**Independent Test**: Create 2 departments with employees in one → click delete on the populated dept → modal appears → select target → confirm → employees moved, original dept soft-deleted. Delete last department → error shown.

### Implementation for US4

- [x] T020 [US4] Implement `DepartmentController::delete()` — validate department exists; check not last active department; if has employees, require reassign_to; reassign employees to target dept; soft-delete department; log action; return JSON
- [x] T021 [P] [US4] Create `app/views/departments/delete-modal.php` — modal partial with department dropdown (excluding dept being deleted), confirm button, cancel button; Arabic labels
- [x] T022 [US4] Update `public/js/departments.js` — add delete button handler that checks employee count; show modal if employees exist; send AJAX DELETE with reassign_to; handle success/error; refresh list

**Checkpoint**: Admin can delete departments with reassignment. Last department is protected. Modal works correctly.

---

## Phase 6: US5 + US6 — Manager Integrity & Default Salary (Priority: P3)

**Goal**: Manager assignment enforces one-to-one constraint. Default salary pre-fills in future employee forms. Manager field clears when employee is terminated.

**Independent Test**: Assign manager → verify one-to-one constraint prevents assigning same employee to two depts. Set default salary → verify it's stored and retrieved correctly.

### Implementation for US5 + US6

- [x] T023 [US5] Add manager clearing logic — when a user's status changes to 'terminated' or user is soft-deleted, clear `departments.manager_id` where that user is the manager. Add method to DepartmentModel and call from UserController (or future employee module)
- [x] T024 [US6] Add `getDefaultSalary()` method to DepartmentModel — fetch default_salary for a given department_id, to be used by future employee creation form for pre-fill. Not implementing the employee form UI in this phase (that's Phase 4 — Employees)
- [x] T025 [US5+US6] Verify create and edit forms properly enforce one-to-one manager constraint — test in availableManagers() that already-assigned managers are excluded, add validation error message in Arabic for constraint violation

**Checkpoint**: One-to-one manager constraint enforced. Default salary field works. Manager field clears on user termination.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: RBAC verification, audit logging, Arabic/RTL verification, syntax checks, validation

- [x] T026 Verify RBAC — log in as employee, confirm all create/edit/delete routes return 403 and sidebar shows no management buttons
- [x] T027 Verify audit logging — create, edit, and delete a department; check `logs` table for entries with entity_type='department'
- [x] T028 Verify Arabic text — create department with Arabic name, search for it, edit it, verify no encoding issues in list/detail/edit
- [x] T029 Run PHP syntax check on all new files (DepartmentController.php, DepartmentModel.php, LogModel.php, all views, departments.js)
- [x] T030 Run quickstart.md validation — follow all steps in `specs/003-departments/quickstart.md` and verify each test passes
- [x] T031 Update `app/views/components/sidebar.php` — ensure departments link active state works correctly with current route highlighting

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 completion — BLOCKS all user stories
- **US1+US2 (Phase 3)**: Depends on Phase 2 — MVP deliverable
- **US3 (Phase 4)**: Depends on Phase 3 (needs list view for edit link, DepartmentModel for update)
- **US4 (Phase 5)**: Depends on Phase 3 (needs DepartmentModel for soft-delete and reassign). Can parallel with Phase 4 since they touch different methods
- **US5+US6 (Phase 6)**: Depends on Phase 3 (needs DepartmentModel). Can parallel with Phases 4-5
- **Polish (Phase 7)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 + US2 (P1)**: Can start after Phase 2 — no dependencies on other stories
- **US3 (P2)**: Depends on US2 (list view provides edit links) and US1 (create provides departments to edit)
- **US4 (P2)**: Depends on US2 (list view provides delete buttons). Independent of US3 (different controller methods)
- **US5 + US6 (P3)**: Depends on US1 (create form is where manager/salary fields live). Independent of US3/US4

### Within Each Phase

- Phase 1: T001 then T002 (routes depend on migration for testing)
- Phase 2: T003 and T004 can run in parallel; T005 depends on both for method stubs; T006 can run in parallel
- Phase 3: T007-T008 are backend; T009, T011 are frontend views; T012 depends on T009 and T011; T013-T014 are API endpoints that can run in parallel
- Phase 4: T016-T017 backend, then T018 view, then T019 JS update
- Phase 5: T020 backend, then T021 view and T022 JS in parallel
- Phase 6: T023-T025 can run in parallel

### Parallel Opportunities

- T003 + T004: Both are new model files with no cross-dependencies
- T009 + T011: Both are view files, different pages
- T013 + T014: Both are read-only API endpoints
- T021 + T020: View and backend for delete can be developed in parallel once API contract is agreed
- T023 + T024 + T025: Three independent verification/enhancement tasks

---

## Parallel Example: Phase 3 (US1+US2)

```text
# Backend first:
Task T007: "Implement DepartmentController::createForm() in app/controllers/DepartmentController.php"
Task T008: "Implement DepartmentController::create() in app/controllers/DepartmentController.php"
Task T010: "Implement DepartmentController::index() in app/controllers/DepartmentController.php"
Task T013: "Implement DepartmentController::activeList() in app/controllers/DepartmentController.php"
Task T014: "Implement DepartmentController::availableManagers() in app/controllers/DepartmentController.php"

# Then views in parallel:
Task T009: "Create app/views/departments/create.php"
Task T011: "Create app/views/departments/index.php"

# Then JS (depends on views):
Task T012: "Create public/js/departments.js"

# Then sidebar:
Task T015: "Update app/views/components/sidebar.php"
```

---

## Implementation Strategy

### MVP First (US1 + US2 Only)

1. Complete Phase 1: Setup (migration + routes)
2. Complete Phase 2: Foundational (models + controller skeleton)
3. Complete Phase 3: US1 + US2 (Create + List)
4. **STOP and VALIDATE**: Create a department, see it in the list, search for it
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 + US2 → Test independently → Deploy/Demo (MVP!)
3. Add US3 → Test edit flow independently → Deploy/Demo
4. Add US4 → Test delete + reassignment independently → Deploy/Demo
5. Add US5 + US6 → Test manager/salary constraints → Deploy/Demo
6. Polish → Final validation

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- US5 and US6 are naturally integrated into US1/US3 (manager and default_salary are form fields)
- The `logs` table is created in this phase but designed generically for all future modules (Phase 9)
- `department_id` column added to `users` table via ALTER TABLE — will be used by Phase 4 (Employees)
- Default salary pre-fill in employee forms is deferred to Phase 4 (Employees module)
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently


