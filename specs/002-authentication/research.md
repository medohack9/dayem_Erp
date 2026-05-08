# Research: Authentication

**Feature**: 002-authentication
**Date**: 2026-04-25
**Status**: Complete — all decisions resolved

---

## R-001: Login Identification Method (Email or Phone)

**Decision**: Single input field that accepts either email or phone number, with backend detection of which format was provided.

**Rationale**: A unified login field simplifies the UX and reduces confusion for users. The backend can distinguish between email (contains `@`) and phone (numeric/dashes) and query the appropriate column. This avoids requiring two separate fields or a toggle UI element.

**Alternatives considered**:
- **Two separate fields (email + phone)**: Clutters the UI and confuses users about which to fill. Requires validation that at least one is filled.
- **Dropdown to choose login type**: Extra click for no benefit; the system can auto-detect the format.
- **Email only**: Excludes phone-only users, contradicts the spec requirement for phone login.

**Implementation notes**:
- Frontend: Single `<input>` field labeled "البريد الإلكتروني أو رقم الهاتف" (Email or phone number)
- Backend: Detect if input contains `@` → email lookup on `users.email`; otherwise → phone lookup on `users.phone`
- Both lookups use prepared statements to prevent SQL injection

---

## R-002: Session Management and Single-Session Enforcement

**Decision**: Store active session identifiers in a `user_sessions` table linked to `users.id`. On login, delete any existing session for that user before creating a new one.

**Rationale**: PHP's default session handling uses file-based storage, which makes it difficult to enforce single-session constraints (we cannot easily query "does user X have an active session on another device?"). A `user_sessions` table provides a database-backed way to track and invalidate sessions across devices.

**Alternatives considered**:
- **Pure PHP sessions (no DB tracking)**: Cannot enforce single-session because file sessions are not queryable by user. Multiple browsers = multiple valid sessions.
- **Custom session handler (save to DB)**: Overkill for < 100 users; would require implementing `SessionHandlerInterface` and rewriting the session layer.
- **Redis sessions**: Not available on Hostinger shared hosting.

**Implementation notes**:
- Table `user_sessions` with columns: `id`, `user_id`, `session_id`, `ip_address`, `user_agent`, `created_at`, `last_activity`
- On login: `DELETE FROM user_sessions WHERE user_id = ?`, then `INSERT` new session row
- On logout: `DELETE FROM user_sessions WHERE session_id = ?`
- On each request (middleware): check if current `session_id` exists in `user_sessions`; if not, session was invalidated → redirect to login
- Session timeout: existing Phase 1 logic (`last_activity > 30 min`) + `user_sessions.last_activity` update on each request

---

## R-003: Password Hashing Strategy

**Decision**: Use PHP's `password_hash()` with `PASSWORD_BCRYPT` (default algorithm) and `password_verify()` for verification.

**Rationale**: `password_hash()` with bcrypt is the PHP-recommended approach and is explicitly mandated by the constitution. It handles salt generation automatically, is future-proof (algo can be changed), and truncates passwords at 72 bytes (bcrypt limitation), which is acceptable for an internal ERP system.

**Alternatives considered**:
- **Argon2ID**: Stronger than bcrypt but may not be available on Hostinger shared hosting (requires `sodium` extension). bcrypt is universally available.
- **SHA-256 with salt**: Not a proper password hashing algorithm (too fast, no work factor). Explicitly forbidden by the constitution.
- **MD5**: Trivially crackable. Explicitly forbidden everywhere.

**Implementation notes**:
- Registration/password set: `password_hash($password, PASSWORD_BCRYPT)`
- Login verification: `password_verify($input, $storedHash)`
- No need for manual salt — `password_hash()` generates it automatically
- The bcrypt 72-byte truncation is acceptable; no need for pre-hashing in this context

---

## R-004: Account Lockout Mechanism

**Decision**: Track `failed_login_attempts` (integer) and `locked_until` (timestamp) columns on the `users` table. Lock after 5 consecutive failed attempts for 15 minutes. Auto-unlock when `locked_until < now()`.

**Rationale**: Storing lockout data on the user row is the simplest approach for < 100 users. No separate lockout table or rate-limiting middleware needed. The 15-minute auto-expiry prevents permanent lockout while still mitigating brute force.

**Alternatives considered**:
- **Separate `login_attempts` table**: Adds complexity with no benefit at this scale. The `auth_logs` table already records all attempts for auditing.
- **Progressive delay (exponential backoff)**: Harder to implement correctly in PHP without a background worker. Simple lockout is more predictable and testable.
- **No lockout**: Unacceptable security risk for a production ERP system.

**Implementation notes**:
- On failed login: `UPDATE users SET failed_login_attempts = failed_login_attempts + 1, locked_until = IF(failed_login_attempts >= 4, NOW() + INTERVAL 15 MINUTE, NULL) WHERE id = ?`
- On successful login: `UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?`
- On login attempt check: if `locked_until IS NOT NULL AND locked_until > NOW()`, reject with "الحساب مقفل مؤقتاً" (Account temporarily locked)
- Admin can manually unlock: `UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = ?`

---

## R-005: Authentication Middleware Architecture

**Decision**: Implement an `AuthMiddleware` class that runs before controller actions. Routes declare their auth requirement and allowed roles in `config/routes.php`. The middleware checks session validity, user existence, and role permissions.

**Rationale**: Centralizing authorization logic in middleware keeps controllers clean and follows the single-responsibility principle. Defining auth requirements alongside routes makes the access control policy visible and easy to audit.

**Alternatives considered**:
- **Per-controller auth checks (manually calling `requireAuth()` in each action)**: Error-prone — a forgotten check exposes pages. Middleware is DRY and automatic.
- **Decorator/annotation pattern (PHP 8 attributes)**: Requires reflection, adds complexity, and doesn't work well without an autoloader that scans attributes.
- **Apache `.htaccess` level protection**: Cannot distinguish roles or handle dynamic route permissions.

**Implementation notes**:
- `config/routes.php` format extended: `'GET /' => ['HomeController', 'index', 'auth' => true, 'roles' => ['admin', 'employee']]` or shorthand `'auth' => true` for any authenticated user
- `AuthMiddleware::handle($routeConfig, $currentUser)`: checks if route requires auth, if user is logged in, and if user's role is in the allowed list
- Unauthenticated → redirect to `/auth/login`
- Unauthorized (wrong role) → 403 page
- Route config `auth => false` or absent means public (e.g., the login page itself)

---

## R-006: Auth Logs Table Design

**Decision**: Dedicated `auth_logs` table with columns for user_id (nullable for unknown users), identifier, IP, user agent, outcome, and timestamp.

**Rationale**: Separating auth logs from error logs (`error_logs` from Phase 1) follows the modular architecture principle. Auth logs have different query patterns (filtering by user, by IP, by outcome) and different retention requirements than system error logs. A dedicated table enables efficient queries like "show all login attempts for user X" without joining with error data.

**Alternatives considered**:
- **Reuse `error_logs` table**: Would require adding `user_id` column and auth-specific `error_level` values. Mixes two distinct concerns and makes querying harder.
- **File-based auth logs**: Not queryable, not searchable, harder to build admin dashboards. Contradicts constitution rule that logs must be stored in database.

---

## R-007: Login Form — Server Render vs AJAX

**Decision**: Server-rendered login form with AJAX (fetch API) submission for authentication. On success, redirect via JavaScript. On failure, display Arabic error message inline without page reload.

**Rationale**: The constitution mandates AJAX for form submissions (Section 10.3). Login is a form submission. Using AJAX provides a smoother UX — no full page reload on failed attempts, immediate inline feedback. The initial page load is server-rendered for SEO and accessibility, but the submit action uses `fetch()`.

**Alternatives considered**:
- **Full page reload on login**: Works but provides poor UX for error states (flash of content, loss of input, no inline validation). Contradicts the AJAX-first principle.
- **SPA-like approach (entire login as client-rendered)**: Unnecessary complexity for a single form. Server-rendered HTML with AJAX submit is the simplest approach.

**Implementation notes**:
- Form posts to `/auth/login` via `fetch()` with JSON body
- Controller returns JSON response: `{success: true, redirect: '/'}` or `{success: false, message: 'بيانات الدخول غير صحيحة'}`
- Validation errors return per-field messages for empty/invalid inputs
- Account locked response: `{success: false, message: 'الحساب مقفل مؤقتاً. حاول مرة أخرى بعد 15 دقيقة'}`

---

## R-008: Protecting Routes — Route Configuration Extension

**Decision**: Extend the route definition format in `config/routes.php` to include auth and role metadata. Routes are defined as: `'METHOD /path' => ['Controller', 'action', 'auth' => bool, 'roles' => [...]]`.

**Rationale**: This keeps the routing configuration centralized and declarative. The router can read these flags and pass them to the middleware before dispatching to the controller. This approach is consistent with Phase 1's simple array-based routing.

**Alternatives considered**:
- **Auth annotations in controller methods**: Requires reflection, adds hidden dependency, fragile.
- **Separate ACL configuration file**: Adds another file to maintain, risk of route/auth config drift.
- **Middleware stack array per route**: Over-engineered for two roles (admin/employee) and < 10 modules.

**Implementation notes**:
- Default behavior: if `auth` key is absent or `false`, the route is public (no login required)
- `"auth" => true` without `roles`: any authenticated user can access
- `"auth" => true, "roles" => ["admin"]`: only admin users can access
- The Router dispatches the route config to AuthMiddleware before calling the controller