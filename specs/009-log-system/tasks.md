# Tasks: Log System

**Input**: Design documents from `/specs/009-log-system/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: No automated test framework — manual testing via browser per project convention.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Routes and directory setup for the logs module

- [x] T001 Add log routes to `config/routes.php` per contracts/http-routes.md: GET /logs, GET /api/logs, GET /logs/export, GET /logs/export/today, GET /logs/{id}, GET /api/logs/filters — all with admin-only role restriction
- [x] T002 [P] Create views directory `app/views/logs/` and `app/views/logs/partials/`

**Checkpoint**: Routes registered, views directory exists

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Extend LogModel with methods needed by all user stories. MUST be complete before any user story work begins.

- [x] T003 Extend `app/models/LogModel.php` with methods: findAllWithFilters (paginated query with user_id, action, entity_type, entity_id, date_from, date_to filters, LEFT JOIN users for user_name), countWithFilters (total count for pagination), getDistinctActions (for filter dropdown), getDistinctEntityTypes (for filter dropdown), findById (single log with user_name JOIN)

**Checkpoint**: LogModel extended with all filter/query methods. User story implementation can begin.

---

## Phase 3: User Story 1 - View System Logs (Priority: P1) 🎯 MVP

**Goal**: Admins can view a paginated list of all system logs ordered by most recent first, with user name, action, entity type, entity ID, timestamp, and IP address.

**Independent Test**: Login as admin → navigate to /logs → see paginated list of logs with all fields. Employee gets 403 when accessing /logs.

### Implementation for User Story 1

- [x] T004 [US1] Create `app/controllers/LogController.php` with `index` method: load logs via LogModel::findAllWithFilters with pagination (50 per page), enforce admin-only access (403 for non-admin), load filter options (users, actions, entity_types) for dropdowns, render logs/index view with logs, pagination, and filter data
- [x] T005 [US1] Create `app/views/logs/index.php` with RTL Arabic log list: page title "سجلات النظام", filter summary showing total count, log table with columns (المستخدم, الإجراء, النوع, الرقم, الوقت, IP), user name with deleted indicator, pagination controls (50 per page), empty state message "لا توجد سجلات" when no logs exist, Dayem brand styling (#F4C400/#111111)
- [x] T006 [US1] Create `public/js/logs.js` with log list handlers: pagination click navigation, initial page setup

**Checkpoint**: Admin can view paginated log list. Employee receives 403. Deleted users show "مستخدم محذوف".

---

## Phase 4: User Story 2 - Filter and Search Logs (Priority: P1)

**Goal**: Admins can filter logs by user, action type, entity type, date range, and search by entity ID. All filters are combinable with clear filters button.

**Independent Test**: Admin filters by user → sees only that user's logs. Admin enters entity ID → sees all actions on that record. Admin clicks clear filters → all filters reset.

### Implementation for User Story 2

- [x] T007 [US2] Update `app/controllers/LogController.php` index method to accept and pass filter params: user_id from dropdown, action from dropdown, entity_type from dropdown, entity_id from search input, date_from and date_to from date inputs — all passed to LogModel::findAllWithFilters
- [x] T008 [US2] Update `app/views/logs/index.php` with filter UI: user dropdown (populated from users table with active/suspended/deleted indicators), action dropdown (populated from distinct actions), entity_type dropdown (populated from distinct entity types), entity_id search input, date_from input (type date), date_to input (type date), clear filters button, filters styled consistently with existing module filters
- [x] T009 [US2] Add filter handlers to `public/js/logs.js`: filter change triggers page reload with query params, clear filters resets URL to /logs, date input changes trigger list refresh, filters persist in URL for bookmarking

**Checkpoint**: All filters work individually and combined. Clear filters resets all. Entity ID search finds all related logs.

---

## Phase 5: User Story 3 - Export Logs to CSV (Priority: P2)

**Goal**: Admins can export filtered logs to CSV with proper Arabic encoding. Quick export button for today's logs.

**Independent Test**: Admin clicks export → CSV downloads with all matching logs → opens in Excel with Arabic characters correct.

### Implementation for User Story 3

- [x] T010 [US3] Add `export` method to `app/controllers/LogController.php`: apply same filters as index, query logs via LogModel::findAllWithFilters (limit 10000), set headers (Content-Type: text/csv; charset=utf-8, Content-Disposition with timestamp filename), output UTF-8 BOM for Excel Arabic support, use fputcsv to output rows (user_name, action, entity_type, entity_id, details, ip_address, created_at), stream to php://output
- [x] T011 [US3] Add `exportToday` method to `app/controllers/LogController.php`: preset date_from and date_to to today, call export logic
- [x] T012 [US3] Add export buttons to `app/views/logs/index.php`: "Export CSV" button (respects current filters), "Export Today's Logs" quick button, export limit notice (max 10,000 records)
- [x] T013 [US3] Add export handlers to `public/js/logs.js`: export button click triggers GET to /logs/export with current filter params, today export triggers GET to /logs/export/today

**Checkpoint**: CSV export works with filters. Arabic displays correctly in Excel. Today's export provides quick access.

---

## Phase 6: User Story 4 - View Log Details (Priority: P2)

**Goal**: Admins can click on a log entry to see full details including parsed JSON details field.

**Independent Test**: Admin clicks on log row → modal opens with all fields → JSON details display as readable key-value pairs.

### Implementation for User Story 4

- [x] T014 [US4] Add `detail` method to `app/controllers/LogController.php`: find log by ID via LogModel::findById, return JSON response with log data (user_name, action, entity_type, entity_id, details as parsed array, ip_address, created_at)
- [x] T015 [US4] Create `app/views/logs/partials/detail-modal.php`: modal overlay with close button, log detail fields (user name, action, entity type, entity ID, IP address, timestamp), JSON details section displayed as key-value table, "No additional details" message when details is null
- [x] T016 [US4] Add log row click handler to `public/js/logs.js`: row click triggers fetch to /logs/{id}, response populates detail modal, modal shows/hides with animation, handles missing details gracefully

**Checkpoint**: Clicking log row opens detail modal. JSON details are readable. Modal closes correctly.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Filter options API and verification

- [x] T017 [P] Add `filterOptions` method to `app/controllers/LogController.php`: return JSON with distinct actions, distinct entity_types, users list (id, name, status) — for filter dropdown population
- [x] T018 [P] Add `list` method to `app/controllers/LogController.php` for API access: return paginated logs as JSON (same logic as index but JSON response)
- [ ] T019 Run quickstart.md verification checklist: admin log viewing works, all filters work combined, pagination at 50 per page, CSV export downloads correctly, CSV Arabic encoding correct in Excel, detail modal shows JSON details, employee gets 403, Arabic RTL throughout, sidebar logs link works

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Foundational — no dependencies on other stories
- **US2 (Phase 4)**: Depends on Foundational + US1 (extends index view with filters)
- **US3 (Phase 5)**: Depends on Foundational + US2 (export uses same filters)
- **US4 (Phase 6)**: Depends on Foundational + US1 (adds detail modal to list view)
- **Polish (Phase 7)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (View Logs)**: Can start after Foundational — No dependencies on other stories
- **US2 (Filter & Search)**: Depends on US1 (extends the index view)
- **US3 (Export CSV)**: Depends on US2 (uses same filter logic)
- **US4 (View Details)**: Depends on US1 (adds modal to log list)

### Within Each User Story

- Controller methods before views
- Views before JavaScript handlers
- Story complete before moving to next priority

### Parallel Opportunities

- T001, T002 can run in parallel (routes + directory creation)
- T017, T018 can run in parallel in Polish phase

---

## Parallel Example: Setup

```text
# All setup tasks can run in parallel:
Task T001: Add routes to config/routes.php
Task T002: Create views directories
```

## Parallel Example: Polish

```text
# All polish tasks can run in parallel:
Task T017: Add filterOptions method
Task T018: Add list method
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (routes + directories)
2. Complete Phase 2: Foundational (extend LogModel)
3. Complete Phase 3: User Story 1 (view logs)
4. **STOP and VALIDATE**: Admin can view log list with pagination
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → View logs → Test → Deploy (MVP!)
3. Add User Story 2 → Filter & search → Test → Deploy
4. Add User Story 3 → CSV export → Test → Deploy
5. Add User Story 4 → Log details → Test → Deploy
6. Add Polish → API endpoints → Final deploy

### Parallel Team Strategy

With multiple developers after Foundational phase:
- Developer A: US1 (View Logs) → then US2 (Filter & Search)
- Developer B: US3 (Export CSV)
- Developer C: US4 (Log Details)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Manual testing per project convention (no automated test framework)
- All views must be Arabic RTL with Dayem brand colors (#F4C400/#111111)
- No database migrations needed — logs table exists from Phase 1
- 50 items per page (higher density than other modules)
- CSV export limit: 10,000 records maximum