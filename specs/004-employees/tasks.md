# Tasks: Employee Management

**Input**: Design documents from `/specs/004-employees/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, contracts/http-routes.md

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3, US4)

---

## Phase 1: Setup

**Purpose**: Add routes and migration for the employee module

- [x] T001 Add employee routes to `config/routes.php` (7 routes: list, create form, create API, edit form, update API, delete API) plus department default-salary API route
- [x] T002 Run migration `database/migrations/004_employees.sql` to add employee-specific columns to users table (national_id, birth_date, salary, hire_date, employee_code) — already executed, verify columns exist
- [x] T003 Update `app/core/Auth.php` to add `status()` and `isActive()` helper methods, and store user status in session on login

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Create EmployeeModel and DepartmentModel enhancement needed by all user stories

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T004 Create `app/models/EmployeeModel.php` with: `findAllActive()` (paginated search + filter by department/status), `findById()`, `findByEmail()`, `findByPhone()`, `findByNationalId()`, `findByEmployeeCode()`, `generateEmployeeCode()`, `create()`, `updateEmployee()`, `softDelete()`, `generateTempPassword()`
- [x] T005 Add `defaultSalary()` method to `app/controllers/DepartmentController.php` (GET `/api/departments/{id}/default-salary`)
- [x] T006 Update `app/core/Auth.php` login method to check user status (active/suspended/terminated) and block non-active users with Arabic messages
- [x] T007 Create `app/views/employees/` directory

**Checkpoint**: Foundation ready — EmployeeModel, Auth status check, and directory structure in place

---

## Phase 3: User Story 1 - View Employee List (Priority: P1) 🎯 MVP

**Goal**: Admin can view, search, filter, and paginate employees in a table with name/avatar, employee code, department, phone, salary, status badges, and action buttons

**Independent Test**: Log in as admin, navigate to /employees, verify table shows employees with search/filter working

- [x] T008 [US1] Create `app/controllers/EmployeeController.php` — skeleton with `index()` method — skeleton with `index()` method that loads employees with pagination, search, department filter, and status filter. Pass departments list to view for filter dropdown
- [x] T009 [US1] Create `app/views/employees/index.php` — employee list view matching Figma — employee list view matching Figma EmployeesPage design: search bar, department dropdown filter, status dropdown filter (active/suspended/terminated/all), table with avatar+name, employee code, department, phone, salary, status badge (green=active, yellow=suspended, red=terminated), edit/delete action buttons, pagination, empty state message
- [x] T010 [US1] Add employee list AJAX search/filter in `public/js/employees.js` — handle status filter change, department filter change with page reload using query params (matching departments module pattern)
- [x] T011 [P] [US1] Update `app/views/components/sidebar.php` — employees link for admin — ensure employees link works for admin role with correct `activePage` match

**Checkpoint**: Employee list page loads with search, filter, and pagination working

---

## Phase 4: User Story 2 - Add New Employee (Priority: P2)

**Goal**: Admin can create a new employee with personal info, job info, auto-generated code, and department default salary suggestion

**Independent Test**: Navigate to /employees/create, fill form, submit, verify new employee appears in list with correct data and auto-generated code

- [x] T012 [US2] Add `createForm()` method to `app/controllers/EmployeeController.php` — load active departments for dropdown, pass to view, set page title and activePage
- [x] T013 [US2] Add `create()` method to `app/controllers/EmployeeController.php` — validate all fields (name required/max100, email valid/unique, phone Egyptian format/unique, national_id 14 digits/unique, birth_date required, salary positive, department_id exists), generate employee code, create temp password, insert via EmployeeModel, log action, return JSON response
- [x] T014 [US2] Create `app/views/employees/create.php` — add employee form matching Figma AddEmployeePage design: two-column layout (personal info + job info on left, actions on right), fields for name, email, phone, national_id, birth_date, salary, department dropdown, hire_date, CSRF token, Arabic labels and placeholders, Tailwind styling consistent with department create form
- [x] T015 [US2] Add `defaultSalary()` method to `app/controllers/DepartmentController.php` — API endpoint returning JSON with department default_salary for AJAX salary suggestion
- [x] T016 [US2] Add create employee AJAX handler in `public/js/employees.js` — form submit with validation, department change triggers salary suggestion via AJAX to `/api/departments/{id}/default-salary`, field-level error display, success redirect to list
- [x] T017 [US2] Add department default-salary route to `config/routes.php` — `GET /api/departments/{id}/default-salary`

**Checkpoint**: New employee can be created with auto-generated code and department salary suggestion

---

## Phase 5: User Story 3 - Edit Employee (Priority: P3)

**Goal**: Admin can edit all employee fields including department change and status change, with audit logging

**Independent Test**: Click edit on an employee, change data and status, submit, verify changes persist and audit log records the update

- [x] T018 [US3] Add `editForm()` method to `app/controllers/EmployeeController.php` — load employee by ID, load active departments for dropdown, pass to view, 404 if not found
- [x] T019 [US3] Add `update()` method to `app/controllers/EmployeeController.php` — validate all fields (uniqueness checks exclude current employee), update via EmployeeModel, log action with details, return JSON response
- [x] T020 [US3] Create `app/views/employees/edit.php` — edit employee form pre-filled with current data, same layout as create but with status dropdown (active/suspended/terminated), CSRF token, Arabic labels, Tailwind styling
- [x] T021 [US3] Add edit employee AJAX handler in `public/js/employees.js` — form submit with validation, field-level error display, success redirect to list

**Checkpoint**: Employee data can be edited including status changes, with audit logging

---

## Phase 6: User Story 4 - Delete Employee (Priority: P4)

**Goal**: Admin can soft-delete an employee with confirmation dialog; system clears department manager if employee was a manager; prevents admin self-deletion

**Independent Test**: Click delete on an employee, confirm deletion in modal, verify employee disappears from active list and audit log records deletion

- [x] T022 [US4] Add `delete()` method to `app/controllers/EmployeeController.php` — validate CSRF, check employee exists, prevent self-deletion (user ID vs session user ID), clear department manager if employee was a manager, soft-delete via EmployeeModel, log action, return JSON response
- [x] T023 [US4] Add delete confirmation modal to `app/views/employees/index.php` — modal with confirmation message, cancel/confirm buttons, Tailwind styling consistent with department delete modal
- [x] T024 [US4] Add delete employee AJAX handler in `public/js/employees.js` — delete button click opens modal, confirm button sends DELETE request with CSRF token, handle success/error responses, remove row from table on success

**Checkpoint**: Employees can be soft-deleted with confirmation, manager conflicts handled, self-deletion prevented

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: RBAC verification, audit logging verification, Arabic consistency, syntax checks, quickstart validation

- [x] T025 Verify RBAC enforcement — confirm all employee routes require admin role; employee role users get 403
- [x] T026 Verify audit logging — test that create, update, delete, and status change actions are logged in the `logs` table with correct entity_type, entity_id, action, and details
- [x] T027 Verify Arabic text — ensure all UI labels, error messages, success messages, and placeholder text are in Arabic
- [x] T028 Run PHP syntax check on all new files: `EmployeeController.php`, `EmployeeModel.php`, view files
- [x] T029 Run quickstart.md validation — follow the quickstart steps to verify the entire feature works end-to-end: list → create → edit → delete → login block

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 completion — BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Phase 2. No dependencies on other stories
- **User Story 2 (Phase 4)**: Depends on Phase 2. Shares EmployeeController with US1 (T008 skeleton must exist)
- **User Story 3 (Phase 5)**: Depends on Phase 2. Shares EmployeeController with US1/US2
- **User Story 4 (Phase 6)**: Depends on Phase 2. Shares EmployeeController and list view with US1
- **Polish (Phase 7)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (View List)**: Foundational only — standalone MVP
- **US2 (Add Employee)**: Foundational + US1 controller skeleton — form needs controller
- **US3 (Edit Employee)**: Foundational + US1 controller skeleton — form needs controller
- **US4 (Delete Employee)**: Foundational + US1 list view (modal is in index.php)

### Within Each User Story

- Controller method before view
- View before JavaScript handler
- JavaScript handler before testing

### Parallel Opportunities

- T003, T005, T006 can run in parallel (different files)
- T009, T011 can run in parallel after T008
- T014, T015, T017 can run in parallel (different files)
- T020, T021 after T018, T019
- T023, T024 after T022

---

## Parallel Example: Phase 2

```bash
# Launch all foundational tasks together:
Task: "Create EmployeeModel in app/models/EmployeeModel.php"
Task: "Add defaultSalary to DepartmentModel in app/models/DepartmentModel.php"
Task: "Update Auth.php login status check in app/core/Auth.php"
```

## Parallel Example: Phase 3 (US1)

```bash
# After controller skeleton (T008):
Task: "Create employee list view in app/views/employees/index.php"
Task: "Update sidebar in app/views/components/sidebar.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test employee list with search/filter/pagination independently
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 → Test list/search/filter → Deploy (MVP!)
3. Add US2 → Test create employee → Deploy
4. Add US3 → Test edit employee → Deploy
5. Add US4 → Test delete employee → Deploy
6. Polish → Final validation → Deploy

### Notes

- Migration 004_employees.sql has already been executed — verify columns exist
- EmployeeModel extends the `users` table (no separate employees table)
- Employee codes are never reused (even after soft delete)
- Default password is auto-generated via `password_hash()` — employees don't get login access initially
- All UI follows Figma EmployeesPage/AddEmployeePage designs
- All error messages in Arabic
- Auth status check must be added BEFORE employee pages go live (else suspended users could still log in)