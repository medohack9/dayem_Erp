# Data Model: Core System Foundation

**Feature**: 001-core-system-foundation
**Date**: 2026-04-23

---

## Entities

### 1. Settings (system_settings)

Stores application-wide configuration that may be changed at runtime without code deployment.

| Field | Description | Constraints |
|-------|-------------|-------------|
| id | Unique identifier | Primary key, auto-increment |
| setting_key | Configuration key name | Unique, not null, max 100 chars |
| setting_value | Configuration value | Text, nullable |
| setting_group | Logical grouping | Not null, max 50 chars (e.g., "general", "mail", "display") |
| created_at | Record creation timestamp | Not null, default current timestamp |
| updated_at | Last modification timestamp | Nullable, updates on change |

**Validation rules**:
- `setting_key` must be unique and use snake_case format
- `setting_value` is free-form text (JSON-encoded for complex values)

**Notes**: This table enables future runtime configuration without redeployment. Phase 1 seeds it with environment mode and basic app settings.

---

### 2. Error Logs (error_logs)

Stores all system errors regardless of environment mode (FR-011).

| Field | Description | Constraints |
|-------|-------------|-------------|
| id | Unique identifier | Primary key, auto-increment |
| error_level | Severity level | Not null, max 20 chars (e.g., "error", "warning", "notice") |
| error_message | Error description | Text, not null |
| error_file | Source file where error occurred | Text, nullable |
| error_line | Line number in source file | Integer, nullable |
| error_trace | Stack trace | Long text, nullable |
| request_url | URL that triggered the error | Text, nullable |
| request_method | HTTP method (GET, POST, etc.) | Max 10 chars, nullable |
| user_agent | Browser user-agent string | Text, nullable |
| ip_address | Client IP address | Max 45 chars (IPv6 compatible), nullable |
| created_at | When the error occurred | Not null, default current timestamp |

**Validation rules**:
- `error_level` restricted to: error, warning, notice, critical, fatal
- `error_message` is required (not empty)

**Notes**: This table fulfills FR-011 (record all errors internally). In production, errors display a friendly page but are always logged here. Old entries can be purged periodically.

---

## Relationships

```text
system_settings  ── (standalone, no foreign keys)
error_logs       ── (standalone, no foreign keys)
```

Both entities in this phase are standalone system tables with no cross-references. Future phases will introduce `users`, `departments`, etc. with proper foreign key relationships.

---

## State Transitions

### Error Log Lifecycle

No state transitions — error logs are append-only. They are created on error occurrence and never modified.

### Settings Lifecycle

Settings are seeded during installation and can be updated by admins. No soft delete applies — settings are overwritten, not removed.

---

## Database Conventions (from Constitution)

- **Engine**: InnoDB (all tables)
- **Naming**: snake_case for all table and column names
- **Audit fields**: `created_at` on all tables; `updated_at` where records are mutable
- **Soft delete**: Not applicable this phase (no user-facing entities to delete)
- **Character set**: utf8mb4 (required for full Arabic character support)
- **Collation**: utf8mb4_unicode_ci (proper Arabic text sorting)
