# Research: Core System Foundation

**Feature**: 001-core-system-foundation
**Date**: 2026-04-23
**Status**: Complete — all decisions resolved

---

## R-001: PHP Router Pattern for Single Entry Point

**Decision**: Array-based route definitions in a config file (`config/routes.php`) with .htaccess URL rewriting to `public/index.php`.

**Rationale**: An array-based route map (`'GET /dashboard' => ['DashboardController', 'index']`) is the simplest pattern that provides clear, maintainable routing without external dependencies. Routes are defined declaratively, easy to scan, and easy to extend per module. The `.htaccess` file rewrites all non-file/non-directory requests to `index.php`, which bootstraps the app and delegates to the Router class.

**Alternatives considered**:
- **Annotation-based routing**: Requires a parser/reflection layer — too complex for a native PHP setup on shared hosting.
- **File-based routing (Next.js style)**: Unconventional for PHP MVC; would make the controller directory structure confusing.
- **Regex-only routing**: Harder to read and maintain; array map with simple parameter patterns (`:id`) is sufficient.

---

## R-002: PDO Connection Management

**Decision**: Singleton pattern via a `Database` class that lazily creates and reuses a single PDO connection per request.

**Rationale**: For a small internal ERP on shared hosting, a singleton is the most practical approach. Each HTTP request gets at most one database connection, and PHP's request lifecycle naturally cleans up connections at request end. Dependency injection containers add complexity without benefit at this scale.

**Alternatives considered**:
- **Dependency injection container**: Overkill for a native PHP app with no framework container. Adds a layer of abstraction that isn't needed for < 10 models.
- **New connection per query**: Wasteful and slow — PDO connection overhead is non-trivial on shared hosting.
- **Connection pooling**: Not available in standard PHP (no persistent process); PHP-FPM persistent connections are unreliable on shared hosting.

**Implementation notes**:
- PDO attributes: `ERRMODE_EXCEPTION`, `DEFAULT_FETCH_MODE => FETCH_ASSOC`, `EMULATE_PREPARES => false`
- Connection params loaded from `config/database.php` (not hardcoded)
- Config file excluded from version control (`.gitignore`); a `database.example.php` template provided

---

## R-003: Session Security and 30-Minute Timeout

**Decision**: PHP native sessions with custom timeout enforcement via timestamp tracking, session ID regeneration on privilege changes, and CSRF token stored in session.

**Rationale**: PHP's built-in session handling is sufficient when augmented with proper timeout checking and regeneration. No need for custom session storage backends for < 100 users.

**Alternatives considered**:
- **Database-backed sessions**: Adds complexity; PHP file sessions are adequate for this scale and Hostinger supports them.
- **JWT tokens**: Stateless auth is unnecessary for a server-rendered MVC app; adds cryptographic complexity without benefit.
- **Redis sessions**: Not available on Hostinger shared hosting.

**Implementation details**:
- Store `last_activity` timestamp in `$_SESSION` on every request
- On each request: check if `time() - $_SESSION['last_activity'] > 1800` (30 min); if expired, destroy session and redirect
- Regenerate session ID (`session_regenerate_id(true)`) after login and on privilege escalation
- CSRF: Generate a per-session token (`bin2hex(random_bytes(32))`), store in `$_SESSION['csrf_token']`, validate on every POST/PUT/DELETE request
- Set `session.cookie_httponly = 1`, `session.cookie_secure = 1` (production), `session.use_strict_mode = 1`

---

## R-004: Tailwind CSS Integration for RTL Arabic UI

**Decision**: Use the Tailwind CSS CDN script (`<script src="https://cdn.tailwindcss.com">`) for development, with a pre-built CSS file for production.

**Rationale**: The CDN approach requires zero build tooling and works immediately on shared hosting. For production, a pre-compiled CSS file (generated once locally using Tailwind CLI) avoids the CDN dependency and improves load times. Tailwind has built-in RTL support via the `rtl:` variant and `dir="rtl"` attribute.

**Alternatives considered**:
- **CDN-only (dev + prod)**: Acceptable for an internal app with < 100 users, but CDN adds ~300ms latency per page load and depends on internet availability.
- **Full build pipeline (npm/PostCSS)**: Requires Node.js tooling which complicates the development workflow on XAMPP and cannot run on Hostinger shared hosting.
- **Alternative CSS frameworks**: Bootstrap has poor RTL support compared to Tailwind's native utilities; DaisyUI adds unnecessary abstraction.

**RTL approach**:
- Set `<html lang="ar" dir="rtl">` on all pages
- Use Tailwind's logical properties: `ms-*`/`me-*` (margin-start/end), `ps-*`/`pe-*` (padding-start/end), `start-*`/`end-*` for positioning
- Tailwind 3.x+ automatically flips physical utilities when `dir="rtl"` is set on the HTML element

---

## R-005: Error Handling Strategy

**Decision**: Custom error handler registered via `set_error_handler()` and `set_exception_handler()`, with behavior controlled by an environment config flag.

**Rationale**: PHP's default error display is unsuitable for production (exposes paths, queries, etc.). A custom handler lets us display friendly Arabic error pages in production while showing full stack traces in development — matching both the spec (FR-010/FR-011) and the constitution (Section 12).

**Alternatives considered**:
- **Whoops library**: Excellent for development but adds a Composer dependency; unnecessary for this project's simplicity.
- **Log-only (no custom display)**: Doesn't meet the requirement for user-friendly Arabic error pages.

**Implementation details**:
- `config/app.php` defines `'environment' => 'development'` or `'production'`
- In development: render error details (file, line, trace) in a styled debug page
- In production: render `views/errors/500.php` (generic Arabic error page)
- All errors logged to `storage/logs/error.log` with timestamp, regardless of environment
- 404 errors routed to `views/errors/404.php` by the Router when no match is found

---

## R-006: Project Folder Structure and Autoloading

**Decision**: Custom PSR-4-style autoloader registered via `spl_autoload_register()` with namespace-to-directory mapping rooted at `app/`.

**Rationale**: Composer may not be reliably available on Hostinger shared hosting. A simple autoloader that maps `App\Controllers\HomeController` to `app/controllers/HomeController.php` provides clean organization without external dependencies.

**Alternatives considered**:
- **Composer autoload**: Standard in modern PHP, but adds a dependency and `vendor/` directory. Can be used locally and committed, but complicates the deployment process for shared hosting.
- **Manual `require` statements**: Unmaintainable as the codebase grows; every new class requires updating includes.
- **Single-file includes**: Anti-pattern; violates modular design constitution rule.

**Mapping convention**:
- `App\Core\*` → `app/core/*.php`
- `App\Controllers\*` → `app/controllers/*.php`
- `App\Models\*` → `app/models/*.php`
- Views are not autoloaded — they are included by the controller via a `render()` method on the base Controller class
