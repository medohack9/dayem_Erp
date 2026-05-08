# HTTP Route Contracts: Tasks Management

**Feature**: 005-tasks
**Date**: 2026-04-25

All routes follow existing patterns: CSRF token required on all mutations, JSON request/response for CRUD, form rendering for pages.

## Page Routes

### GET /tasks
- **Auth**: Required (admin or employee)
- **Controller**: `TaskController::index`
- **Action**: Render task list page
- **Query Params**: `page`, `search`, `status`, `priority`, `department_id`, `assigned_to`
- **Behavior**:
  - Admin: Shows all tasks, all filters available
  - Employee: Shows only tasks assigned to `Auth::id()`, limited filters
- **Response**: HTML page with tasks, pagination, filters

### GET /tasks/create
- **Auth**: Required, admin only
- **Controller**: `TaskController::createForm`
- **Action**: Render create task form
- **Response**: HTML form with employee dropdown, department dropdown

### GET /tasks/{id}/edit
- **Auth**: Required, admin only
- **Controller**: `TaskController::editForm`
- **Action**: Render edit task form
- **Response**: HTML form pre-filled with task data

## API Routes

### POST /tasks
- **Auth**: Required, admin only
- **Controller**: `TaskController::create`
- **Request Body** (JSON):
  ```json
  {
    "_csrf_token": "string",
    "title": "string (required, max 200)",
    "description": "string (optional, max 2000)",
    "priority": "عاجل|متوسط|منخفض",
    "due_date": "YYYY-MM-DD|null",
    "assigned_to": "int (required, employee user id)",
    "department_id": "int|null"
  }
  ```
- **Response 200**:
  ```json
  {
    "success": true,
    "message": "تم إنشاء المهمة بنجاح",
    "task": { ... }
  }
  ```
- **Response 422**:
  ```json
  {
    "success": false,
    "errors": {
      "title": "عنوان المهمة مطلوب",
      "assigned_to": "يجب اختيار موظف"
    }
  }
  ```
- **Response 403**: `{ "success": false, "message": "غير مصرح بهذا الإجراء" }`

### PUT /tasks/{id}
- **Auth**: Required, admin only (for full edit)
- **Controller**: `TaskController::update`
- **Request Body** (JSON):
  ```json
  {
    "_csrf_token": "string",
    "title": "string",
    "description": "string|null",
    "priority": "عاجل|متوسط|منخفض",
    "status": "جديد|قيد التنفيذ|مكتمل",
    "due_date": "YYYY-MM-DD|null",
    "assigned_to": "int",
    "department_id": "int|null"
  }
  ```
- **Response 200**: `{ "success": true, "message": "تم تحديث المهمة بنجاح", "task": { ... } }`
- **Response 422**: Validation errors
- **Response 404**: Task not found

### PUT /tasks/{id}/status
- **Auth**: Required (admin or assigned employee)
- **Controller**: `TaskController::updateStatus`
- **Request Body** (JSON):
  ```json
  {
    "_csrf_token": "string",
    "status": "قيد التنفيذ|مكتمل"
  }
  ```
- **Behavior**:
  - Employee: Can only change status of their own tasks, and only forward (جديد → قيد التنفيذ → مكتمل)
  - Admin: Can set any status on any task
  - Setting status to مكتمل sets `completed_at` to NOW()
  - Changing status away from مكتمل clears `completed_at` to NULL
- **Response 200**: `{ "success": true, "message": "تم تحديث حالة المهمة بنجاح" }`
- **Response 403**: `{ "success": false, "message": "غير مصرح بتعديل هذه المهمة" }` (employee trying to edit another's task or regress status)

### DELETE /tasks/{id}
- **Auth**: Required, admin only
- **Controller**: `TaskController::delete`
- **Request Body** (JSON):
  ```json
  { "_csrf_token": "string" }
  ```
- **Response 200**: `{ "success": true, "message": "تم حذف المهمة بنجاح" }`
- **Response 404**: Task not found

### GET /api/tasks/active
- **Auth**: Required
- **Controller**: `TaskController::activeList`
- **Action**: Return list of active employees for assignment dropdown
- **Response**:
  ```json
  {
    "success": true,
    "employees": [
      { "id": 1, "name": "أحمد محمد", "employee_code": "EMP-001" }
    ]
  }
  ```
  (Admin sees all active employees; not needed for employee role)

### GET /api/departments/active
- **Auth**: Required (already exists from departments module)
- **Reuse**: Existing `DepartmentController::activeList` endpoint