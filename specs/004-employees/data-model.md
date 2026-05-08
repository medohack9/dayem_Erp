# Data Model: Employee Management

**Date**: 2026-04-25
**Feature**: 004-employees

## Entity: Employee (extends `users` table)

The Employee entity is not a separate table — it uses the existing `users` table with additional columns added by migration `004_employees.sql`.

### Existing columns (from migrations 001-003)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | INT | PK, AUTO_INCREMENT | |
| name | VARCHAR(100) | NOT NULL | Full name in Arabic |
| email | VARCHAR(255) | NOT NULL, UNIQUE | |
| phone | VARCHAR(20) | NOT NULL, UNIQUE | Egyptian format (01XXXXXXXXX) |
| password | VARCHAR(255) | NOT NULL | bcrypt hash |
| role | ENUM('admin','employee') | NOT NULL, DEFAULT 'employee' | |
| status | ENUM('active','suspended','terminated') | NOT NULL, DEFAULT 'active' | |
| department_id | INT | NULL, FK → departments(id) | Added in migration 003 |
| failed_login_attempts | INT | NOT NULL, DEFAULT 0 | |
| locked_until | TIMESTAMP | NULL | |
| deleted_at | TIMESTAMP | NULL | Soft delete |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP | NULL, ON UPDATE CURRENT_TIMESTAMP | |

### New columns (migration 004)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| national_id | VARCHAR(14) | NULL, UNIQUE | Egyptian 14-digit national ID. NULL for admin users, required for employees. |
| birth_date | DATE | NULL | Required for employees |
| salary | DECIMAL(10,2) | NULL | Monthly salary in EGP. Required for employees. |
| hire_date | DATE | NULL | Defaults to creation date if not specified |
| employee_code | VARCHAR(20) | NULL, UNIQUE | Auto-generated format EMP-NNN. NULL for admin users. |

### Indexes (new)

| Index | Columns | Type | Purpose |
|-------|---------|------|---------|
| uk_users_national_id | national_id | UNIQUE | Enforce national ID uniqueness |
| uk_users_employee_code | employee_code | UNIQUE | Enforce employee code uniqueness |
| idx_users_department_id | department_id | NORMAL | Speed up department-based queries (may already exist from migration 003) |
| idx_users_status | status | NORMAL | Speed up status-based filtering (may already exist from migration 002) |

## State Diagram: Employee Status

```text
                  ┌──────────┐
              ┌──▶│  active  │◀─┐
              │   └──────────┘  │
    reactivate│        │        │reactivate
              │   suspend│        │
              │        ▼        │
              │   ┌──────────┐  │
              │   │suspended │──┘
              │   └──────────┘
              │        │
              │terminate│
              │        ▼
              │   ┌──────────┐
              └───│terminated│
                  └──────────┘
                       │
                  soft_delete│
                       ▼
                  ┌──────────┐
                  │ deleted  │  (deleted_at IS NOT NULL)
                  └──────────┘
```

## Validation Rules

### Create Employee

| Field | Rule | Error Message (Arabic) |
|-------|------|----------------------|
| name | Required, max 100 chars | اسم الموظف مطلوب |
| email | Required, valid email format, unique | البريد الإلكتروني مطلوب / صيغة البريد غير صحيحة / البريد الإلكتروني مستخدم بالفعل |
| phone | Required, matches /^01[0-9]{9}$/, unique | رقم الموبايل مطلوب / صيغة الرقم غير صحيحة / رقم الموبايل مستخدم بالفعل |
| national_id | Required for employees, exactly 14 digits, unique | الرقم القومي مطلوب / الرقم القومي يجب أن يكون 14 رقم / الرقم القومي مستخدم بالفعل |
| birth_date | Required for employees, valid date | تاريخ الميلاد مطلوب |
| salary | Required for employees, positive number | المرتب مطلوب / المرتب يجب أن يكون رقماً موجباً |
| department_id | Required for employees, must exist in active departments | القسم مطلوب / القسم غير موجود |
| hire_date | Optional, valid date, defaults to today | — |

### Edit Employee

Same validation as Create, but uniqueness checks exclude the current employee's own record.

### Delete Employee

- Cannot delete own account (session user ID check)
- If employee is a department manager (departments.manager_id), clear that field
- Soft delete only (set deleted_at timestamp)

## Relationships

```text
┌─────────────┐     ┌──────────────┐
│ departments │◀────│    users     │
│             │  N:1 │  (employees) │
│ manager_id ─│─────│     id       │
└─────────────┘     │              │
                    │ department_id │
                    └──────────────┘

Note: manager_id is a 1:1 relationship from departments → users
      department_id is a N:1 relationship from users → departments
      A user can be both an employee AND a department manager
```