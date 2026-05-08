# Data Model: Log System

**Feature**: 009-log-system
**Date**: 2026-05-01

## Entity: Log (Existing — No Changes)

The `logs` table already exists from Phase 1. No migrations needed.

### Existing Fields

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INT AUTO_INCREMENT | PRIMARY KEY | Log ID |
| `user_id` | INT | NULL | FK → users.id (who performed action) |
| `action` | VARCHAR(50) | NOT NULL | Action type (create/update/delete/etc.) |
| `entity_type` | VARCHAR(50) | NOT NULL | Entity type (employee/department/task/etc.) |
| `entity_id` | INT | NOT NULL | ID of affected entity |
| `details` | TEXT | NULL | JSON details (before/after values, etc.) |
| `ip_address` | VARCHAR(45) | NULL | IP address of request |
| `created_at` | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | When action occurred |

### Existing Indexes

| Index Name | Columns | Type | Purpose |
|------------|---------|------|---------|
| `idx_logs_user_id` | user_id | INDEX | Filter by user |
| `idx_logs_entity` | entity_type, entity_id | INDEX | Filter by entity |
| `idx_logs_created_at` | created_at | INDEX | Sort by date |

---

## Computed Fields (not stored)

| Field | Logic |
|-------|-------|
| `user_name` | JOIN with users table (or "مستخدم محذوف" if NULL/deleted) |
| `user_status` | Status from users table (active/suspended/terminated/deleted) |
| `distinct_actions` | `SELECT DISTINCT action FROM logs ORDER BY action` |
| `distinct_entity_types` | `SELECT DISTINCT entity_type FROM logs ORDER BY entity_type` |

---

## Query Patterns

### Filtered Log List

```sql
SELECT l.*, u.name as user_name, u.status as user_status
FROM logs l
LEFT JOIN users u ON l.user_id = u.id
WHERE (:user_id IS NULL OR l.user_id = :user_id)
  AND (:action IS NULL OR l.action = :action)
  AND (:entity_type IS NULL OR l.entity_type = :entity_type)
  AND (:entity_id IS NULL OR l.entity_id = :entity_id)
  AND (:date_from IS NULL OR l.created_at >= :date_from)
  AND (:date_to IS NULL OR l.created_at <= :date_to)
ORDER BY l.created_at DESC
LIMIT :limit OFFSET :offset
```

### Count for Pagination

```sql
SELECT COUNT(*) as total
FROM logs l
WHERE [same filter conditions]
```

### CSV Export

```sql
SELECT l.id, u.name as user_name, l.action, l.entity_type, l.entity_id, 
       l.details, l.ip_address, l.created_at
FROM logs l
LEFT JOIN users u ON l.user_id = u.id
WHERE [same filter conditions]
ORDER BY l.created_at DESC
LIMIT 10000
```