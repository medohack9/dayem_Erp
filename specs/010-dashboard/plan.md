# Implementation Plan: Dashboard

**Branch**: `010-dashboard` | **Date**: 2026-05-01 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/010-dashboard/spec.md`

## Summary

Admin dashboard with 4 KPI cards (Employees, Tasks, Tickets, Salaries) and 2 trend charts (Task Completion, Ticket Trends) for the last 8 weeks. Employee dashboard shows personalized view with own data counts and storage usage. Uses Chart.js (CDN) for visualization.

## Technical Context

**Language/Version**: PHP 8.x (native, custom MVC)
**Primary Dependencies**: Chart.js (CDN - one-time exception for visualization)
**Storage**: MySQL (existing tables: users, tasks, tickets, salaries, files)
**Testing**: Manual testing via browser (no automated test framework)
**Target Platform**: Web browser (desktop/tablet, RTL Arabic)
**Project Type**: web-application
**Performance Goals**: Dashboard load < 2 seconds, all KPIs computed on page load
**Constraints**: No external JS libraries except Chart.js CDN, Arabic RTL only
**Scale/Scope**: Single company admin dashboard, ~50-200 employees

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Gate | Status | Notes |
|------|--------|-------|
| MVC Structure | ✅ PASS | Controller → Model → View separation |
| Single Entry Point | ✅ PASS | All routes via /public/index.php |
| Feature-First Development | ✅ PASS | Dashboard is a complete module |
| Arabic RTL | ✅ PASS | All labels in Arabic, RTL layout |
| Soft Delete Support | ✅ PASS | Queries filter deleted_at IS NULL |
| Access Control | ✅ PASS | Admin full view, employee personalized view |
| No Business Logic in Views | ✅ PASS | All calculations in Model layer |
| AJAX/JSON Communication | ✅ PASS | Charts load via AJAX API endpoints |
| CSRF Protection | ✅ PASS | All POST requests validated |

**Gate Status**: ✅ ALL GATES PASSED

## Project Structure

### Documentation (this feature)

```text
specs/010-dashboard/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output (no new entities)
├── quickstart.md        # Phase 1 output
└── tasks.md             # Phase 2 output (/speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── controllers/
│   └── HomeController.php     # Modify existing - add dashboard logic
├── models/
│   └── DashboardModel.php     # NEW - aggregate queries for KPIs/charts
├── views/
│   ├── home/
│   │   └── index.php          # Modify - dashboard view
│   └── components/
│       └── kpi-card.php       # NEW - reusable KPI card component
config/
└── routes.php                 # Add API routes for chart data
public/
└── js/
    └── dashboard.js           # NEW - Chart.js initialization
```

**Structure Decision**: Single project web application. Dashboard extends existing HomeController and adds new DashboardModel for aggregated queries. No backend/frontend split needed.

## Complexity Tracking

> No constitution violations - tracking not required.

## Implementation Phases

### Phase 0: Research

**Output**: research.md with decisions on Chart.js implementation, Arabic week label format, and chart data API design.

### Phase 1: Design & Contracts

**Output**: 
- data-model.md (documents data aggregation from existing entities)
- quickstart.md (test scenarios for dashboard verification)

### Phase 2: Tasks Generation

**Triggered by**: `/speckit.tasks` command (separate workflow)