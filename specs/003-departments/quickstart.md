# Quickstart: Departments Module

**Feature**: 003-departments
**Date**: 2026-04-25

## Prerequisites

- Phase 1 (Core System) and Phase 2 (Authentication) complete and running
- MySQL database accessible with `dayem_erp` database created
- XAMPP running with Apache DocumentRoot pointing to `public/`
- Admin account exists (`admin@dayem.com` / `Dayem@2026`)

## Step 1: Run Migration

```bash
mysql -u root dayem_erp < database/migrations/003_departments.sql
```

This creates:
- `departments` table with indexes
- `logs` table with indexes (generic, serves all future modules)
- ALTER on `users` table adding `department_id` column
- Seed data: 3 sample departments

## Step 2: Verify File Structure

Ensure these files exist:

```text
app/controllers/DepartmentController.php
app/models/DepartmentModel.php
app/models/LogModel.php
app/views/departments/index.php
app/views/departments/create.php
app/views/departments/edit.php
app/views/departments/delete-modal.php
public/js/departments.js
config/routes.php          (updated with department routes)
```

## Step 3: Verify Routes

Confirm `config/routes.php` includes department routes with proper auth/roles config.

## Step 4: Test as Admin

1. Log in as `admin@dayem.com` / `Dayem@2026`
2. Navigate to `/departments` — should see department list
3. Click "إضافة قسم" — create form should appear
4. Create a department with name "التسويق", no manager, default salary 4000
5. Edit the department — change name, assign a manager
6. Try to create a department with the same name — should show error
7. Delete a department that has no employees — should succeed immediately
8. Create another department, then try to delete it — modal should appear for reassignment

## Step 5: Test as Employee

1. Log in as an employee
2. Navigate to `/departments` — should see read-only list
3. Create/Edit/Delete buttons should NOT be visible
4. Directly accessing `/departments/create` should return 403

## Step 6: Test Edge Cases

1. Try deleting the last remaining active department — should show error
2. Search for a department that doesn't exist — should show "no results"
3. Submit create form with empty name — should show validation error
4. Assign a manager who is already managing another department — should show error

## Step 7: Verify in Database

```sql
SELECT * FROM departments WHERE deleted_at IS NULL;
SELECT * FROM logs WHERE entity_type = 'department';
SELECT id, name, department_id FROM users WHERE role = 'employee';
```

All department CRUD actions should appear in the `logs` table.