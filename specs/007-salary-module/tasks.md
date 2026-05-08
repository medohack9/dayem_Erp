# Tasks: Salary Module

**Input**: Design documents from `/specs/007-salary-module/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 [P] Create folder structure `app/views/salaries/components` and `public/js/salaries`
- [x] T002 [P] Register salary routes in `config/routes.php` per contracts/http-routes.md

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T003 Create database migration `database/migrations/009_salaries.sql` for `salaries` and `salary_configs` tables
- [x] T004 [P] Create `SalaryConfigModel.php` in `app/models/` for managing employee pay scales
- [x] T005 [P] Create `SalaryModel.php` in `app/models/` for managing monthly salary records
- [x] T006 [P] Initialize `SalaryController.php` in `app/controllers/` with basic RBAC middleware

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Admin Generates Monthly Salary Records (Priority: P1) 🎯 MVP

**Goal**: Allow admins to generate monthly payroll records and recalculate them if configuration changes.

**Independent Test**: Generate May 2026 salaries, verify 1 record per active employee in DB, then update a config and verify "Recalculate" updates the unpaid record.

### Implementation for User Story 1

- [x] T007 [P] [US1] Implement `generateMonthlySalaries` in `app/models/SalaryModel.php` (pulling from `salary_configs` or `departments`)
- [x] T008 [P] [US1] Implement `recalculateUnpaid` in `app/models/SalaryModel.php`
- [x] T009 [US1] Implement `generate` and `recalculate` actions in `app/controllers/SalaryController.php`
- [x] T010 [US1] Create generation form UI in `app/views/salaries/index.php`
- [x] T011 [US1] Add AJAX handler for recalculation in `public/js/salaries.js`
- [x] T012 [US1] Add audit logging for generation and recalculation in `app/controllers/SalaryController.php`

**Checkpoint**: User Story 1 is functional - admins can generate and sync payroll.

---

## Phase 4: User Story 2 - Admin Confirms Salary Payment (Priority: P1)

**Goal**: Enable admins to mark individual records as "Paid" with date and admin tracking.

**Independent Test**: Click "Mark as Paid" on an unpaid record and verify status, paid_at date, and paid_by admin fields in DB.

### Implementation for User Story 2

- [x] T013 [P] [US2] Implement `markAsPaid` in `app/models/SalaryModel.php`
- [x] T014 [US2] Implement `pay` action in `app/controllers/SalaryController.php`
- [x] T015 [US2] Create payment confirmation button/modal in `app/views/salaries/index.php`
- [x] T016 [US2] Add AJAX handler for payment confirmation in `public/js/salaries.js`
- [x] T017 [US2] Add audit logging for payment confirmation in `app/controllers/SalaryController.php`

**Checkpoint**: User Story 2 is functional - admins can process payments.

---

## Phase 5: User Story 3 - Admin Views Salary List with Filters (Priority: P2)

**Goal**: Provide a paginated, filterable list of all salary records for admins.

**Independent Test**: Filter by a specific month and department, verify only matching records appear across paginated results.

### Implementation for User Story 3

- [x] T018 [P] [US3] Implement `findAllPaginated` with filters in `app/models/SalaryModel.php`
- [x] T019 [US3] Implement filtered list display in `app/views/salaries/index.php`
- [x] T020 [US3] Create filter component (Month, Department, Status) in `app/views/salaries/components/filters.php`
- [x] T021 [US3] Implement pagination logic in `app/controllers/SalaryController.php`

---

## Phase 6: User Story 4 - Employee Views Own Salary History (Priority: P2)

**Goal**: Allow employees to securely view their own payment history.

**Independent Test**: Login as employee, verify only personal records are visible and others' records are inaccessible via URL.

### Implementation for User Story 4

- [x] T022 [P] [US4] Implement `findUserHistory` in `app/models/SalaryModel.php`
- [x] T023 [US4] Implement `myHistory` action in `app/controllers/SalaryController.php` with ownership check
- [x] T024 [US4] Create employee-specific history view in `app/views/salaries/index.php` (conditional rendering based on role)

---

## Phase 7: User Story 5 - Admin Views Salary Summary Statistics (Priority: P3)

**Goal**: Display high-level payroll totals and counts for the selected month.

**Independent Test**: Compare summary card totals with the sum of individual records on the page.

### Implementation for User Story 5

- [x] T025 [P] [US5] Implement `getSummaryStats` in `app/models/SalaryModel.php`
- [x] T026 [US5] Create summary cards UI in `app/views/salaries/components/summary.php`
- [x] T027 [US5] Integrate summary stats into `index` action in `app/controllers/SalaryController.php`

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final touches and configuration management

- [x] T028 [P] Implement `SalaryConfigController.php` for managing individual overrides
- [x] T029 Create configuration management UI in `app/views/salaries/config.php`
- [x] T030 Ensure all views use brand colors (#F4C400) and Arabic RTL layout
- [x] T031 Run validation against `specs/007-salary-module/quickstart.md`
- [x] T032 [P] Add final audit log entries for configuration changes

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)** & **Foundational (Phase 2)**: MUST complete first.
- **User Story 1 (US1)**: Must be completed to enable any meaningful testing of US2-US5.
- **User Story 3 (US3)**: Provides the container for US1, US2, and US5 UI.

### Parallel Opportunities

- T004, T005, T006 (Foundational Models/Controllers)
- T007, T008 (US1 Backend logic)
- T013 (US2 Backend logic)
- T018 (US3 Backend logic)
- T022 (US4 Backend logic)
- T025 (US5 Backend logic)

---

## Implementation Strategy

### MVP First (US1 + US2)

1. Complete Setup + Foundational.
2. Complete US1 (Generation) and US2 (Payment).
3. **STOP and VALIDATE**: Verify a full payroll cycle can be completed.

### Incremental Delivery

1. Add US3 (Filtering) to manage growing record sets.
2. Add US4 (Employee Access) to reduce admin support load.
3. Add US5 (Summaries) for admin reporting.
