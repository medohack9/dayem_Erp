# Quickstart: Dashboard Feature

**Date**: 2026-05-01
**Branch**: 010-dashboard

## Test Scenarios

### Scenario 1: Admin Dashboard KPI Cards

**Preconditions**: 
- Logged in as admin
- System has employees, tasks, tickets, and paid salaries

**Steps**:
1. Navigate to home page (`/`)
2. Verify 4 KPI cards are displayed
3. Check each card shows correct count/value

**Expected Results**:
- Employees card: Shows count of active employees (status='active', deleted_at IS NULL)
- Tasks card: Shows total tasks count with hover tooltip for status breakdown
- Tickets card: Shows count of open tickets (status != 'مغلق')
- Salaries card: Shows total paid salaries for current month with "جنيه" suffix

---

### Scenario 2: Admin Task Completion Chart

**Preconditions**:
- Logged in as admin
- Tasks exist spanning the last 8 weeks

**Steps**:
1. Navigate to home page
2. Scroll to Task Completion section
3. Verify chart renders with 8 weeks of data

**Expected Results**:
- Bar chart displays 8 week groups
- X-axis shows relative Arabic labels (الأسبوع الماضي, منذ أسبوعين, etc.)
- Two bars per week: created (blue) and completed (green)
- RTL layout with bars right-to-left

---

### Scenario 3: Admin Ticket Trends Chart

**Preconditions**:
- Logged in as admin
- Tickets exist spanning the last 8 weeks

**Steps**:
1. Navigate to home page
2. Scroll to Ticket Trends section
3. Verify chart renders with 8 weeks of data

**Expected Results**:
- Bar chart displays 8 week groups
- X-axis shows relative Arabic labels
- Two bars per week: created (yellow) and closed (gray)
- RTL layout

---

### Scenario 4: Employee Dashboard

**Preconditions**:
- Logged in as employee (not admin)
- Employee has assigned tasks, created tickets, uploaded files

**Steps**:
1. Navigate to home page
2. Verify personalized dashboard displays

**Expected Results**:
- Shows 4 KPI cards: My Tasks, My Tickets, My Files, Storage Used
- Values reflect only the employee's own data
- No admin charts displayed (or simplified personal charts)

---

### Scenario 5: KPI Card Navigation

**Preconditions**:
- Logged in as admin
- Dashboard is displayed

**Steps**:
1. Click on Employees KPI card
2. Verify navigation to employees list
3. Return to dashboard
4. Click on Tasks KPI card
5. Verify navigation to tasks list

**Expected Results**:
- Employees card → `/employees` page
- Tasks card → `/tasks` page
- Tickets card → `/tickets` page
- Salaries card → `/salaries` page

---

### Scenario 6: Empty Data States

**Preconditions**:
- Logged in as admin
- No data in system (fresh install)

**Steps**:
1. Navigate to home page
2. Check KPI card displays
3. Check chart displays

**Expected Results**:
- All KPI cards show "0" with appropriate icons
- Charts display with message "لا توجد بيانات" (No data)
- Salaries card shows "0 جنيه"

---

### Scenario 7: Tasks KPI Hover Tooltip

**Preconditions**:
- Logged in as admin
- Tasks exist with different statuses

**Steps**:
1. Navigate to home page
2. Hover over Tasks KPI card
3. Verify tooltip appears

**Expected Results**:
- Tooltip shows breakdown by status:
  - قيد الانتظار: X
  - قيد التنفيذ: X
  - مكتمل: X

---

### Scenario 8: Dashboard Performance

**Preconditions**:
- System has 100+ employees, 500+ tasks, 200+ tickets

**Steps**:
1. Clear browser cache
2. Navigate to home page
3. Measure load time

**Expected Results**:
- Full page load (all KPIs + charts) completes within 2 seconds
- Charts render without flicker
- No JavaScript console errors

---

## API Endpoint Tests

### Test: GET /api/dashboard/stats (Admin)

**Request**: GET `/api/dashboard/stats`
**Expected Response**:
```json
{
  "employees": 15,
  "tasks": 42,
  "tasksBreakdown": {
    "قيد الانتظار": 10,
    "قيد التنفيذ": 20,
    "مكتمل": 12
  },
  "tickets": 8,
  "salaries": 125000.00
}
```

---

### Test: GET /api/dashboard/tasks-chart

**Request**: GET `/api/dashboard/tasks-chart`
**Expected Response**:
```json
{
  "labels": ["منذ 8 أسابيع", "منذ 7 أسابيع", "منذ 6 أسابيع", "منذ 5 أسابيع", "منذ 4 أسابيع", "منذ 3 أسابيع", "منذ أسبوعين", "الأسبوع الماضي"],
  "created": [5, 8, 3, 12, 7, 9, 4, 6],
  "completed": [4, 6, 5, 10, 8, 7, 3, 5]
}
```

---

### Test: GET /api/dashboard/stats (Employee)

**Request**: GET `/api/dashboard/stats` (as employee user ID 5)
**Expected Response**:
```json
{
  "myTasks": 8,
  "myTickets": 3,
  "myFiles": 12,
  "storageUsed": 2048576,
  "storageUsedFormatted": "2.0 MB"
}
```

---

## Manual Test Checklist

- [ ] Admin sees 4 KPI cards with correct values
- [ ] Admin sees Task Completion chart (8 weeks)
- [ ] Admin sees Ticket Trends chart (8 weeks)
- [ ] KPI cards are clickable and navigate to correct pages
- [ ] Tasks KPI shows status breakdown on hover
- [ ] Employee sees personalized dashboard
- [ ] Employee dashboard shows own data only
- [ ] Empty states display correctly (0 values, "لا توجد بيانات")
- [ ] Charts render correctly in RTL Arabic
- [ ] Dashboard loads within 2 seconds
- [ ] All labels are in Arabic (Egyptian colloquial)