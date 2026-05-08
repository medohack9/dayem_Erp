# Quickstart: Tasks Management

**Feature**: 005-tasks
**Date**: 2026-04-25

## Prerequisites

- Phases 1-4 complete and migrated (core, auth, departments, employees)
- Database `dayem_erp` exists with `users`, `departments`, `logs` tables
- XAMPP running, DocumentRoot pointing to `public/`

## Setup Steps

1. **Run migration**:
   ```sql
   -- Execute: database/migrations/006_tasks.sql
   -- via phpMyAdmin or mysql CLI
   ```

2. **Add routes** to `config/routes.php`:
   ```php
   'GET /tasks' => ['TaskController', 'index', 'auth' => true],
   'GET /tasks/create' => ['TaskController', 'createForm', 'auth' => true, 'roles' => ['admin']],
   'POST /tasks' => ['TaskController', 'create', 'auth' => true, 'roles' => ['admin']],
   'GET /tasks/{id}/edit' => ['TaskController', 'editForm', 'auth' => true, 'roles' => ['admin']],
   'PUT /tasks/{id}' => ['TaskController', 'update', 'auth' => true, 'roles' => ['admin']],
   'PUT /tasks/{id}/status' => ['TaskController', 'updateStatus', 'auth' => true],
   'DELETE /tasks/{id}' => ['TaskController', 'delete', 'auth' => true, 'roles' => ['admin']],
   'GET /api/tasks/active-employees' => ['TaskController', 'activeEmployees', 'auth' => true, 'roles' => ['admin']],
   ```

3. **Create files**:
   - `app/controllers/TaskController.php`
   - `app/models/TaskModel.php`
   - `app/views/tasks/index.php`
   - `app/views/tasks/create.php`
   - `app/views/tasks/edit.php`
   - `public/js/tasks.js`
   - `database/migrations/006_tasks.sql`

4. **Verify**: Login as admin → navigate to `/tasks` → create a task

## Key Files Reference

| File | Purpose |
|------|---------|
| `app/controllers/TaskController.php` | CRUD + status update, RBAC filtering |
| `app/models/TaskModel.php` | Query builder with search/filter/pagination |
| `app/views/tasks/index.php` | Task list with filters, pagination |
| `app/views/tasks/create.php` | Create form |
| `app/views/tasks/edit.php` | Edit form (admin), status update (employee) |
| `public/js/tasks.js` | AJAX handlers for CRUD + status |
| `database/migrations/006_tasks.sql` | Create tasks table + indexes |

## Verification Checklist

- [ ] Migration 006 executed successfully
- [ ] Admin can create task and assign to employee
- [ ] Admin can see all tasks; employee sees only own tasks
- [ ] Employee can update task status (forward only)
- [ ] Admin can change any task status including regression
- [ ] Task list filters work (status, priority, department, assignee)
- [ ] Overdue tasks show visual indicator
- [ ] Soft delete works (task disappears from active list)
- [ ] All mutations logged in audit log
- [ ] CSRF protection on all forms
- [ ] Arabic RTL throughout