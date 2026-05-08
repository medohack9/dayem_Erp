# Data Model: Departments Module

**Feature**: 003-departments
**Date**: 2026-04-25

## Entity Relationship

```text
users (existing)
  ├── 1:1 ── departments.manager_id  (a user manages at most one department)
  └── N:1 ── departments.id         (many employees belong to one department, via department_id on users)

departments (new)
  ├── manager_id ──► users.id       (nullable, one-to-one)
  └── (computed) employee_count     (COUNT of users where department_id = departments.id)

logs (new, generic)
  └── entity_type='department'      (polymorphic, supports all future modules)
```

## Table: departments

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Internal identifier |
| name | VARCHAR(100) | NOT NULL, UNIQUE | Department name (Arabic, case-insensitive unique) |
| manager_id | INT NULL | FOREIGN KEY → users(id) ON DELETE SET NULL | Manager reference (one-to-one) |
| default_salary | DECIMAL(10,2) NULL | DEFAULT NULL | Default salary for new employees in this department |
| deleted_at | TIMESTAMP NULL | DEFAULT NULL | Soft delete timestamp (NULL = active) |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP NULL | ON UPDATE CURRENT_TIMESTAMP | Last update timestamp |

**Indexes**:
- `idx_departments_name` ON (name) — supports search and unique enforcement
- `idx_departments_manager_id` ON (manager_id) — supports manager lookups and FK
- `idx_departments_deleted_at` ON (deleted_at) — supports filtering active departments

**Notes**:
- `name` uses `utf8mb4_unicode_ci` collation (inherited from table) enabling case-insensitive Arabic search and unique constraint
- `manager_id` is nullable; a department can have no manager
- `ON DELETE SET NULL` on manager_id: when a manager's user record is deleted, the department's manager_id is cleared (supports FR-015)
- `default_salary` uses DECIMAL(10,2) for EGP currency precision

## Table: logs

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | INT AUTO_INCREMENT | PRIMARY KEY | Internal identifier |
| user_id | INT NULL | FOREIGN KEY → users(id) ON DELETE SET NULL | User who performed the action |
| action | VARCHAR(50) | NOT NULL | Action type (e.g., 'create', 'update', 'delete') |
| entity_type | VARCHAR(50) | NOT NULL | Entity type (e.g., 'department', 'employee') |
| entity_id | INT | NOT NULL | ID of the affected entity |
| details | JSON NULL | DEFAULT NULL | Additional details (field changes, etc.) |
| ip_address | VARCHAR(45) NULL | DEFAULT NULL | Request IP address |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Log timestamp |

**Indexes**:
- `idx_logs_entity` ON (entity_type, entity_id) — supports entity-specific log queries
- `idx_logs_user_id` ON (user_id) — supports user activity queries
- `idx_logs_created_at` ON (created_at) — supports date-range queries and CSV export
- `idx_logs_action` ON (action) — supports action-type filtering

**Notes**:
- Generic log table serves all modules (department, employee, task, etc.) per constitution requirement
- `details` is JSON to flexibly store field-level change data (e.g., `{"field": "name", "old": "HR", "new": "Human Resources"}`)
- Supports Phase 9 (Logs) requirements without rework

## Migration: users table modification

Add `department_id` column to the existing `users` table:

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| department_id | INT NULL | FOREIGN KEY → departments(id) ON DELETE SET NULL, AFTER role | Employee's department |

**Notes**:
- Added via ALTER TABLE in migration 003
- ON DELETE SET NULL: if a department is soft-deleted, employee's department_id remains pointing to it (soft-deleted departments still exist in DB); when reassigning, department_id is updated to the new department before soft-delete
- Nullable because an employee may not yet be assigned to a department

## State Transitions

### Department Status (via soft delete)

```text
[Active] ──soft delete──► [Deleted (soft)]
                           └──excluded from active lists, preserved for audit
```

- Active: `deleted_at IS NULL`
- Deleted: `deleted_at` has a timestamp
- No "restore" action in MVP (admin can recreate if needed)

### Manager Assignment

```text
[No Manager] ──assign──► [Has Manager]
[Has Manager] ──reassign──► [New Manager] (old manager freed)
[Has Manager] ──clear──► [No Manager] (manager terminated/deleted)
```