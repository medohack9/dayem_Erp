# Data Model: Authentication

**Feature**: 002-authentication
**Date**: 2026-04-25

---

## Entities

### 1. Users (users)

Stores all system users (admins and employees). The authentication module reads from and writes to this table for login, lockout, and session management.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier |
| name | VARCHAR(100) | NOT NULL | User's full name (Arabic supported) |
| email | VARCHAR(255) | NOT NULL, UNIQUE | User's email address |
| phone | VARCHAR(20) | NOT NULL, UNIQUE | User's phone number (Egyptian format) |
| password | VARCHAR(255) | NOT NULL | Bcrypt-hashed password |
| role | ENUM('admin', 'employee') | NOT NULL, DEFAULT 'employee' | User's access role |
| status | ENUM('active', 'suspended', 'terminated') | NOT NULL, DEFAULT 'active' | Account status |
| failed_login_attempts | INT | NOT NULL, DEFAULT 0 | Counter for consecutive failed logins |
| locked_until | TIMESTAMP | NULL | Lockout expiration time; NULL = not locked |
| deleted_at | TIMESTAMP | NULL | Soft delete timestamp; NULL = active record |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Record creation time |
| updated_at | TIMESTAMP | NULL, ON UPDATE CURRENT_TIMESTAMP | Last modification time |

**Validation rules**:
- `email` must be a valid email format and unique across all non-deleted users
- `phone` must be unique across all non-deleted users
- `password` must be at least 8 characters (enforced on creation, not stored in plain text)
- `failed_login_attempts` resets to 0 on successful login
- `locked_until` is set to `NOW() + 15 minutes` when `failed_login_attempts` reaches 5
- `role` only accepts 'admin' or 'employee'
- `status` only accepts 'active', 'suspended', or 'terminated'

**Indexes**:
- `idx_users_email` on `email` — for login lookup by email
- `idx_users_phone` on `phone` — for login lookup by phone
- `idx_users_status` on `status` — for filtering active users
- `idx_users_role` on `role` — for role-based queries

**Seed data**:
- Admin user: `name='مدير النظام', email='admin@dayem.com', phone='01000000000', role='admin', status='active'`
- Default password: `Dayem@2026` (stored as bcrypt hash)

**Notes**: The `users` table is created in this phase but will be extended in Phase 4 (Employee module) with additional employee-specific fields. The initial seed creates one admin account for system access.

---

### 2. Auth Logs (auth_logs)

Dedicated audit trail for all authentication events. Each login attempt (successful or failed) is recorded here.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique log entry identifier |
| user_id | INT | NULL, FOREIGN KEY → users.id | User ID; NULL if identifier not found in system |
| identifier | VARCHAR(255) | NOT NULL | The email or phone attempted |
| ip_address | VARCHAR(45) | NULL | Client IP address (IPv6 compatible) |
| user_agent | TEXT | NULL | Browser user-agent string |
| outcome | ENUM('success', 'failed', 'locked') | NOT NULL | Result of the login attempt |
| failure_reason | VARCHAR(100) | NULL | Specific reason for failure (invalid_credentials, account_suspended, account_terminated, account_locked) |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | When the attempt occurred |

**Validation rules**:
- `outcome` restricted to: 'success', 'failed', 'locked'
- `failure_reason` populated only when `outcome` is 'failed' or 'locked'
- `user_id` is NULL when the provided identifier doesn't match any user in the system

**Indexes**:
- `idx_auth_logs_user_id` on `user_id` — for querying a user's login history
- `idx_auth_logs_created_at` on `created_at` — for time-based queries and cleanup
- `idx_auth_logs_outcome` on `outcome` — for filtering by success/failure
- `idx_auth_logs_ip_address` on `ip_address` — for IP-based security analysis

**Notes**: This table is separate from `error_logs` (Phase 1) per the clarification decision. It serves security auditing and is optimized for auth-specific queries like "show all failed attempts for user X" or "show all attempts from IP Y".

---

### 3. User Sessions (user_sessions)

Tracks active user sessions for single-session enforcement and session validation.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique session record identifier |
| user_id | INT | NOT NULL, FOREIGN KEY → users.id | The user this session belongs to |
| session_id | VARCHAR(128) | NOT NULL, UNIQUE | PHP session ID |
| ip_address | VARCHAR(45) | NULL | Client IP address at session creation |
| user_agent | TEXT | NULL | Browser user-agent at session creation |
| created_at | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | When the session was created |
| last_activity | TIMESTAMP | NOT NULL, DEFAULT CURRENT_TIMESTAMP | Updated on each request for timeout tracking |

**Validation rules**:
- Only one active row per `user_id` at any time (single-session enforcement)
- `session_id` must match the PHP session ID for the request
- Row is deleted on logout or when a new session replaces it

**Indexes**:
- `idx_user_sessions_user_id` on `user_id` — for looking up a user's session
- `idx_user_sessions_session_id` on `session_id` — for validating current session on each request (UNIQUE)

**Notes**: This table enforces the single-session policy. On login, any existing row for the same `user_id` is deleted before inserting the new session row. On each authenticated request, the middleware checks that the current `session_id` exists in this table; if not, the session was invalidated (another login occurred) and the user is redirected to login.

---

## Relationships

```text
users  ──1:N──>  auth_logs        (one user has many auth log entries)
users  ──1:1──>  user_sessions    (one user has at most one active session)
```

- `auth_logs.user_id` → `users.id` (nullable — NULL when identifier not found)
- `user_sessions.user_id` → `users.id` (not null — must reference a real user)
- `user_sessions.session_id` is unique — enforced by database index

Foreign key constraints:
- `auth_logs.user_id` → `users.id ON DELETE SET NULL` (if a user is soft-deleted, their auth logs remain)
- `user_sessions.user_id` → `users.id ON DELETE CASCADE` (if a user is hard-deleted, their session is removed)

---

## State Transitions

### User Account Status

```text
active  ──[admin suspends]──>  suspended
active  ──[admin terminates]──>  terminated
suspended  ──[admin reactivates]──>  active
terminated  ──[admin reactivates]──>  active
```

- **active**: User can log in and use the system
- **suspended**: User cannot log in (blocked with generic error message to prevent enumeration)
- **terminated**: User cannot log in (same generic error; used for employees who have left the company)

### Account Lockout

```text
normal  ──[5th failed attempt]──>  locked
locked  ──[15 minutes pass]──>  normal (auto-unlock)
locked  ──[admin unlocks]──>  normal (manual unlock)
```

- `failed_login_attempts` increments on each failed login
- At 5 failed attempts, `locked_until` is set to `NOW() + 15 minutes`
- When `locked_until < NOW()`, the account auto-unlocks and `failed_login_attempts` resets
- Admin can manually unlock by setting `failed_login_attempts = 0, locked_until = NULL`

### Auth Log Outcome

```text
login_attempt  ──[valid credentials + active account]──>  success
login_attempt  ──[invalid identifier or wrong password]──>  failed
login_attempt  ──[locked account]──>  locked
login_attempt  ──[suspended/terminated account]──>  failed (failure_reason: account_suspended/account_terminated)
```

---

## Database Conventions (from Constitution)

- **Engine**: InnoDB (all tables)
- **Naming**: snake_case for all table and column names
- **Audit fields**: `created_at` on all tables; `updated_at` on `users` (mutable records)
- **Soft delete**: `deleted_at` on `users` (major entity requires soft delete per constitution)
- **Character set**: utf8mb4 (full Arabic character support)
- **Collation**: utf8mb4_unicode_ci (proper Arabic text sorting)
- **Foreign keys**: Used where applicable to maintain referential integrity