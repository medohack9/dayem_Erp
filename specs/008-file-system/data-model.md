# Data Model: File System

**Feature**: 008-file-system
**Date**: 2026-05-01

## Entity: File

New table `files` to be created in migration `010_files.sql`.

### Fields

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT AUTO_INCREMENT | PRIMARY KEY | File ID |
| `user_id` | INT | NOT NULL | FK → users.id (uploader) |
| `name` | VARCHAR(200) | NOT NULL | Display name (user-defined) |
| `original_name` | VARCHAR(255) | NOT NULL | Original filename from upload |
| `stored_name` | VARCHAR(255) | NOT NULL, UNIQUE | Unique system-generated filename |
| `file_path` | VARCHAR(500) | NOT NULL | Relative path from storage root |
| `file_size` | INT | NOT NULL | File size in bytes |
| `file_type` | VARCHAR(100) | NOT NULL | MIME type |
| `priority` | VARCHAR(20) | NOT NULL, DEFAULT 'متوسطة' | منخفضة / متوسطة / عالية |
| `notes` | TEXT | NULL | Optional notes (max 1000 chars) |
| `deleted_at` | TIMESTAMP | NULL | Soft delete timestamp |
| `created_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Upload timestamp |
| `updated_at` | TIMESTAMP | NULL ON UPDATE | Last update time |

### Indexes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `uk_files_stored_name` | stored_name | UNIQUE | Ensure unique stored filenames |
| `idx_files_user_id` | user_id | INDEX | Filter files by user |
| `idx_files_priority` | priority | INDEX | Filter by priority |
| `idx_files_deleted_at` | deleted_at | INDEX | Soft delete filter |
| `idx_files_created_at` | created_at | INDEX | Sort by upload date |

### Foreign Keys

| Constraint | Columns | References | On Delete |
|-------------|---------|-----------|----------|
| `fk_files_user` | user_id | users(id) | CASCADE |

### Validation Rules

| Field | Rule |
|-------|------|
| name | Required, max 200 chars |
| file | Required, max 5MB (5,242,880 bytes) |
| file_type | Must be one of: image/jpeg, image/png, image/gif, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/zip, application/x-rar-compressed |
| priority | Required, one of: منخفضة, متوسطة, عالية |
| notes | Optional, max 1000 chars |
| user_id | Must reference active user |

---

## Entity: User Storage (Extended)

Add `storage_quota` column to existing `users` table via migration `010_files.sql`.

### New Field

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `storage_quota` | BIGINT | NULL, DEFAULT 524288000 | Storage quota in bytes (500MB default, NULL = unlimited for admins) |

### Notes

- Default value: 524288000 bytes = 500MB
- NULL means unlimited (for admins)
- Admins can modify quota for individual employees

---

## Computed Fields (not stored)

| Field | Logic |
|-------|-------|
| `storage_used` | `SELECT COALESCE(SUM(file_size), 0) FROM files WHERE user_id = ? AND deleted_at IS NULL` |
| `storage_remaining` | `storage_quota - storage_used` (or NULL if unlimited) |
| `quota_exceeded` | `storage_used > storage_quota` (false if quota is NULL/unlimited) |

---

## Entity Relationships

```
User 1:N File — a user uploads many files
File N:1 User — each file belongs to one user (uploader)
```

### Relationship to Existing Entities

```
File N:1 User (user_id) — each file uploaded by one user
```

---

## Migration Script

Migration `010_files.sql` will:

1. Create `files` table with all fields and indexes
2. Add `storage_quota` column to `users` table
3. Update existing admin users to have NULL storage_quota (unlimited)
4. Set default 500MB quota for all existing employees