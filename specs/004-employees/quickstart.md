# Quickstart: Employee Management

**Date**: 2026-04-25
**Feature**: 004-employees

## Prerequisites

1. PHP 8.x with PDO MySQL extension
2. MySQL/MariaDB with InnoDB engine
3. XAMPP (or similar) with Apache pointing to `/public/`
4. Existing Dayem ERP installation with Phases 1-3 complete

## Setup Steps

### 1. Run Migration

```sql
-- Run migration 004_employees.sql against your database
mysql -u root dayem_erp < database/migrations/004_employees.sql
```

Or run via phpMyAdmin / MySQL CLI:

```sql
SOURCE database/migrations/004_employees.sql;
```

This adds the following columns to the `users` table:
- `national_id` VARCHAR(14) NULL UNIQUE
- `birth_date` DATE NULL
- `salary` DECIMAL(10,2) NULL
- `hire_date` DATE NULL
- `employee_code` VARCHAR(20) NULL UNIQUE

Plus indexes on `department_id` and `status` (idempotent).

### 2. Verify Routes

The following routes should be added to `config/routes.php`:

```php
'GET /employees' => ['EmployeeController', 'index', 'auth' => true, 'roles' => ['admin']],
'GET /employees/create' => ['EmployeeController', 'createForm', 'auth' => true, 'roles' => ['admin']],
'POST /employees' => ['EmployeeController', 'create', 'auth' => true, 'roles' => ['admin']],
'GET /employees/{id}/edit' => ['EmployeeController', 'editForm', 'auth' => true, 'roles' => ['admin']],
'PUT /employees/{id}' => ['EmployeeController', 'update', 'auth' => true, 'roles' => ['admin']],
'DELETE /employees/{id}' => ['EmployeeController', 'delete', 'auth' => true, 'roles' => ['admin']],
'GET /api/departments/{id}/default-salary' => ['DepartmentController', 'defaultSalary', 'auth' => true, 'roles' => ['admin']],
```

### 3. Verify Files Created

- `app/controllers/EmployeeController.php`
- `app/models/EmployeeModel.php`
- `app/views/employees/index.php`
- `app/views/employees/create.php`
- `app/views/employees/edit.php`
- `public/js/employees.js`

### 4. Test the Feature

1. Log in as admin (`admin@dayem.com` / `Dayem@2026`)
2. Navigate to "الموظفين" in the sidebar
3. Verify the employee list page loads with the table and search/filter controls
4. Click "إضافة موظف جديد" and fill in the form
5. Verify the new employee appears in the list with correct data
6. Edit the employee and verify changes persist
7. Try searching and filtering
8. Delete an employee and verify soft-delete behavior
9. Verify that suspended/terminated employees cannot log in

### 5. Graceful Fallback

- If migration fails, check that the `users` table exists from Phase 2
- If routes 404, verify `config/routes.php` has been updated
- If auth fails, verify `app/core/Auth.php` has the status check for login