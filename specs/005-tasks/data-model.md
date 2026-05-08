# Data Model: Tasks Management

**Feature**: 005-tasks
**Date**: 2026-04-25

## Entity: Task

New table `tasks` to be created in migration `006_tasks.sql`.

### Fields

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT AUTO_INCREMENT | PRIMARY KEY | Task ID |
| `task_code` | VARCHAR(20) | NOT NULL, UNIQUE | Human-readable code (e.g., TSK-001) |
| `title` | VARCHAR(200) | NOT NULL | Task title |
| `description` | TEXT | NULL | Task description (max 2000 chars enforced in app) |
| `priority` | VARCHAR(20) | NOT NULL, DEFAULT 'متوسط' | عاجل / متوسط / منخفض |
| `status` | VARCHAR(20) | NOT NULL, DEFAULT 'جديد' | جديد / قيد التنفيذ / مكتمل |
| `assigned_to` | INT | NOT NULL | FK → users.id (employee) |
| `department_id` | INT | NULL | FK → departments.id (optional) |
| `due_date` | DATE | NULL | Optional due date |
| `completed_at` | TIMESTAMP | NULL | Set when status becomes مكتمل |
| `created_by` | INT | NOT NULL | FK → users.id (admin who created) |
| `deleted_at` | TIMESTAMP | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Creation time |
| `updated_at` | TIMESTAMP | NULL ON UPDATE | Last update time |

### Indexes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `uk_tasks_task_code` | task_code | UNIQUE | Ensure unique task codes |
| `idx_tasks_assigned_to` | assigned_to | INDEX | Filter tasks by employee |
| `idx_tasks_department_id` | department_id | INDEX | Filter tasks by department |
| `idx_tasks_status` | status | INDEX | Filter by status |
| `idx_tasks_priority` | priority | INDEX | Filter by priority |
| `idx_tasks_deleted_at` | deleted_at | INDEX | Soft delete filter |
| `idx_tasks_created_by` | created_by | INDEX | Find tasks created by admin |

### Foreign Keys

| Constraint | Columns | References | On Delete |
|-------------|---------|-----------|----------|
| `fk_tasks_assigned_to` | assigned_to | users(id) | CASCADE |
| `fk_tasks_department` | department_id | departments(id) | SET NULL |
| `fk_tasks_created_by` | created_by | users(id) | SET NULL |

### Validation Rules

| Field | Rule |
|-------|------|
| title | Required, max 200 chars |
| description | Optional, max 2000 chars |
| priority | Required, one of: عاجل, متوسط, منخفض |
| status | Default جديد on create; one of: جديد, قيد التنفيذ, مكتمل |
| assigned_to | Required, must reference active employee (role=employee, status=active) |
| department_id | Optional, must reference active department if provided |
| due_date | Optional, must be a valid date |

### State Transitions

```
جديد (New)
  ↓ employee or admin
قيد التنفيذ (In Progress)
  ↓ employee or admin
مكتمل (Completed)

Admin can set any status directly.
Employee can only advance: جديد → قيد التنفيذ → مكتمل.
Admin can regress: مكتمل → قيد التنفيذ (clears completed_at).
```

### Computed Fields (not stored)

| Field | Logic |
|-------|-------|
| `is_overdue` | `due_date IS NOT NULL AND due_date < CURDATE() AND status != 'مكتمل'` |
| `employee_status` | From JOIN on users table (u.status) for alert display |

## Entity: Task (relationships to existing entities)

```
Task N:1 User (assigned_to)    — each task assigned to one employee
Task N:1 Department (optional) — each task optionally in one department  
Task N:1 User (created_by)    — each task created by one admin
```