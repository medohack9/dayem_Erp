# HTTP Route Contracts: Departments Module

**Feature**: 003-departments
**Date**: 2026-04-25

## Authentication & Authorization

All department routes require authentication (`auth => true`).
Management routes (create, edit, delete) require admin role (`roles => ['admin']`).
View routes are accessible to all authenticated users.

## Routes

### US1: Create Department

| Method | Path | Controller | Action | Auth | Roles | Request | Response |
|--------|------|------------|--------|------|-------|---------|----------|
| GET | /departments/create | DepartmentController | createForm | true | ['admin'] | — | HTML form |
| POST | /departments | DepartmentController | create | true | ['admin'] | JSON: `{name, manager_id, default_salary, _csrf_token}` | JSON: `{success, message, department?}` |

**Validations**:
- `name`: required, max 100 chars, unique (case-insensitive)
- `manager_id`: optional, must exist in users where status='active', must not already manage another department
- `default_salary`: optional, numeric, min 0, max 99999999.99

**Success Response** (201):
```json
{
  "success": true,
  "message": "تم إنشاء القسم بنجاح",
  "department": {"id": 1, "name": "الموارد البشرية", "manager_id": 5, "default_salary": 5000.00}
}
```

**Error Responses**:
- 422: Validation errors `{success: false, errors: {name: "اسم القسم مطلوب"}}`
- 403: Non-admin access
- 409: Duplicate name `{success: false, message: "اسم القسم موجود بالفعل"}`

---

### US2: View Department List

| Method | Path | Controller | Action | Auth | Roles | Request | Response |
|--------|------|------------|--------|------|-------|---------|----------|
| GET | /departments | DepartmentController | index | true | — | Query: `?page=1&search=hr` | HTML page |

**Query Parameters**:
- `page`: page number (default 1)
- `search`: search term for department name (optional)

**Response**: HTML page with paginated department list. Admin sees action buttons (create, edit, delete). Employee sees read-only list.

---

### US3: Edit Department

| Method | Path | Controller | Action | Auth | Roles | Request | Response |
|--------|------|------------|--------|------|-------|---------|----------|
| GET | /departments/{id}/edit | DepartmentController | editForm | true | ['admin'] | — | HTML form (pre-filled) |
| PUT | /departments/{id} | DepartmentController | update | true | ['admin'] | JSON: `{name, manager_id, default_salary, _csrf_token}` | JSON: `{success, message, department?}` |

**Validations**: Same as create, plus:
- `{id}` must exist and not be soft-deleted
- `name` uniqueness excludes the current department (can keep same name)

**Success Response** (200):
```json
{
  "success": true,
  "message": "تم تحديث القسم بنجاح",
  "department": {"id": 1, "name": "الموارد البشرية", "manager_id": 5, "default_salary": 5500.00}
}
```

---

### US4: Delete Department (with Reassignment)

| Method | Path | Controller | Action | Auth | Roles | Request | Response |
|--------|------|------------|--------|------|-------|---------|----------|
| DELETE | /departments/{id} | DepartmentController | delete | true | ['admin'] | JSON: `{reassign_to, _csrf_token}` | JSON: `{success, message}` |

**Validations**:
- `{id}` must exist and not be soft-deleted
- Cannot delete the last active department
- `reassign_to` is required if the department has active employees; must be a different active department
- `reassign_to` is optional (ignored) if the department has no employees

**Success Response** (200):
```json
{
  "success": true,
  "message": "تم حذف القسم وإعادة تعيين الموظفين بنجاح"
}
```

**Error Responses**:
- 403: Non-admin or attempted deletion of last department
- 422: Missing reassign_to when department has employees
- 404: Department not found or already deleted

---

### US5: Assign Manager (handled within Create/Edit)

No separate route. Manager assignment is part of create and edit via the `manager_id` field.

---

### US6: Default Salary (handled within Create/Edit)

No separate route. Default salary is set via `default_salary` field in create and edit.

---

### Supporting Routes

| Method | Path | Controller | Action | Auth | Roles | Purpose |
|--------|------|------------|--------|------|-------|---------|
| GET | /api/departments/active | DepartmentController | activeList | true | — | JSON list of active departments (for dropdowns, reassignment modal) |
| GET | /api/employees/available-managers | DepartmentController | availableManagers | true | ['admin'] | JSON list of employees eligible to be managers |

**Notes**:
- `activeList` returns all active departments as JSON: `[{id, name}]` — used for reassignment dropdown and employee form department selector
- `availableManagers` returns employees who can be assigned as manager (active, not already managing another department, or currently managing this department): `[{id, name}]`

## Route Configuration (routes.php)

```php
'DELETE /departments/{id}' => ['DepartmentController', 'delete', 'auth' => true, 'roles' => ['admin']],
'GET /departments' => ['DepartmentController', 'index', 'auth' => true],
'GET /departments/create' => ['DepartmentController', 'createForm', 'auth' => true, 'roles' => ['admin']],
'GET /departments/{id}/edit' => ['DepartmentController', 'editForm', 'auth' => true, 'roles' => ['admin']],
'GET /api/departments/active' => ['DepartmentController', 'activeList', 'auth' => true],
'GET /api/employees/available-managers' => ['DepartmentController', 'availableManagers', 'auth' => true, 'roles' => ['admin']],
'POST /departments' => ['DepartmentController', 'create', 'auth' => true, 'roles' => ['admin']],
'PUT /departments/{id}' => ['DepartmentController', 'update', 'auth' => true, 'roles' => ['admin']],
```