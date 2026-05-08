# HTTP Route Contracts: File System

**Feature**: 008-file-system
**Date**: 2026-05-01

All routes follow existing patterns: CSRF token required on all mutations, JSON request/response for CRUD, form rendering for pages.

## Page Routes

### GET /files
- **Auth**: Required (admin or employee)
- **Controller**: `FileController::index`
- **Action**: Render file list page
- **Query Params**: `page`, `search`, `priority`, `date_from`, `date_to`
- **Behavior**:
  - Admin: Shows all files from all employees, additional filter by employee name, shows each employee's quota usage
  - Employee: Shows only files uploaded by `Auth::id()`, shows own quota usage indicator
- **Response**: HTML page with files, pagination, filters, storage usage indicator

### GET /files/create
- **Auth**: Required (admin or employee)
- **Controller**: `FileController::createForm`
- **Action**: Render upload file form
- **Response**: HTML form with name, priority dropdown, notes textarea, file upload area

### GET /files/{id}/edit
- **Auth**: Required (admin or file owner)
- **Controller**: `FileController::editForm`
- **Action**: Render edit metadata form
- **Behavior**:
  - Admin: Can edit any file's metadata
  - Employee: Can only edit their own files
- **Response**: HTML form with name, priority, notes (file itself cannot be changed)

## API Routes

### POST /files
- **Auth**: Required (admin or employee)
- **Controller**: `FileController::create`
- **Request Body** (multipart/form-data):
  - `_csrf_token`: string (required)
  - `file`: file (required, max 5MB)
  - `name`: string (required, max 200)
  - `priority`: string (required, one of: منخفضة, متوسطة, عالية)
  - `notes`: string (optional, max 1000)
- **Behavior**:
  - Validates file type against allowed MIME types
  - Validates file size ≤ 5MB
  - Checks user storage quota before upload
  - Generates unique stored filename (UUID + timestamp)
  - Stores file in `storage/uploads/files/`
- **Response 200**:
  ```json
  {
    "success": true,
    "message": "تم رفع الملف بنجاح",
    "file": { "id": 1, "name": "...", "storage_used": "320MB", "storage_quota": "500MB" }
  }
  ```
- **Response 422**: Validation errors
  ```json
  {
    "success": false,
    "errors": {
      "file": "حجم الملف يتجاوز الحد المسموح (5 ميجابايت)",
      "name": "اسم الملف مطلوب"
    }
  }
  ```
- **Response 413**: Quota exceeded
  ```json
  {
    "success": false,
    "message": "مساحة التخزين غير كافية. المستخدم: 498MB / 500MB"
  }
  ```
- **Response 403**: `{ "success": false, "message": "غير مصرح بهذا الإجراء" }`

### PUT /files/{id}
- **Auth**: Required (admin or file owner)
- **Controller**: `FileController::update`
- **Request Body** (JSON):
  ```json
  {
    "_csrf_token": "string",
    "name": "string (max 200)",
    "priority": "منخفضة|متوسطة|عالية",
    "notes": "string (optional, max 1000)"
  }
  ```
- **Behavior**:
  - Updates metadata only; file content cannot be changed
  - Admin can edit any file
  - Employee can only edit their own files
- **Response 200**: `{ "success": true, "message": "تم تحديث الملف بنجاح" }`
- **Response 403**: Employee attempting to edit another user's file
- **Response 404**: File not found

### DELETE /files/{id}
- **Auth**: Required (admin or file owner)
- **Controller**: `FileController::delete`
- **Request Body** (JSON):
  ```json
  { "_csrf_token": "string" }
  ```
- **Behavior**:
  - Soft delete (sets deleted_at timestamp)
  - File remains on disk but is no longer accessible
  - Admin can delete any file
  - Employee can only delete their own files
  - Frees up storage quota
- **Response 200**: `{ "success": true, "message": "تم حذف الملف بنجاح", "storage_freed": "2.5MB" }`
- **Response 403**: Employee attempting to delete another user's file
- **Response 404**: File not found

### GET /files/{id}/download
- **Auth**: Required (admin or file owner)
- **Controller**: `FileController::download`
- **Action**: Serve file from secure storage with permission check
- **Behavior**:
  - Admin can download any file
  - Employee can only download their own files
  - Files served from `storage/uploads/files/` directory
  - Serves with original filename via Content-Disposition header
  - Logs download action in audit log
- **Response**: File download with appropriate Content-Type and Content-Disposition headers
- **Response 403**: Unauthorized access to another employee's file
- **Response 404**: File not found

### GET /api/files/storage
- **Auth**: Required
- **Controller**: `FileController::storageInfo`
- **Action**: Return current storage usage for logged-in user
- **Response**:
  ```json
  {
    "success": true,
    "storage_used": 335544320,
    "storage_quota": 524288000,
    "storage_used_formatted": "320MB",
    "storage_quota_formatted": "500MB",
    "percentage": 64,
    "unlimited": false
  }
  ```
  For admin (unlimited):
  ```json
  {
    "success": true,
    "storage_used": 10485760,
    "storage_quota": null,
    "storage_used_formatted": "10MB",
    "storage_quota_formatted": "غير محدود",
    "percentage": null,
    "unlimited": true
  }
  ```

### PUT /api/files/user/{userId}/quota
- **Auth**: Required, admin only
- **Controller**: `FileController::updateQuota`
- **Request Body** (JSON):
  ```json
  {
    "_csrf_token": "string",
    "storage_quota": 1073741824
  }
  ```
- **Action**: Update storage quota for a specific employee
- **Behavior**:
  - Admin can increase or decrease employee quota
  - Setting to null gives unlimited storage
  - Cannot set below current usage (return error)
- **Response 200**: `{ "success": true, "message": "تم تحديث مساحة التخزين بنجاح" }`
- **Response 400**: `{ "success": false, "message": "لا يمكن تعيين مساحة أقل من المستخدم حالياً" }`
- **Response 403**: Non-admin attempting quota change