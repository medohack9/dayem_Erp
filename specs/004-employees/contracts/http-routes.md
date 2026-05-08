# HTTP Route Contracts: Employee Management

**Date**: 2026-04-25
**Feature**: 004-employees

## Routes

### Employee List (View)

```
GET /employees
```

**Auth**: Required (admin only)
**Role**: admin
**Query Parameters**:
- `page` (int, optional, default: 1) — Page number
- `search` (string, optional) — Search by name or employee code
- `department_id` (int, optional) — Filter by department
- `status` (string, optional, default: "active") — Filter by status: active, suspended, terminated, or "all"

**Response**: HTML view (employees/index)
**Data passed to view**:
- `employees` (array) — Paginated employee records with department name
- `total` (int) — Total matching records
- `page` (int) — Current page
- `totalPages` (int) — Total pages
- `search` (string) — Current search term
- `departmentId` (int|null) — Current department filter
- `statusFilter` (string) — Current status filter
- `departments` (array) — Active departments for filter dropdown
- `perPage` (int) — Items per page (15)

---

### Employee Create Form (View)

```
GET /employees/create
```

**Auth**: Required (admin only)
**Role**: admin
**Response**: HTML view (employees/create)
**Data passed to view**:
- `departments` (array) — Active departments for dropdown
- `pageScripts` (array) — ['/js/employees.js']

---

### Employee Create (API)

```
POST /employees
```

**Auth**: Required (admin only)
**Role**: admin
**Content-Type**: application/json
**CSRF**: `_csrf_token` field required

**Request Body**:
```json
{
  "_csrf_token": "...",
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "phone": "01012345678",
  "national_id": "29901011234567",
  "birth_date": "1990-01-01",
  "salary": 15000,
  "department_id": 1,
  "hire_date": "2024-01-15"
}
```

**Success Response** (201):
```json
{
  "success": true,
  "message": "تم إضافة الموظف بنجاح",
  "employee": {
    "id": 5,
    "name": "أحمد محمد",
    "employee_code": "EMP-001",
    "email": "ahmed@example.com",
    "status": "active",
    "department_id": 1
  }
}
```

**Validation Error Response** (422):
```json
{
  "success": false,
  "errors": {
    "name": "اسم الموظف مطلوب",
    "email": "البريد الإلكتروني مستخدم بالفعل"
  }
}
```

---

### Employee Edit Form (View)

```
GET /employees/{id}/edit
```

**Auth**: Required (admin only)
**Role**: admin
**URL Parameters**:
- `id` (int, required) — Employee ID

**Response**: HTML view (employees/edit) or 404
**Data passed to view**:
- `employee` (array) — Employee record with department info
- `departments` (array) — Active departments for dropdown
- `pageScripts` (array) — ['/js/employees.js']

---

### Employee Update (API)

```
PUT /employees/{id}
```

**Auth**: Required (admin only)
**Role**: admin
**Content-Type**: application/json
**CSRF**: `_csrf_token` field required

**URL Parameters**:
- `id` (int, required) — Employee ID

**Request Body**:
```json
{
  "_csrf_token": "...",
  "name": "أحمد محمد علي",
  "email": "ahmed@example.com",
  "phone": "01012345678",
  "national_id": "29901011234567",
  "birth_date": "1990-01-01",
  "salary": 17000,
  "department_id": 2,
  "hire_date": "2024-01-15",
  "status": "active"
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "تم تحديث بيانات الموظف بنجاح",
  "employee": { ... }
}
```

**Not Found Response** (404):
```json
{
  "success": false,
  "message": "الموظف غير موجود"
}
```

**Validation Error Response** (422):
```json
{
  "success": false,
  "errors": { ... }
}
```

---

### Employee Delete (API)

```
DELETE /employees/{id}
```

**Auth**: Required (admin only)
**Role**: admin
**Content-Type**: application/json
**CSRF**: `_csrf_token` field required

**URL Parameters**:
- `id` (int, required) — Employee ID

**Request Body**:
```json
{
  "_csrf_token": "..."
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "تم حذف الموظف بنجاح"
}
```

**Self-Delete Error Response** (403):
```json
{
  "success": false,
  "message": "لا يمكنك حذف حسابك الخاص"
}
```

**Not Found Response** (404):
```json
{
  "success": false,
  "message": "الموظف غير موجود"
}
```

---

### API: Active Departments List

```
GET /api/departments/active
```

**Auth**: Required (any authenticated user)
**Response** (200):
```json
{
  "success": true,
  "departments": [
    { "id": 1, "name": "الموارد البشرية" },
    { "id": 2, "name": "تقنية المعلومات" }
  ]
}
```

**Note**: This endpoint already exists from the departments module. Listed here for reference.

---

### API: Department Default Salary

```
GET /api/departments/{id}/default-salary
```

**Auth**: Required (admin only)
**Role**: admin
**URL Parameters**:
- `id` (int, required) — Department ID

**Response** (200):
```json
{
  "success": true,
  "default_salary": 5000.00
}
```

**Not Found Response** (404):
```json
{
  "success": false,
  "message": "القسم غير موجود"
}
```

---

### API: Available Managers

```
GET /api/employees/available-managers
```

**Auth**: Required (admin only)
**Note**: This endpoint already exists from the departments module. Listed here for reference.