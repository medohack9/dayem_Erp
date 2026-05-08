# Quickstart: File System

**Feature**: 008-file-system
**Date**: 2026-05-01

## Prerequisites

- Phases 1-7 complete and migrated (core, auth, departments, employees, tasks, tickets, salary)
- Database `dayem_erp` exists with `users`, `departments`, `logs` tables
- XAMPP running, DocumentRoot pointing to `public/`

## Setup Steps

1. **Run migration**:
   ```sql
   -- Execute: database/migrations/010_files.sql
   -- via phpMyAdmin or mysql CLI
   ```

2. **Add routes** to `config/routes.php`:
   ```php
   'GET /files' => ['FileController', 'index', 'auth' => true],
   'GET /files/create' => ['FileController', 'createForm', 'auth' => true],
   'POST /files' => ['FileController', 'create', 'auth' => true],
   'GET /files/{id}/edit' => ['FileController', 'editForm', 'auth' => true],
   'PUT /files/{id}' => ['FileController', 'update', 'auth' => true],
   'DELETE /files/{id}' => ['FileController', 'delete', 'auth' => true],
   'GET /files/{id}/download' => ['FileController', 'download', 'auth' => true],
   'GET /api/files/storage' => ['FileController', 'storageInfo', 'auth' => true],
   'PUT /api/files/user/{userId}/quota' => ['FileController', 'updateQuota', 'auth' => true, 'roles' => ['admin']],
   ```

3. **Create files**:
   - `app/controllers/FileController.php`
   - `app/models/FileModel.php`
   - `app/views/files/index.php`
   - `app/views/files/create.php`
   - `app/views/files/edit.php`
   - `public/js/files.js`
   - `database/migrations/010_files.sql`

4. **Create upload directory**:
   ```bash
   mkdir -p storage/uploads/files
   ```

5. **Verify**: Login as employee → navigate to `/files` → upload a file

## Key Files Reference

| File | Purpose |
|------|---------|
| `app/controllers/FileController.php` | CRUD + download + quota management + RBAC |
| `app/models/FileModel.php` | File queries with search/filter/pagination + storage calculations |
| `app/views/files/index.php` | File list with filters, pagination, storage usage indicator |
| `app/views/files/create.php` | Upload form with file, name, priority, notes |
| `app/views/files/edit.php` | Edit metadata form (name, priority, notes only) |
| `public/js/files.js` | AJAX handlers for upload, edit, delete, download |
| `database/migrations/010_files.sql` | Create files table, add storage_quota to users |

## Verification Checklist

- [ ] Migration 010 executed successfully
- [ ] Employee can upload file with name, priority, notes
- [ ] File type validation rejects unsupported formats
- [ ] File size validation rejects files > 5MB
- [ ] Storage quota enforced (upload rejected when quota exceeded)
- [ ] Storage usage indicator shows correct values
- [ ] Employee sees only their own files
- [ ] Admin sees all files from all employees
- [ ] Admin can filter by employee name
- [ ] Employee can edit metadata on their own files
- [ ] Admin can edit metadata on any file
- [ ] Employee can delete their own files
- [ ] Admin can delete any file
- [ ] Soft delete works (file disappears from active list)
- [ ] Download serves file with original filename
- [ ] Employee cannot download another employee's file (403)
- [ ] Admin can modify employee storage quota
- [ ] Cannot set quota below current usage
- [ ] Duplicate file names allowed per user
- [ ] All mutations logged in audit log
- [ ] CSRF protection on all forms
- [ ] Arabic RTL throughout