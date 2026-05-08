# Feature Specification: Dashboard

**Feature Branch**: `010-dashboard`
**Created**: 2026-05-01
**Status**: Draft
**Input**: Phase 10 of the Dayem ERP implementation plan — admin overview dashboard with KPI cards and charts for system monitoring.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View Dashboard Overview (Priority: P1) 🎯 MVP

As an admin, I want to see a dashboard with key performance indicators when I log in so I can quickly understand the system's current state. I see KPI cards showing employee count, task count, ticket count, and total salaries at a glance.

**Why this priority**: The dashboard is the admin's entry point to the system — it provides immediate visibility into the organization's health and activity.

**Independent Test**: Login as admin → land on dashboard → see 4 KPI cards with current counts.

**Acceptance Scenarios**:

1. **Given** an admin logs in, **When** they reach the home page, **Then** they see a dashboard with 4 KPI cards.
2. **Given** an admin views the dashboard, **When** they look at the Employees card, **Then** they see total active employee count.
3. **Given** an admin views the dashboard, **When** they look at the Tasks card, **When** they look at the Tickets card, **Then** they see total open tickets count.
4. **Given** an admin views the dashboard, **When** they look at the Salaries card, **Then** they see total paid salaries for the current month.
5. **Given** an employee logs in, **When** they reach the home page, **Then** they see a simplified dashboard (own tasks, own tickets, own files count).

---

### User Story 2 - View Task Completion Chart (Priority: P1)

As an admin, I want to see a chart showing task completion trends over time so I can understand productivity patterns. The chart shows tasks created vs completed per week for the last 8 weeks.

**Why this priority**: Visual representation of task trends is essential for management insight — numbers alone don't show patterns.

**Independent Test**: Admin views dashboard → sees task chart with weekly bars for last 8 weeks.

**Acceptance Scenarios**:

1. **Given** an admin views the dashboard, **When** they look at the Task Completion section, **Then** they see a bar chart showing weekly task activity.
2. **Given** the task chart displays, **When** the admin examines it, **Then** they see two bars per week: tasks created and tasks completed.
3. **Given** the task chart displays, **When** the admin views the x-axis, **Then** they see the last 8 weeks labeled in Arabic date format.
4. **Given** an employee views their dashboard, **When** they look at the task section, **Then** they see only their own task completion trend.

---

### User Story 3 - View Ticket Trends Chart (Priority: P1)

As an admin, I want to see a chart showing ticket activity over time so I can understand support workload patterns. The chart shows tickets created vs closed per week for the last 8 weeks.

**Why this priority**: Ticket trends help admins identify support workload patterns and response efficiency.

**Independent Test**: Admin views dashboard → sees ticket chart with weekly bars for last 8 weeks.

**Acceptance Scenarios**:

1. **Given** an admin views the dashboard, **When** they look at the Ticket Trends section, **Then** they see a bar chart showing weekly ticket activity.
2. **Given** the ticket chart displays, **When** the admin examines it, **Then** they see two bars per week: tickets created and tickets closed.
3. **Given** the ticket chart displays, **When** the admin views the x-axis, **Then** they see the last 8 weeks labeled in Arabic date format.
4. **Given** an employee views their dashboard, **When** they look at the ticket section, **Then** they see only their own ticket activity (created tickets and admin replies).

---

### User Story 4 - Quick Navigation from Dashboard (Priority: P2)

As an admin, I want to click on KPI cards to navigate to the corresponding module so I can quickly access detailed views. Clicking "Employees" card takes me to the employees page, "Tasks" to tasks page, etc.

**Why this priority**: Navigation shortcuts improve workflow efficiency but the dashboard's primary value is the overview.

**Independent Test**: Admin clicks on Employees KPI card → navigates to employees list page.

**Acceptance Scenarios**:

1. **Given** an admin views the dashboard, **When** they click on the Employees card, **Then** they navigate to the employees list page.
2. **Given** an admin views the dashboard, **When** they click on the Tasks card, **Then** they navigate to the tasks list page.
3. **Given** an admin views the dashboard, **When** they click on the Tickets card, **Then** they navigate to the tickets list page.
4. **Given** an admin views the dashboard, **When** they click on the Salaries card, **Then** they navigate to the salaries list page.

---

### Edge Cases

- What happens when there is no data for a KPI (e.g., no employees yet)? → Show "0" with appropriate icon.
- What happens when there are no tasks or tickets for chart period? → Show empty chart with message "لا توجد بيانات" (No data).
- What happens when salary data doesn't exist for current month? → Show "0 جنيه" (0 EGP).
- What happens when an employee views the dashboard? → Show personalized dashboard with their own data only (tasks, tickets, files).
- What happens when a department has no employees? → Still shows in charts if they have tasks/tickets.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Dashboard MUST display 4 KPI cards: Active Employees, Total Tasks, Open Tickets, Monthly Salary Total.
- **FR-002**: Each KPI card MUST show: icon, label (Arabic), current value, and link to corresponding module.
- **FR-003**: Employee KPI MUST show count of active employees (status = 'active', deleted_at IS NULL).
- **FR-004**: Tasks KPI MUST show count of all tasks (excluding soft-deleted), with breakdown by status visible via hover tooltip (pending, in-progress, completed).
- **FR-005**: Tickets KPI MUST show count of open tickets (status NOT 'مغلق', deleted_at IS NULL).
- **FR-006**: Salary KPI MUST show total paid salaries for the current month (paid_at within current month, status = 'paid').
- **FR-007**: Dashboard MUST display Task Completion chart showing last 8 weeks of activity: tasks created vs tasks completed per week.
- **FR-008**: Dashboard MUST display Ticket Trends chart showing last 8 weeks of activity: tickets created vs tickets closed per week.
- **FR-009**: Charts MUST use Arabic labels and RTL layout where applicable.
- **FR-010**: Dashboard MUST be the default landing page after login (both admin and employee).
- **FR-011**: Employee dashboard MUST show personalized view: own tasks count, own tickets count, own files count, and own storage usage.
- **FR-012**: All KPI values MUST update in real-time (calculated on page load, no caching).
- **FR-013**: Dashboard MUST load within 2 seconds with all data and charts rendered.
- **FR-014**: All dashboard pages and messages MUST be in Arabic (RTL layout, Egyptian colloquial tone).
- **FR-015**: Charts MUST be responsive and readable on tablet-sized screens.
- **FR-016**: Dashboard MUST be admin-only for full view; employees see simplified personal dashboard.

### Key Entities

- **No new entities** — Dashboard aggregates data from existing entities: users, tasks, tickets, salaries, files.
- **Dashboard Metrics**: Computed values, not stored. Calculated on each page load from existing tables.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Dashboard loads with all KPIs and charts within 2 seconds.
- **SC-002**: All 4 KPI cards display accurate, real-time counts.
- **SC-003**: Charts display correctly with Arabic labels and RTL layout.
- **SC-004**: Employees see only their own data on their dashboard.
- **SC-005**: Clicking a KPI card navigates to the correct module within 1 second.
- **SC-006**: Dashboard functions correctly on tablet-sized viewports (768px+).

## Clarifications

### Session 2026-05-01

- Q: Which format for chart x-axis labels? → A: Relative labels (الأسبوع الماضي, منذ أسبوعين, etc.)
- Q: Tasks KPI breakdown interaction pattern? → A: Hover tooltip showing status breakdown

## Assumptions

- All previous phases (1-9) are complete and functional.
- Charts will be rendered using Chart.js (or similar) loaded via CDN — one-time exception for visualization library.
- Week periods are calculated as Monday–Sunday.
- "Current month" for salaries follows the Gregorian calendar.
- Employee dashboard shows minimal info: tasks assigned to them, their tickets, their files, storage used.
- KPI cards show single aggregate number; detailed breakdown is on the module page.
- Charts show 8 weeks of historical data; more detailed analytics are out of scope for MVP.
- Chart x-axis labels use relative Arabic format: "الأسبوع الماضي" (last week), "منذ أسبوعين" (2 weeks ago), etc.
- No real-time auto-refresh — user refreshes page for latest data.
- Dashboard is accessible from anywhere via the home icon in sidebar.