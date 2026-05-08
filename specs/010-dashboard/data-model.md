# Data Model: Dashboard Feature

**Date**: 2026-05-01
**Branch**: 010-dashboard

## Overview

The Dashboard feature does **not introduce new database entities**. It aggregates data from existing tables to provide KPI metrics and trend visualizations.

---

## Data Sources (Existing Entities)

### 1. Users Table

**Relevant Fields for Dashboard**:
| Field | Type | Usage |
|-------|------|-------|
| id | INT | PK, referenced by other tables |
| role | ENUM('admin','employee') | Determines dashboard view |
| status | ENUM('active','inactive') | Active employee count filter |
| department_id | INT | FK to departments |
| deleted_at | TIMESTAMP | Soft delete filter |

**Dashboard Queries**:
- Active Employee Count: `COUNT(*) WHERE role='employee' AND status='active' AND deleted_at IS NULL`

---

### 2. Tasks Table

**Relevant Fields for Dashboard**:
| Field | Type | Usage |
|-------|------|-------|
| id | INT | PK |
| task_code | VARCHAR | Display code |
| title | VARCHAR | Display title |
| status | ENUM('قيد الانتظار','قيد التنفيذ','مكتمل') | Status breakdown |
| assigned_to | INT | FK to users (for employee dashboard) |
| created_at | TIMESTAMP | Week grouping for charts |
| deleted_at | TIMESTAMP | Soft delete filter |

**Status Values** (Arabic):
- قيد الانتظار (Pending)
- قيد التنفيذ (In Progress)
- مكتمل (Completed)

**Dashboard Queries**:
- Total Tasks Count: `COUNT(*) WHERE deleted_at IS NULL`
- Tasks by Status: `GROUP BY status`
- Weekly Created: `COUNT(*) WHERE deleted_at IS NULL AND created_at BETWEEN weekStart AND weekEnd`
- Weekly Completed: `COUNT(*) WHERE deleted_at IS NULL AND status='مكتمل' AND updated_at BETWEEN weekStart AND weekEnd`

---

### 3. Tickets Table

**Relevant Fields for Dashboard**:
| Field | Type | Usage |
|-------|------|-------|
| id | INT | PK |
| ticket_code | VARCHAR | Display code |
| title | VARCHAR | Display title |
| status | ENUM('جديد','قيد المراجعة','مغلق') | Open/Closed status |
| created_by | INT | FK to users (for employee dashboard) |
| created_at | TIMESTAMP | Week grouping for charts |
| deleted_at | TIMESTAMP | Soft delete filter |

**Status Values** (Arabic):
- جديد (New)
- قيد المراجعة (In Review)
- مغلق (Closed)

**Dashboard Queries**:
- Open Tickets Count: `COUNT(*) WHERE status != 'مغلق' AND deleted_at IS NULL`
- Weekly Created: `COUNT(*) WHERE deleted_at IS NULL AND created_at BETWEEN weekStart AND weekEnd`
- Weekly Closed: `COUNT(*) WHERE deleted_at IS NULL AND status='مغلق' AND updated_at BETWEEN weekStart AND weekEnd`

---

### 4. Salaries Table

**Relevant Fields for Dashboard**:
| Field | Type | Usage |
|-------|------|-------|
| id | INT | PK |
| user_id | INT | FK to users |
| net_amount | DECIMAL(10,2) | Salary amount |
| status | ENUM('unpaid','paid') | Payment status |
| paid_at | TIMESTAMP | Payment date (for current month filter) |
| month | INT | Month number (1-12) |
| year | INT | Year number |
| deleted_at | TIMESTAMP | Soft delete filter |

**Dashboard Queries**:
- Monthly Paid Total: `SUM(net_amount) WHERE status='paid' AND MONTH(paid_at)=CURRENT_MONTH AND YEAR(paid_at)=CURRENT_YEAR AND deleted_at IS NULL`

---

### 5. Files Table

**Relevant Fields for Dashboard** (Employee Dashboard only):
| Field | Type | Usage |
|-------|------|-------|
| id | INT | PK |
| user_id | INT | FK to users |
| file_size | INT | Bytes (for storage calculation) |
| deleted_at | TIMESTAMP | Soft delete filter |

**Dashboard Queries**:
- User's Files Count: `COUNT(*) WHERE user_id = ? AND deleted_at IS NULL`
- User's Storage Used: `SUM(file_size) WHERE user_id = ? AND deleted_at IS NULL`

---

## Computed Metrics (Not Stored)

### KPI Metrics

| Metric | Calculation | Refresh |
|--------|-------------|---------|
| Active Employees | COUNT from users table | On page load |
| Total Tasks | COUNT from tasks table | On page load |
| Open Tickets | COUNT from tickets table | On page load |
| Monthly Salaries Paid | SUM from salaries table | On page load |

### Chart Metrics (8 Weeks)

| Metric | Calculation | Refresh |
|--------|-------------|---------|
| Tasks Created per Week | COUNT grouped by week | On page load |
| Tasks Completed per Week | COUNT grouped by week | On page load |
| Tickets Created per Week | COUNT grouped by week | On page load |
| Tickets Closed per Week | COUNT grouped by week | On page load |

---

## Data Access Pattern

### DashboardModel (NEW)

**Methods**:
| Method | Parameters | Returns |
|--------|------------|---------|
| `getKpiStats()` | `$isAdmin`, `$userId` | Array with 4 KPI values |
| `getTaskStatusBreakdown()` | `$isAdmin`, `$userId` | Array of status => count |
| `getTasksChartData()` | `$isAdmin`, `$userId` | Array with labels, created[], completed[] |
| `getTicketsChartData()` | `$isAdmin`, `$userId` | Array with labels, created[], closed[] |
| `getEmployeeStats()` | `$userId` | Array with tasks, tickets, files, storage |

---

## Week Calculation Logic

```php
// Get the 8 most recent complete weeks (Monday-Sunday)
// Week labels are relative to current week

function getLast8Weeks(): array {
    $weeks = [];
    for ($i = 1; $i <= 8; $i++) {
        $weeks[] = [
            'start' => date('Y-m-d', strtotime("-{$i} monday", strtotime('this monday'))),
            'end' => date('Y-m-d', strtotime("-{$i} sunday", strtotime('this sunday'))),
            'label' => getArabicWeekLabel($i),
        ];
    }
    return array_reverse($weeks); // Oldest to newest
}
```

---

## No Schema Changes Required

This feature uses only existing tables. No migrations or schema modifications needed.