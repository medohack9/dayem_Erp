# Tasks: Dashboard

**Input**: Design documents from `/specs/010-dashboard/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Single project**: `app/` at repository root (PHP MVC)
- Paths shown below use the existing project structure

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Add new model and API routes for dashboard data

- [x] T001 Create DashboardModel class in app/models/DashboardModel.php with empty class structure
- [x] T002 [P] Add API routes for dashboard endpoints in config/routes.php

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core model methods that all user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T003 Implement getKpiStats() method in app/models/DashboardModel.php - returns admin KPI values (employees, tasks, tickets, salaries)
- [x] T004 Implement getEmployeeStats() method in app/models/DashboardModel.php - returns employee personal KPIs
- [x] T005 Implement getTaskStatusBreakdown() method in app/models/DashboardModel.php - returns task counts by status
- [x] T006 [P] Implement getLast8Weeks() helper method in app/models/DashboardModel.php - calculates week periods for charts
- [x] T007 Implement getTasksChartData() method in app/models/DashboardModel.php - returns 8 weeks of task created/completed counts
- [x] T008 Implement getTicketsChartData() method in app/models/DashboardModel.php - returns 8 weeks of ticket created/closed counts

**Checkpoint**: DashboardModel complete with all data aggregation methods - user story implementation can now begin

---

## Phase 3: User Story 1 - View Dashboard Overview (Priority: P1) 🎯 MVP

**Goal**: Admin sees 4 KPI cards (Employees, Tasks, Tickets, Salaries) with accurate real-time counts. Employee sees personalized dashboard with own data.

**Independent Test**: Login as admin → land on dashboard → see 4 KPI cards with current counts. Login as employee → see personalized stats.

### Implementation for User Story 1

- [x] T009 [P] [US1] Create kpi-card.php component in app/views/components/kpi-card.php - reusable card with icon, label, value, link, and hover tooltip support
- [x] T010 [US1] Update HomeController::index() in app/controllers/HomeController.php - inject role-based dashboard data
- [x] T011 [US1] Rewrite dashboard view in app/views/home/index.php - display admin KPI cards (4) or employee cards (4 personal stats)
- [x] T012 [US1] Add GET /api/dashboard/stats route handler in app/controllers/HomeController.php - returns JSON with KPI values
- [x] T013 [US1] Style KPI cards with Tailwind - yellow accent borders, RTL layout, hover tooltip animations

**Checkpoint**: Admin dashboard shows 4 KPI cards. Employee dashboard shows personalized stats. Navigation works but charts not yet implemented.

---

## Phase 4: User Story 2 - View Task Completion Chart (Priority: P1)

**Goal**: Admin sees bar chart showing tasks created vs completed per week for last 8 weeks with Arabic labels.

**Independent Test**: Admin views dashboard → sees task chart section with 8 weeks of bars → RTL Arabic labels.

### Implementation for User Story 2

- [x] T014 [P] [US2] Add GET /api/dashboard/tasks-chart route handler in app/controllers/HomeController.php - returns JSON with labels, created[], completed[]
- [x] T015 [US2] Create dashboard.js in public/js/dashboard.js - Chart.js initialization for task completion chart
- [x] T016 [US2] Add task chart section to app/views/home/index.php - canvas element and chart container
- [x] T017 [US2] Include Chart.js CDN and dashboard.js in layout when activePage === 'home'

**Checkpoint**: Task completion chart renders with Arabic week labels, RTL layout, and correct data.

---

## Phase 5: User Story 3 - View Ticket Trends Chart (Priority: P1)

**Goal**: Admin sees bar chart showing tickets created vs closed per week for last 8 weeks with Arabic labels.

**Independent Test**: Admin views dashboard → sees ticket Chart section with 8 weeks of bars → RTL Arabic labels.

### Implementation for User Story 3

- [x] T018 [P] [US3] Add GET /api/dashboard/tickets-chart route handler in app/controllers/HomeController.php - returns JSON with labels, created[], closed[]
- [x] T019 [US3] Add ticket chart initialization to public/js/dashboard.js - second Chart.js instance for ticket trends
- [x] T020 [US3] Add ticket chart section to app/views/home/index.php - canvas element and chart container

**Checkpoint**: Ticket trends chart renders with Arabic week labels, RTL layout, and correct data. Both charts display correctly.

---

## Phase 6: User Story 4 - Quick Navigation from Dashboard (Priority: P2)

**Goal**: Clicking KPI cards navigates to corresponding module pages.

**Independent Test**: Admin clicks on Employees card → navigates to employees list page. Same for Tasks, Tickets, Salaries.

### Implementation for User Story 4

- [x] T021 [US4] Wrap KPI cards in clickable links in app/views/home/index.php - each card links to /employees, /tasks, /tickets, /salaries
- [x] T022 [US4] Add cursor-pointer and hover scale effect to KPI cards via Tailwind classes

**Checkpoint**: All KPI cards are clickable and navigate to correct module pages.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T023 [P] Add empty state handling in app/views/home/index.php - show "لا توجد بيانات" for empty charts, "0" for zero KPIs
- [x] T024 [P] Add responsive styles for tablet viewports in app/views/home/index.php - charts stack vertically below 768px
- [x] T025 Verify employee dashboard shows only own data - test with employee login
- [x] T026 Verify admin dashboard shows all data - test with admin login
- [x] T027 Run quickstart.md validation scenarios

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-6)**: All depend on Foundational phase completion
  - US1 (KPI cards) should complete first - provides base dashboard structure
  - US2 (Task Chart) depends on US1 dashboard structure
  - US3 (Ticket Chart) depends on US1 dashboard structure
  - US4 (Navigation) depends on US1 KPI cards
- **Polish (Phase 7)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational - No dependencies on other stories
- **User Story 2 (P1)**: Depends on US1 dashboard view being complete
- **User Story 3 (P1)**: Depends on US1 dashboard view being complete, can run parallel to US2
- **User Story 4 (P2)**: Depends on US1 KPI cards being complete

### Within Each User Story

- Models before controllers
- Controllers before views
- Core implementation before styling

### Parallel Opportunities

- T001 and T002 can run in parallel (different files)
- T006 can run in parallel with T003-T005 (helper method, no dependencies)
- T009 (component) and T010 (controller) can run in parallel
- T014 and T018 (API handlers) can run in parallel
- T023 and T024 (polish tasks) can run in parallel

---

## Parallel Example: Phase 2 Foundational

```bash
# These can run in parallel:
Task: "Implement getKpiStats() method in app/models/DashboardModel.php"
Task: "Implement getEmployeeStats() method in app/models/DashboardModel.php"
Task: "Implement getTaskStatusBreakdown() method in app/models/DashboardModel.php"

# Then this can run parallel with the above:
Task: "Implement getLast8Weeks() helper method in app/models/DashboardModel.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1 (KPI cards)
4. **STOP and VALIDATE**: Test dashboard shows 4 KPI cards
5. Deploy/demo if ready

### Full Implementation

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → KPI cards work
3. Add User Story 2 → Test independently → Task chart works
4. Add User Story 3 → Test independently → Ticket chart works
5. Add User Story 4 → Test independently → Navigation works
6. Polish → Full dashboard complete

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- No automated tests - manual testing via browser

---

## ✅ Implementation Complete

All 27 tasks have been implemented successfully.