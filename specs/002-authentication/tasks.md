# Tasks: Authentication

**Input**: Design documents from `specs/002-authentication/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/http-routes.md, quickstart.md
**Target**: Cheaper LLM implementation — every task is self-contained with exact specs

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1-US4)

## User Story Mapping

| Story | Title | Priority | Spec Reference |
|-------|-------|----------|----------------|
| US1 | Log In to the System | P1 | Routing + login + validation + lockout |
| US2 | Log Out of the System | P1 | Session destruction + redirect |
| US3 | Role-Based Access After Login | P2 | RBAC + sidebar + middleware |
| US4 | Session Timeout and Security | P2 | Single-session + timeout + CSRF |

---

## Phase 1: Setup (Database & Configuration)

**Purpose**: Create the database tables and update configuration files. Nothing runs yet — just the schema and config.

- [x] T001 Create `database/migrations/002_authentication.sql` — SQL file that creates three tables. Table 1 `users`: `id INT AUTO_INCREMENT PRIMARY KEY`, `name VARCHAR(100) NOT NULL`, `email VARCHAR(255) NOT NULL UNIQUE`, `phone VARCHAR(20) NOT NULL UNIQUE`, `password VARCHAR(255) NOT NULL`, `role ENUM('admin','employee') NOT NULL DEFAULT 'employee'`, `status ENUM('active','suspended','terminated') NOT NULL DEFAULT 'active'`, `failed_login_attempts INT NOT NULL DEFAULT 0`, `locked_until TIMESTAMP NULL`, `deleted_at TIMESTAMP NULL`, `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`, `updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP`, `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`. Table 2 `auth_logs`: `id INT AUTO_INCREMENT PRIMARY KEY`, `user_id INT NULL`, `identifier VARCHAR(255) NOT NULL`, `ip_address VARCHAR(45) NULL`, `user_agent TEXT NULL`, `outcome ENUM('success','failed','locked') NOT NULL`, `failure_reason VARCHAR(100) NULL`, `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`, `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL`, `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`. Table 3 `user_sessions`: `id INT AUTO_INCREMENT PRIMARY KEY`, `user_id INT NOT NULL`, `session_id VARCHAR(128) NOT NULL UNIQUE`, `ip_address VARCHAR(45) NULL`, `user_agent TEXT NULL`, `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`, `last_activity TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`, `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`, `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`. After creating tables, add indexes: `CREATE INDEX idx_users_email ON users(email)`, `CREATE INDEX idx_users_phone ON users(phone)`, `CREATE INDEX idx_users_status ON users(status)`, `CREATE INDEX idx_users_role ON users(role)`, `CREATE INDEX idx_auth_logs_user_id ON auth_logs(user_id)`, `CREATE INDEX idx_auth_logs_created_at ON auth_logs(created_at)`, `CREATE INDEX idx_auth_logs_outcome ON auth_logs(outcome)`, `CREATE INDEX idx_auth_logs_ip_address ON auth_logs(ip_address)`, `CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id)`, `CREATE INDEX idx_user_sessions_session_id ON user_sessions(session_id)`. Then seed the admin user: `INSERT INTO users (name, email, phone, password, role, status) VALUES ('مدير النظام', 'admin@dayem.com', '01000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active')` — note: the hash is for password `password`; replace it during implementation by using `password_hash('Dayem@2026', PASSWORD_BCRYPT)` in a PHP script or generate the hash manually.

- [x] T002 [P] Update `config/app.php` — add authentication configuration keys to the returned array: `'lockout_attempts' => 5` (number of failed attempts before lockout), `'lockout_duration' => 15` (lockout duration in minutes). Keep all existing keys intact.

- [x] T003 [P] Create `app/middleware/` directory — create the empty directory for middleware classes.

---

## Phase 2: Foundational (Core Auth Infrastructure — BLOCKS All User Stories)

**Purpose**: Build the models, auth helper, and middleware that every user story depends on.

**CRITICAL**: No user story work can begin until this phase is complete.

- [x] T004 Create `app/models/UserModel.php` — class `UserModel` in namespace `App\Models`, extends `App\Core\Model`. Methods: `findById(int $id)` — return `$this->findOne('users', 'id = :id AND deleted_at IS NULL', ['id' => $id])`. `findByIdentifier(string $identifier)` — if `$identifier` contains `@`, search by email: `$this->findOne('users', 'email = :email AND deleted_at IS NULL', ['email' => $identifier])`; otherwise search by phone: `$this->findOne('users', 'phone = :phone AND deleted_at IS NULL', ['phone' => $identifier])`. `findAllActive()` — return `$this->findAll('users', 'deleted_at IS NULL')`. `isLocked(array $user)` — return `true` if `$user['locked_until'] !== null && strtotime($user['locked_until']) > time()`. `incrementFailedAttempts(int $userId)` — execute `$this->query('UPDATE users SET failed_login_attempts = failed_login_attempts + 1, locked_until = IF(failed_login_attempts >= 4, DATE_ADD(NOW(), INTERVAL 15 MINUTE), locked_until) WHERE id = :id', ['id' => $userId])`. `resetFailedAttempts(int $userId)` — execute `$this->query('UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE id = :id', ['id' => $userId])`. `unlockAccount(int $userId)` — same as `resetFailedAttempts` — set `failed_login_attempts = 0`, `locked_until = NULL`. `createUser(array $data)` — call `$this->insert('users', $data)` with `$data['password']` already hashed using `password_hash()`. `updateUser(int $userId, array $data)` — call `$this->update('users', $data, 'id = :id', ['id' => $userId])`. `softDelete(int $userId)` — call `$this->update('users', ['deleted_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $userId])`.

- [x] T005 [P] Create `app/models/AuthLogModel.php` — class `AuthLogModel` in namespace `App\Models`, extends `App\Core\Model`. Methods: `logAttempt(array $data)` — call `$this->insert('auth_logs', $data)` with keys: `user_id` (nullable), `identifier`, `ip_address`, `user_agent`, `outcome`, `failure_reason`. `getRecentByUser(int $userId, int $limit = 50)` — return `$this->query('SELECT * FROM auth_logs WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit', ['user_id' => $userId, 'limit' => $limit])->fetchAll()`. `getRecentByIp(string $ip, int $limit = 50)` — return `$this->query('SELECT * FROM auth_logs WHERE ip_address = :ip ORDER BY created_at DESC LIMIT :limit', ['ip' => $ip, 'limit' => $limit])->fetchAll()`. `getFailedAttemptsCount(int $userId)` — return `$this->query('SELECT COUNT(*) as count FROM auth_logs WHERE user_id = :user_id AND outcome = :outcome AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)', ['user_id' => $userId, 'outcome' => 'failed'])->fetch()['count']`.

- [x] T006 [P] Create `app/models/UserSessionModel.php` — class `UserSessionModel` in namespace `App\Models`, extends `App\Core\Model`. Methods: `createSession(int $userId, string $sessionId, string $ip, string $userAgent)` — first call `$this->deleteByUserId($userId)` to enforce single-session, then call `$this->insert('user_sessions', ['user_id' => $userId, 'session_id' => $sessionId, 'ip_address' => $ip, 'user_agent' => $userAgent])`. `deleteBySessionId(string $sessionId)` — call `$this->query('DELETE FROM user_sessions WHERE session_id = :sid', ['sid' => $sessionId])`. `deleteByUserId(int $userId)` — call `$this->query('DELETE FROM user_sessions WHERE user_id = :uid', ['uid' => $userId])`. `findBySessionId(string $sessionId)` — return `$this->findOne('user_sessions', 'session_id = :sid', ['sid' => $sessionId])`. `updateActivity(string $sessionId)` — call `$this->query('UPDATE user_sessions SET last_activity = NOW() WHERE session_id = :sid', ['sid' => $sessionId])`.

- [x] T007 Create `app/core/Auth.php` — class `Auth` in namespace `App\Core`. This is a static helper class for authentication checks. Static method `check()`: return `isset($_SESSION['user_id'])`. Static method `user()`: return an array `['id' => $_SESSION['user_id'] ?? null, 'name' => $_SESSION['user_name'] ?? null, 'email' => $_SESSION['user_email'] ?? null, 'role' => $_SESSION['user_role'] ?? null]` if logged in, or `null` if not. Static method `id()`: return `$_SESSION['user_id'] ?? null`. Static method `role()`: return `$_SESSION['user_role'] ?? null`. Static method `isAdmin()`: return `$_SESSION['user_role'] === 'admin'`. Static method `isEmployee()`: return `$_SESSION['user_role'] === 'employee'`. Static method `login(array $user)`: set `$_SESSION['user_id'] = $user['id']`, `$_SESSION['user_name'] = $user['name']`, `$_SESSION['user_email'] = $user['email']`, `$_SESSION['user_role'] = $user['role']`, `$_SESSION['last_activity'] = time()`. Static method `logout()`: call `session_unset()`, `session_destroy()`, `session_start()`, then regenerate CSRF token with `$_SESSION['csrf_token'] = bin2hex(random_bytes(32))`.

- [x] T008 Create `app/middleware/AuthMiddleware.php` — class `AuthMiddleware` in namespace `App\Middleware`. Static method `handle(array $routeConfig, ?callable $getUser)` — or simpler: static method `check(array $routeConfig)`: (1) Get route config array from Router, (2) If `$routeConfig['auth']` is not set or `false`, return `true` (public route, no check needed), (3) If `$routeConfig['auth'] === true`: check if `Auth::check()` returns `true`. If not logged in, redirect to `/auth/login` with `header('Location: /auth/login')` and `exit`, (4) If `$routeConfig['roles']` is set, check if `Auth::role()` is in `$routeConfig['roles']` array. If role not allowed, set `http_response_code(403)` and render `errors/403` view, then `exit`, (5) If all checks pass, update `user_sessions.last_activity` via `UserSessionModel::updateActivity()` and return `true`. Static method `validateSession()`: call `UserSessionModel::findBySessionId(session_id())`. If not found (session was invalidated), destroy session via `Auth::logout()` and redirect to `/auth/login` with flash message "تم تسجيل دخولك من جهاز آخر" (You logged in from another device). If found, return `true`.

- [x] T009 Update `app/core/Router.php` — modify the `dispatch()` method to pass route config to `AuthMiddleware::check()` before instantiating the controller. In the `dispatch()` method, after determining `$handler = [$controllerName, $action]` from the routes, extract `$routeConfig` (the entire route value array including `auth` and `roles` keys). Then call `AuthMiddleware::check($routeConfig)`. If the middleware redirects or exits, the controller is never reached. Also update `addRoutes()` — routes are now in the format `'METHOD /path' => ['ControllerName', 'actionMethod', 'auth' => true, 'roles' => ['admin']]` where `auth` and `roles` are optional keys. The router must pass the full config array to middleware. Similarly update `matchRouteWithParams()` to handle route configs with extra keys.

- [x] T010 Update `config/routes.php` — replace the existing route array with authentication-aware routes. The new format: `'GET /' => ['HomeController', 'index', 'auth' => true]`, `'GET /auth/login' => ['AuthController', 'loginForm', 'auth' => false]`, `'POST /auth/login' => ['AuthController', 'login', 'auth' => false]`, `'GET /auth/logout' => ['AuthController', 'logout', 'auth' => true]`. Keep any other existing routes and add `'auth' => true` to each one. The `auth` key defaults to `false` if absent, meaning public access.

---

## Phase 3: User Story 1 — Log In to the System (Priority: P1)

**Goal**: A user can log in with email or phone + password. Invalid credentials show a generic Arabic error. Locked accounts show a lockout message. Suspended/terminated users get the same generic error.

**Independent Test**: Visit `/auth/login`, enter `admin@dayem.com` / `Dayem@2026`, verify redirect to home page with user info in sidebar. Enter wrong password, verify generic Arabic error. Enter wrong email, verify same generic error.

### Implementation

- [x] T011 [US1] Create `app/controllers/AuthController.php` — class `AuthController` in namespace `App\Controllers`, extends `App\Core\Controller`. Method `loginForm()`: if `Auth::check()` returns `true`, redirect to `/` with `$this->redirect('/')`. Otherwise, render `auth/login` view with `['pageTitle' => 'تسجيل الدخول', 'activePage' => 'auth']`. Method `login()`: (1) Validate CSRF token — call `$this->validateCsrf()` from the base controller (which compares `$_POST['_csrf_token']` with `$_SESSION['csrf_token']`). If the request is AJAX (check `$_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'` or content type JSON), read the raw input with `json_decode(file_get_contents('php://input'), true)` and use `_csrf_token` from the JSON body for CSRF validation, modifying the `validateCsrf()` check to handle both form POST and JSON POST. (2) Get `identifier` and `password` from the request (either `$_POST` or JSON body). (3) Validate inputs: if `identifier` is empty, return JSON `{'success': false, 'errors': {'identifier': 'هذا الحقل مطلوب'}}` with 422 status. If `password` is empty, return JSON `{'success': false, 'errors': {'password': 'كلمة المرور مطلوبة'}}` with 422 status. (4) Create `UserModel` instance, call `$userModel->findByIdentifier($identifier)`. If no user found AND `identifier` contains `@`, also try `$userModel->findByIdentifier($identifier)` with phone lookup as fallback. Log the attempt in `auth_logs` with `outcome = 'failed'` and `failure_reason = 'invalid_credentials'`. Return JSON `{'success': false, 'message': 'بيانات الدخول غير صحيحة'}`. (5) If user found, check `$userModel->isLocked($user)`. If locked, log attempt with `outcome = 'locked'`, return JSON `{'success': false, 'message': 'الحساب مقفل مؤقتاً. حاول مرة أخرى بعد 15 دقيقة'}`. (6) If user found but `$user['status'] !== 'active'`, log attempt with `outcome = 'failed'` and appropriate `failure_reason` ('account_suspended' or 'account_terminated'), but STILL return JSON `{'success': false, 'message': 'بيانات الدخول غير صحيحة'}` (generic message — prevents enumeration). (7) Verify password with `password_verify($password, $user['password'])`. If wrong, increment failed attempts via `$userModel->incrementFailedAttempts($user['id'])`, log attempt with `outcome = 'failed'`, `failure_reason = 'invalid_credentials'`, return JSON `{'success': false, 'message': 'بيانات الدخول غير صحيحة'}`. (8) If password correct: reset failed attempts via `$userModel->resetFailedAttempts($user['id'])`, create session via `Auth::login($user)`, regenerate session ID with `session_regenerate_id(true)`, store new session in `user_sessions` via `UserSessionModel::createSession($user['id'], session_id(), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']`)`, log attempt with `outcome = 'success'`, return JSON `{'success': true, 'redirect': '/', 'user': {'name': $user['name'], 'role': $user['role']}}`.

- [x] T012 [US1] Create `app/views/auth/login.php` — the login page view (content only, rendered within the base layout). Content: a centered container with Tailwind classes, an `<h1>` saying "تسجيل الدخول" (Log In) with `text-2xl font-bold text-[#111111]`, a `<form>` with `id="login-form"` and `@submit.prevent` handled via JavaScript. Form fields: a single `<input>` for identifier (email or phone) labeled "البريد الإلكتروني أو رقم الهاتف" with classes `w-full border rounded px-3 py-2 focus:outline-none focus:border-[#F4C400]` and `dir="ltr"` for the input (Arabic label, LTR input), a password `<input>` labeled "كلمة المرور" (Password) with same styling, a hidden `_csrf_token` input, and a submit button "تسجيل الدخول" (Log In) styled with `bg-[#F4C400] text-[#111111] font-bold py-2 px-4 rounded hover:bg-yellow-500 transition-colors w-full`. Include a `<div id="login-error" class="hidden text-red-600 text-sm mt-2">` for error messages. Include a `<div id="login-errors" class="hidden">` for per-field validation errors. The form should NOT use the base layout's sidebar/header (login page has its own minimal layout). Modify the render call in `loginForm()` to use a special login layout or render without the sidebar — specifically, `loginForm()` should render `auth/login` WITHOUT the main layout (use `$this->renderWithoutLayout('auth/login', ...)` or create a minimal `app/views/layouts/login.php` that wraps content in `<html lang="ar" dir="rtl">` with Tailwind CDN and Cairo font but without sidebar/header).

- [x] T013 [US1] Create `app/views/layouts/login.php` — a minimal layout for the login page (no sidebar, no header). Structure: `<!DOCTYPE html>`, `<html lang="ar" dir="rtl">`, `<head>` with `<meta charset="utf-8">`, `<meta name="viewport" content="width=device-width, initial-scale=1.0">`, `<title><?= htmlspecialchars($pageTitle ?? 'تسجيل الدخول - Dayem ERP') ?></title>`, `<script src="https://cdn.tailwindcss.com"></script>`, `<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">`, `<style>body { font-family: 'Cairo', sans-serif; }</style>`, `<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">`. `<body class="bg-gray-100 min-h-screen flex items-center justify-center">`: `<div class="w-full max-w-md">`, `<div class="bg-white rounded-lg shadow-md p-8">`, `<div class="text-center mb-6">`, `<h1 class="text-3xl font-bold text-[#F4C400]">دايم</h1>`, `<p class="text-gray-600 text-sm">نظام إدارة الموارد البشرية</p>`, `</div>`, then `<?= $content ?>` for the form content, close divs. `<script src="/js/app.js"></script>` before `</body>`. Update `AuthController::loginForm()` to use this layout instead of the main layout.

- [x] T014 [US1] Update `public/js/app.js` — add a login form handler at the bottom of the file. The handler: (1) Select `#login-form` element, (2) Add submit event listener that prevents default, (3) Collect form data into an object `{identifier, password, _csrf_token}`, (4) Send via `fetchApi('/auth/login', {method: 'POST', body: JSON.stringify(data), headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken}})`, (5) On success response (`success: true`), redirect to `response.redirect || '/'`, (6) On validation error (422 status), display per-field errors next to each input in Arabic, (7) On generic error (`success: false`), display the error message in the `#login-error` div with red text, (8) On CSRF error (403), display "طلب غير مصرح به" in the error div, (9) Show/hide loading state on the submit button (disable button, change text to "جاري التحميل...").

**Checkpoint**: Import `002_authentication.sql` into MySQL. Visit `/auth/login`. Verify the login page renders with Arabic RTL layout and Dayem branding. Enter `admin@dayem.com` / `Dayem@2026` → should redirect to home page with user name in header. Enter wrong password → should see "بيانات الدخول غير صحيحة". Enter nonexistent email → same generic error. Enter wrong credentials 5 times → should see lockout message.

---

## Phase 4: User Story 2 — Log Out of the System (Priority: P1)

**Goal**: An authenticated user can log out. The session is destroyed and the user is redirected to the login page.

**Independent Test**: After logging in, click the logout button in the header. Verify redirect to login page. Verify protected pages redirect to login.

### Implementation

- [x] T015 [US2] Add logout method to `app/controllers/AuthController.php` — method `logout()`: (1) If `Auth::check()` is `false`, just redirect to `/auth/login`. (2) Get current session ID with `session_id()`, (3) Delete from `user_sessions` via `UserSessionModel::deleteBySessionId(session_id())`, (4) Call `Auth::logout()` which destroys the session and starts a new one, (5) Redirect to `/auth/login` with a flash message. Since PHP sessions don't natively support flash messages, store a temporary flag: set `$_SESSION['logout_success'] = true` before destroying, then after `session_start()` in the new session, check for this flag in the `loginForm()` method and display "تم تسجيل الخروج بنجاح" (Logged out successfully) as a green notification.

- [x] T016 [US2] Update `app/views/components/header.php` — modify the header to show the logged-in user's name and a logout button. Replace the existing `<div class="text-[#111111] text-sm">مرحبا</div>` with: if `Auth::check()` is true, display `<div class="flex items-center gap-3">`, `<span class="text-[#111111] text-sm">مرحبا، <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></span>`, `<a href="/auth/logout" class="text-[#111111] text-sm hover:text-red-600">خروج</a>`, `</div>`. If not logged in, display the original `مرحبا` text. This requires adding `use App\Core\Auth;` at the top of the view or checking `$_SESSION` directly, since views may not have access to the Auth class. Use `$_SESSION['user_name']` directly in the view since it's already populated by `Auth::login()`.

- [x] T017 [US2] Update `app/views/components/sidebar.php` — modify the sidebar to conditionally show navigation links based on user role. At the top of the file, check `$_SESSION['user_role']`. If role is `'admin'`, show all links (الرئيسية, تسجيل الدخول hide this since they're logged in, الأقسام, الموظفين, المهام, التذاكر, الرواتب, الملفات, السجلات, لوحة التحكم). If role is `'employee'`, show only (الرئيسية, المهام, التذاكر, الملفات, الرواتب). Remove the "تسجيل الدخول" (Login) link from the sidebar since it's only for unauthenticated users — it should only appear when NOT logged in.

**Checkpoint**: Log in, verify header shows user name and "خروج" (Logout) button. Click logout. Verify redirect to login page with success message. Try accessing `/` — should redirect to `/auth/login`.

---

## Phase 5: User Story 3 — Role-Based Access After Login (Priority: P2)

**Goal**: After login, users see different sidebar links based on their role. Employees accessing admin-only URLs get a 403 page. Backend routes are protected by middleware.

**Independent Test**: Log in as admin → all sidebar links visible. Log in as employee → only 5 links visible. Access `/departments` as employee → 403 page.

### Implementation

- [x] T018 [US3] Update `app/middleware/AuthMiddleware.php` — enhance the `check()` method to validate the session against the `user_sessions` table. After confirming the user is logged in (step 3), call `UserSessionModel::findBySessionId(session_id())`. If the session ID is NOT found in `user_sessions`, this means the session was invalidated by another login. Destroy the session and redirect to `/auth/login` with a message "تم تسجيل دخولك من جهاز آخر" (You have logged in from another device). If found, call `UserSessionModel::updateActivity(session_id())` to update `last_activity`. Run `php -l` to verify syntax.

- [x] T019 [US3] Update `config/routes.php` — add role restrictions to admin-only routes. The complete routes file should be: `'GET /' => ['HomeController', 'index', 'auth' => true]`, `'GET /auth/login' => ['AuthController', 'loginForm', 'auth' => false]`, `'POST /auth/login' => ['AuthController', 'login', 'auth' => false]`, `'GET /auth/logout' => ['AuthController', 'logout', 'auth' => true]`, `'GET /departments' => ['HomeController', 'notFound', 'auth' => true, 'roles' => ['admin']]`, `'GET /employees' => ['HomeController', 'notFound', 'auth' => true, 'roles' => ['admin']]`, `'GET /tasks' => ['HomeController', 'notFound', 'auth' => true, 'roles' => ['admin', 'employee']]`, `'GET /tickets' => ['HomeController', 'notFound', 'auth' => true, 'roles' => ['admin', 'employee']]`, `'GET /salary' => ['HomeController', 'notFound', 'auth' => true, 'roles' => ['admin', 'employee']]`, `'GET /files' => ['HomeController', 'notFound', 'auth' => true, 'roles' => ['admin', 'employee']]`, `'GET /logs' => ['HomeController', 'notFound', 'auth' => true, 'roles' => ['admin']]`, `'GET /dashboard' => ['HomeController', 'notFound', 'auth' => true, 'roles' => ['admin']]`. (Note: the placeholder routes like `/departments` currently point to `HomeController::notFound()` since those modules don't exist yet — they just need to exist in the route map so the middleware can check roles.)

- [x] T020 [US3] Update `app/views/components/sidebar.php` — wrap each navigation link in a conditional based on `$_SESSION['user_role']`. Admin sees all links. Employee sees only: الرئيسية (`/`), المهام (`/tasks`), التذاكر (`/tickets`), الملفات (`/files`), الرواتب (`/salary`). Remove the "تسجيل الدخول" link entirely from the sidebar since logged-in users don't need it. Add a "تسجيل الخروج" link pointing to `/auth/logout` that is shown ONLY when the user is logged in. The sidebar conditional should check `isset($_SESSION['user_id'])` for logged-in state.

- [x] T021 [US3] Update `app/core/Controller.php` — add a `requireAuth()` method: if `!Auth::check()`, redirect to `/auth/login`. Add a `requireRole(string ...$roles)` method: if `!in_array(Auth::role(), $roles)`, set `http_response_code(403)` and render `errors/403` with `['pageTitle' => 'غير مصرح', 'activePage' => '']`. These are convenience methods for controllers that need additional checks beyond middleware.

- [x] T022 [US3] Update `app/views/errors/403.php` — verify the 403 page content is correct. It should display: "403" in large yellow text, "غير مصرح" (Unauthorized) as heading, "ليس لديك صلاحية للوصول إلى هذه الصفحة" (You do not have permission) as paragraph, and a "العودة للرئيسية" (Back to home) link. This file was created in Phase 1 but ensure it renders within the main layout when accessed through middleware (the middleware should set `$pageTitle` and `$activePage` before rendering).

**Checkpoint**: Log in as admin → all 9 sidebar links visible (excluding login, including logout). Log in as employee → only 5 permitted links shown. Access `/departments` URL directly as employee → 403 forbidden page with Arabic message. Access `/` as employee → home page loads fine.

---

## Phase 6: User Story 4 — Session Timeout and Security (Priority: P2)

**Goal**: Sessions expire after 30 min of inactivity. Single-session enforcement works (login from new device invalidates old session). CSRF validation is active on all forms.

**Independent Test**: Log in, wait (or simulate) 30 min timeout → session-expired page. Log in from browser A, then browser B → browser A redirected to login on next request. Submit login form without CSRF token → 403 error.

### Implementation

- [x] T023 [US4] Update `app/core/Session.php` — modify the session timeout check to also handle the case where the session was invalidated from another device. After the existing timeout check, add: if `isset($_SESSION['user_id'])`, verify the session exists in `user_sessions` by calling `UserSessionModel::findBySessionId(session_id())`. If not found, destroy the session and redirect to `/auth/login`. This requires adding `use App\Models\UserSessionModel;` at the top. Note: Since `Session.php` is called before autoloader registration in `App.php`, this check should be moved to the `AuthMiddleware::check()` method instead (already done in T018). The Session class should ONLY handle the 30-minute timeout and CSRF token generation (which it already does from Phase 1). No changes needed here if T018 handles the user_sessions validation.

- [x] T024 [US4] Update `app/core/App.php` — after `initSession()` and `registerAutoloader()`, the `dispatch()` method should call `AuthMiddleware::check()` for each route before the controller is instantiated. This is already handled by T009 (updating Router.php to pass route config to middleware). Verify that the `App.php` bootstrap sequence is: (1) load config, (2) set timezone, (3) register autoloader, (4) init error handler, (5) init session, (6) dispatch (which now includes middleware check). No changes needed in App.php itself if Router and AuthMiddleware are properly wired.

- [x] T025 [US4] Update `app/controllers/AuthController.php` — in the `login` method, add the account lockout check BEFORE password verification. The flow should be: (1) CSRF check, (2) input validation, (3) find user by identifier, (4) if user not found → generic error, (5) check `isLocked($user)` → if locked, return lockout message in JSON, (6) check `$user['status'] !== 'active'` → generic error (prevents enumeration), (7) verify password → if wrong, increment failed attempts and return generic error, (8) if correct, reset failed attempts, create session, log success, return redirect. This is the complete flow that was described in T011 but needs to be verified and correctly ordered. Also add: after successful login, regenerate session ID with `session_regenerate_id(true)` and insert into `user_sessions` table via `UserSessionModel::createSession()`.

- [x] T026 [US4] Create `app/views/auth/session-message.php` — a view for displaying session-related messages on the login page. This includes: (1) "تم تسجيل الخروج بنجاح" (Logged out successfully) in green, shown after logout, (2) "انتهت الجلسة، يرجى تسجيل الدخول مرة أخرى" (Session expired, please log in again) in amber/yellow, shown after session timeout, (3) "تم تسجيل دخولك من جهاز آخر" (You have logged in from another device) in red, shown when session is invalidated by another login. Update `AuthController::loginForm()` to check for `$_SESSION['flash_message']` and `$_SESSION['flash_type']` and pass them to the view. The session-expired redirect should set `$_SESSION['flash_message'] = 'انتهت الجلسة، يرجى تسجيل الدخول مرة أخرى'` and `$_SESSION['flash_type'] = 'warning'`. The concurrent login redirect should set `$_SESSION['flash_message'] = 'تم تسجيل دخولك من جهاز آخر'` and `$_SESSION['flash_type'] = 'error'`.

- [x] T027 [US4] Update `app/views/layouts/main.php` — add session expiration handling. In the `<head>` section, add a meta refresh or JavaScript check that monitors session timeout. Add a small JS snippet after the `app.js` script: `setInterval(function() { fetch('/auth/check-session', {headers: {'X-CSRF-Token': window.csrfToken}}).then(r => { if(r.status === 401) { window.location.href = '/auth/login'; }}); }, 60000);` — but since we don't have a session check endpoint, instead rely on the 30-minute PHP session timeout (from Phase 1's Session class) and the `last_activity` check. Users will be redirected to login on their next request if the session expires. No additional JS needed — the existing Session class handles this.

- [x] T028 [US4] Verify CSRF protection is working — confirm that the `validateCsrf()` method in `Controller.php` (from Phase 1) is called in `AuthController::login()`. Verify that the login form includes `<input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">` inside the form. Verify that the AJAX login handler in `app.js` sends the CSRF token via `X-CSRF-Token` header AND `_csrf_token` in the body. Verify that the existing 403 error page displays properly for CSRF failures.

**Checkpoint**: Log in, wait for session timeout (or modify `config/app.php` temporarily to set `session_timeout => 1` for testing), verify redirect to login with "session expired" message. Log in from browser A, then browser B — verify browser A is redirected to login on next request with "logged in from another device" message. Submit login form without CSRF token → verify 403 response.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final cleanup, verification, and integration testing across all stories

- [x] T029 Update `app/views/components/sidebar.php` — finalize the sidebar to properly show/hide links based on authentication state AND role. When NOT logged in: show only "تسجيل الدخول" (Login) link. When logged in as admin: show all 8 module links plus "تسجيل الخروج" (Logout). When logged in as employee: show only 4 allowed module links plus "تسجيل الخروج". Move the "تسجيل الدخول" link logic into the sidebar so it shows for unauthenticated users and is hidden for authenticated users.

- [x] T030 Update `app/views/components/header.php` — finalize the header to show: for authenticated users — "مرحبا، {user_name}" and a logout link "خروج" linking to `/auth/logout`. For unauthenticated users — just the app name "نظام دايم" without username/logout.

- [x] T031 Update `app/controllers/HomeController.php` — add `'auth' => true` requirement implicitly via the middleware (already done in routes.php). Ensure the `index()` method works for both admin and employee users. The `$appNameFromDb` variable should still work since it's fetched from `SettingModel`. Verify the `notFound()` method also works correctly.

- [x] T032 Run `php -l` on all PHP files — check `app/controllers/AuthController.php`, `app/core/Auth.php`, `app/middleware/AuthMiddleware.php`, `app/models/UserModel.php`, `app/models/AuthLogModel.php`, `app/models/UserSessionModel.php`, `app/views/auth/login.php`, `app/views/layouts/login.php` for syntax errors. Fix any parse errors found.

- [x] T033 Verify all PHP files use `<?php` opening tag with NO closing `?>` tag. Check: `app/controllers/AuthController.php`, `app/core/Auth.php`, `app/middleware/AuthMiddleware.php`, `app/models/UserModel.php`, `app/models/AuthLogModel.php`, `app/models/UserSessionModel.php`. Config files (`config/app.php`, `config/routes.php`) should also omit closing tags.

- [x] T034 Verify `htmlspecialchars()` is used on ALL user-facing output in views. Check every `<?= ... ?>` in `app/views/auth/login.php`, `app/views/auth/session-message.php`, `app/views/components/sidebar.php`, `app/views/components/header.php`. Any dynamic value must be wrapped in `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`.

- [x] T035 Run the quickstart.md verification checklist — follow every step in `specs/002-authentication/quickstart.md` and confirm each checkbox passes: login page loads, successful login with email/phone, invalid credentials error, account lockout after 5 attempts, logout works, protected page redirect, role-based sidebar, 403 for unauthorized access, session timeout, CSRF validation.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 completion — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Phase 2 — login functionality
- **US2 (Phase 4)**: Depends on Phase 3 — logout requires working login
- **US3 (Phase 5)**: Depends on Phase 3 — RBAC requires working login
- **US4 (Phase 6)**: Depends on Phase 3 — session security requires working login
- **Polish (Phase 7)**: Depends on all user stories complete

### User Story Dependencies

```text
Phase 1 (Setup)
    │
    ▼
Phase 2 (Foundational) ──── BLOCKS ALL ────┐
    │                                       │
    ├──► Phase 3 (US1: Login) ◄────────────┤
    │         │                             │
    │         ├──► Phase 4 (US2: Logout) ◄──┤
    │         │                             │
    │         ├──► Phase 5 (US3: RBAC) ◄────┤
    │         │                             │
    │         └──► Phase 6 (US4: Sessions) ◄┤
    │                                       │
    └──────────────────────────────────────►┘
                                         │
                                         ▼
                                  Phase 7 (Polish)
```

### Within Each Phase

- Phase 1: T001 first, then T002/T003 in parallel
- Phase 2: T004 first (UserModel), then T005/T006 in parallel, then T007/T008, then T009/T010
- Phase 3: T011→T012→T013→T014 (sequential — controller first, then views, then JS)
- Phase 4: T015→T016/T017 (logout method first, then UI updates in parallel)
- Phase 5: T018→T019→T020/T021/T022 (middleware first, then routes, then UI)
- Phase 6: T023→T024→T025→T026→T027→T028 (sequential verification)
- Phase 7: T029→T030→T031→T032→T033→T034→T035 (sequential polish)

### Parallel Opportunities

**Phase 1**: T002 and T003 can run in parallel (different files)

**Phase 2**: T005 (AuthLogModel) and T006 (UserSessionModel) can run in parallel (different files, no cross-dependency)

**Phase 4**: T016 (header update) and T017 (sidebar update) can run in parallel after T015

**Phase 5**: T020 (sidebar), T021 (Controller), and T022 (403 page) can run in parallel after T018 and T019

---

## Implementation Strategy

### MVP First (US1 Only)

1. Complete Phase 1: Setup (T001-T003)
2. Complete Phase 2: Foundational (T004-T010)
3. Complete Phase 3: US1 Login (T011-T014)
4. **STOP and VALIDATE**: Log in with admin@dayem.com, verify redirected to home page. Enter wrong password → generic Arabic error. Enter wrong email → same generic error. Enter wrong password 5 times → lockout message.
5. This is the minimum viable proof that authentication works

### Incremental Delivery

1. Setup + Foundational → Auth infrastructure ready
2. Add US1 (Login) → Users can log in
3. Add US2 (Logout) → Users can end their session
4. Add US3 (RBAC) → Role-based access control works
5. Add US4 (Session Security) → Single-session + timeout + CSRF fully enforced
6. Polish → Production-ready authentication
7. Each increment adds value without breaking previous work

---

## Notes

- Every task includes the EXACT file path and detailed implementation spec
- All PHP classes use namespace `App\*` mapped to `app/*` directory
- Database naming: snake_case. PHP naming: camelCase. Both enforced throughout.
- All user-facing text is Arabic. All layouts are RTL.
- Brand colors: Yellow #F4C400, Black #111111 — used consistently
- No Composer, no npm — zero external build dependencies
- Phase 1 core framework must be fully working before starting Phase 2 authentication
- Single-session enforcement via `user_sessions` table — not PHP session files
- Auth middleware is route-level, checked before controller instantiation
- Login form uses AJAX (fetch API) per constitution requirement
- The bcrypt hash for the admin seed password must be generated dynamically, not hardcoded
- Total: 35 tasks across 7 phases
