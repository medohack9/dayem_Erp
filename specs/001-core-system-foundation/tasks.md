# Tasks: Core System Foundation

**Input**: Design documents from `specs/001-core-system-foundation/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/http-routes.md, quickstart.md
**Target**: Cheaper LLM implementation — every task is self-contained with exact specs

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1-US5)

## User Story Mapping

| Story | Title | Priority | Spec Reference |
|-------|-------|----------|----------------|
| US1 | Navigate to a Page via URL | P1 | Routing + single entry point |
| US2 | View Base Layout with Sidebar and Header | P2 | Branded RTL layout |
| US3 | System Connects to the Database | P1 | PDO + parameterized queries |
| US4 | Session Persistence Across Pages | P2 | Sessions + 30-min timeout + CSRF |
| US5 | Graceful Error Display | P3 | Environment-aware error handling |

---

## Phase 1: Setup (Project Initialization)

**Purpose**: Create the full directory tree and configuration files. Nothing runs yet — just the skeleton.

- [x] T001 Create the complete project directory structure by running these mkdir commands from the project root: `mkdir -p public/css public/js app/core app/controllers app/models app/views/layouts app/views/components app/views/errors app/views/home config storage/logs storage/uploads database/migrations`

- [x] T002 [P] Create `.gitignore` at project root with these entries: `config/database.php`, `storage/logs/*.log`, `storage/uploads/*`, `!storage/uploads/.gitkeep`, `!storage/logs/.gitkeep`, `.DS_Store`, `Thumbs.db`, `*.swp`. Also create empty `.gitkeep` files inside `storage/logs/` and `storage/uploads/` so Git tracks the empty directories.

- [x] T003 [P] Create `public/.htaccess` for Apache URL rewriting. Contents: `RewriteEngine On`, `RewriteCond %{REQUEST_FILENAME} !-f` (skip if real file), `RewriteCond %{REQUEST_FILENAME} !-d` (skip if real directory), `RewriteRule ^(.*)$ index.php [QSA,L]` (send everything else to index.php). This ensures all requests go through the single entry point.

---

## Phase 2: Foundational (Core Framework — BLOCKS All User Stories)

**Purpose**: Build the core MVC framework classes. Every user story depends on these.

**CRITICAL**: No user story work can begin until this phase is complete.

- [x] T004 Create `config/app.php` — return a PHP array with these keys: `'app_name' => 'Dayem ERP'`, `'environment' => 'development'` (change to `'production'` on deploy), `'base_url' => 'http://localhost'`, `'timezone' => 'Africa/Cairo'`, `'session_timeout' => 1800` (30 minutes in seconds), `'charset' => 'utf-8'`. The file should start with `<?php` and `return [...]`.

- [x] T005 [P] Create `config/database.example.php` — return a PHP array: `'host' => 'localhost'`, `'port' => 3306`, `'database' => 'dayem_erp'`, `'username' => 'root'`, `'password' => ''`, `'charset' => 'utf8mb4'`, `'collation' => 'utf8mb4_unicode_ci'`. Add a comment at top: "Copy this file to database.php and fill in your credentials". The file should start with `<?php` and `return [...]`.

- [x] T006 [P] Create `config/routes.php` — return a PHP array that maps HTTP method + path to controller + action. Format: `'GET /' => ['HomeController', 'index']`. Start with just the home route. Add a comment explaining the format: `'METHOD /path' => ['ControllerName', 'actionMethod']`. The file should start with `<?php` and `return [...]`.

- [x] T007 Create `app/core/App.php` — the application bootstrap class in namespace `App\Core`. This is the FIRST class that runs. Constructor must do these steps IN ORDER: (1) Load `config/app.php` into a property `$config`, (2) set timezone with `date_default_timezone_set($config['timezone'])`, (3) call `$this->registerAutoloader()`, (4) call `$this->initErrorHandler()`, (5) call `$this->initSession()`, (6) call `$this->dispatch()`. Method `registerAutoloader()`: use `spl_autoload_register()` to map namespace `App\` to the `app/` directory. Convert namespace separators `\` to directory separators `/`, convert to lowercase directory path. Example: `App\Core\Router` maps to `app/core/Router.php`, `App\Controllers\HomeController` maps to `app/controllers/HomeController.php`. Method `initErrorHandler()`: create instance of `App\Core\ErrorHandler` passing `$this->config['environment']`. Method `initSession()`: create instance of `App\Core\Session` passing `$this->config['session_timeout']`. Method `dispatch()`: create `App\Core\Router` instance, load routes from `config/routes.php`, call `$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'])`.

- [x] T008 Create `app/core/Router.php` — class in namespace `App\Core`. Properties: `$routes` (array). Method `addRoutes(array $routes)`: store the route map. Method `dispatch(string $method, string $uri)`: (1) Strip query string from URI with `parse_url($uri, PHP_URL_PATH)`, (2) Remove trailing slash (except for `/` itself), (3) Build lookup key as `"$method $path"`, (4) If key exists in `$routes`, extract `[$controllerName, $action]`, build full class `"App\\Controllers\\$controllerName"`, instantiate it, call `$controller->$action()`, (5) If key NOT found, set `http_response_code(404)` and instantiate `App\Controllers\HomeController` then call `$controller->notFound()`. Support route parameters: if a route contains `:param` like `GET /users/:id`, match against incoming URI segments and pass extracted values to the action method.

- [x] T009 [P] Create `app/core/Database.php` — singleton class in namespace `App\Core`. Private static property `$instance` (PDO object, initially null). Private constructor (prevents external instantiation). Public static method `getInstance()`: if `$instance` is null, load `config/database.php` into `$dbConfig`, build DSN string `"mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}"`, create new `PDO($dsn, $dbConfig['username'], $dbConfig['password'])`, set attributes: `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`, `PDO::ATTR_EMULATE_PREPARES => false`. Store in `$instance`. Return `$instance`. Wrap the PDO creation in try-catch: on failure, throw a new `\RuntimeException("Database connection failed: " . $e->getMessage())`.

- [x] T010 [P] Create `app/core/Controller.php` — base controller class in namespace `App\Core`. All controllers extend this. Method `render(string $view, array $data = [])`: (1) Extract `$data` into variables with `extract($data)`, (2) Start output buffering with `ob_start()`, (3) Require the view file: `require app/views/{$view}.php` (build path relative to project root), (4) Capture buffer into `$content = ob_get_clean()`, (5) Require the layout file `app/views/layouts/main.php` which will echo `$content` in its content area. Method `renderWithoutLayout(string $view, array $data = [])`: same as render but skip the layout (for error pages that might not use layout). Method `redirect(string $url)`: set `header("Location: $url")` and `exit`. Method `json(array $data, int $status = 200)`: set `http_response_code($status)`, set `header('Content-Type: application/json; charset=utf-8')`, echo `json_encode($data)`, exit. Property `$csrfToken`: getter that returns `$_SESSION['csrf_token'] ?? ''`. Method `validateCsrf()`: compare `$_POST['_csrf_token'] ?? ''` with `$_SESSION['csrf_token'] ?? ''`. If mismatch, set `http_response_code(403)` and render an error page with Arabic message "طلب غير مصرح به" (Unauthorized request), then exit.

- [x] T011 [P] Create `app/core/Model.php` — base model class in namespace `App\Core`. All models extend this. Property `$db` (PDO instance). Constructor: `$this->db = Database::getInstance()`. Method `query(string $sql, array $params = [])`: prepare statement with `$this->db->prepare($sql)`, execute with `$params`, return the PDOStatement. Method `findAll(string $table, string $conditions = '', array $params = [])`: build `SELECT * FROM $table` + optional `WHERE $conditions`, call `$this->query()`, return `->fetchAll()`. Method `findOne(string $table, string $conditions, array $params = [])`: same but return `->fetch()`. Method `insert(string $table, array $data)`: build `INSERT INTO $table (columns) VALUES (:placeholders)` from `$data` keys, execute, return `$this->db->lastInsertId()`. Method `update(string $table, array $data, string $conditions, array $params = [])`: build `UPDATE $table SET col=:col WHERE $conditions`, merge `$data` with `$params`, execute. Method `delete(string $table, string $conditions, array $params = [])`: build `DELETE FROM $table WHERE $conditions`, execute. Note: ALL queries MUST use prepared statements — never concatenate user input into SQL strings.

- [x] T012 Create `app/core/Session.php` — session management class in namespace `App\Core`. Constructor takes `int $timeout = 1800`. Must do: (1) Set `ini_set('session.cookie_httponly', '1')`, `ini_set('session.use_strict_mode', '1')`, `ini_set('session.cookie_samesite', 'Lax')`, (2) Call `session_start()`, (3) Check timeout: if `$_SESSION['last_activity']` exists and `time() - $_SESSION['last_activity'] > $timeout`, call `session_unset()`, `session_destroy()`, `session_start()` (expired session), (4) Set `$_SESSION['last_activity'] = time()`, (5) Generate CSRF token if not exists: if `!isset($_SESSION['csrf_token'])`, set `$_SESSION['csrf_token'] = bin2hex(random_bytes(32))`. Public method `get(string $key, $default = null)`: return `$_SESSION[$key] ?? $default`. Method `set(string $key, $value)`: `$_SESSION[$key] = $value`. Method `destroy()`: `session_unset()`, `session_destroy()`. Method `getCsrfToken()`: return `$_SESSION['csrf_token']`. Method `regenerate()`: call `session_regenerate_id(true)`.

- [x] T013 Create `app/core/ErrorHandler.php` — error handling class in namespace `App\Core`. Constructor takes `string $environment = 'development'`. Must: (1) Store environment, (2) Register `set_error_handler([$this, 'handleError'])`, (3) Register `set_exception_handler([$this, 'handleException'])`, (4) Register `register_shutdown_function([$this, 'handleShutdown'])`. Method `handleError($errno, $errstr, $errfile, $errline)`: convert to ErrorException and throw it. Method `handleException(\Throwable $e)`: (1) Log the error (call `$this->logError()`), (2) If environment is `'development'`: display detailed HTML with error message, file, line, and stack trace styled with Tailwind classes, (3) If environment is `'production'`: set `http_response_code(500)` and require `app/views/errors/500.php`. Method `handleShutdown()`: get `error_get_last()`, if it's a fatal error (E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), call `handleException(new \ErrorException(...))`. Method `logError(\Throwable $e)`: (1) Try to insert into `error_logs` table via Database::getInstance() with fields: error_level, error_message, error_file, error_line, error_trace, request_url (`$_SERVER['REQUEST_URI']`), request_method (`$_SERVER['REQUEST_METHOD']`), user_agent (`$_SERVER['HTTP_USER_AGENT']`), ip_address (`$_SERVER['REMOTE_ADDR']`), (2) If DB logging fails (catch exception), fall back to writing to `storage/logs/error.log` using `error_log()` with timestamp.

- [x] T014 Create `public/index.php` — the single entry point. Contents: (1) `<?php`, (2) Define `ROOT_PATH` constant: `define('ROOT_PATH', dirname(__DIR__))` (this points to the project root, one level up from `public/`), (3) Require `app/core/App.php` manually (autoloader isn't registered yet), (4) Create new `App\Core\App()` instance. That's it — the App constructor handles everything else (autoloading, error handling, sessions, routing).

- [x] T015 Create `database/migrations/001_core_foundation.sql` — SQL file that creates two tables. Table 1 `system_settings`: `id INT AUTO_INCREMENT PRIMARY KEY`, `setting_key VARCHAR(100) NOT NULL UNIQUE`, `setting_value TEXT NULL`, `setting_group VARCHAR(50) NOT NULL DEFAULT 'general'`, `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`, `updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP`, `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`. Table 2 `error_logs`: `id INT AUTO_INCREMENT PRIMARY KEY`, `error_level VARCHAR(20) NOT NULL`, `error_message TEXT NOT NULL`, `error_file TEXT NULL`, `error_line INT NULL`, `error_trace LONGTEXT NULL`, `request_url TEXT NULL`, `request_method VARCHAR(10) NULL`, `user_agent TEXT NULL`, `ip_address VARCHAR(45) NULL`, `created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`, `ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`. After table creation, INSERT seed data into `system_settings`: `('app_name', 'Dayem ERP', 'general')`, `('environment', 'development', 'general')`, `('session_timeout', '1800', 'general')`.

**Checkpoint**: Core framework is complete. Run `php -l` on each file to check for syntax errors. All files should parse without errors.

---

## Phase 3: User Story 1 — Navigate to a Page via URL (Priority: P1)

**Goal**: A user types a URL and the system routes them to the correct page. Invalid URLs show a 404 page.

**Independent Test**: Visit `http://localhost/` and see the home page content. Visit `http://localhost/nonexistent` and see an Arabic 404 page.

### Implementation

- [x] T016 [US1] Register the home route in `config/routes.php` — add entry `'GET /' => ['HomeController', 'index']` to the return array

- [x] T017 [US1] Create `app/controllers/HomeController.php` — class `HomeController` in namespace `App\Controllers`, extends `App\Core\Controller`. Method `index()`: call `$this->render('home/index', ['pageTitle' => 'Dayem ERP - الرئيسية'])` (page title in Arabic: "Dayem ERP - Home"). Method `notFound()`: call `$this->render('errors/404', ['pageTitle' => 'الصفحة غير موجودة'])` (Arabic: "Page not found"). The Router calls `notFound()` when no route matches.

- [x] T018 [US1] Create `app/views/home/index.php` — the home page view (content only, no HTML wrapper — the layout handles that). Content: a div with Tailwind classes, an `<h1>` saying "مرحبا بك في نظام دايم" (Welcome to Dayem System) styled with `text-2xl font-bold text-[#111111]`, a paragraph saying "نظام إدارة الموارد البشرية" (HR Management System) with `text-gray-600 mt-2`, and a card/box with a welcome message styled with `bg-white rounded-lg shadow p-6 mt-6`.

- [x] T019 [US1] Create `app/views/errors/404.php` — the 404 error page view (content only). Content: a centered div with Tailwind classes, a large "404" text styled `text-6xl font-bold text-[#F4C400]`, an Arabic heading "الصفحة غير موجودة" (Page not found) with `text-2xl font-bold text-[#111111] mt-4`, a paragraph "عذراً، الصفحة التي تبحث عنها غير موجودة" (Sorry, the page you are looking for does not exist) with `text-gray-600 mt-2`, and a link "العودة للرئيسية" (Back to home) pointing to `/` styled as a button with `bg-[#F4C400] text-[#111111] px-6 py-2 rounded mt-6 inline-block font-bold hover:bg-yellow-500`.

**Checkpoint**: Start XAMPP, visit `http://localhost/`. You should see the home page content (unstyled, no layout yet — that's US2). Visit `http://localhost/xyz` and see the 404 page.

---

## Phase 4: User Story 2 — View Base Layout with Sidebar and Header (Priority: P2)

**Goal**: Every page renders inside a consistent branded layout with RTL Arabic direction, Dayem colors (yellow #F4C400, black #111111), a header with logo/name, and a sidebar with navigation links.

**Independent Test**: Load any page and verify: HTML has `lang="ar" dir="rtl"`, header shows "دايم" branding with yellow background, sidebar has Arabic navigation links to all planned modules, content area displays the page-specific content.

### Implementation

- [x] T020 [US2] Create `app/views/layouts/main.php` — the base HTML layout template. This file receives `$content` (the page-specific HTML) and `$pageTitle` from the controller's `render()` method. Structure: `<!DOCTYPE html>`, `<html lang="ar" dir="rtl">`, `<head>` with `<meta charset="utf-8">`, `<meta name="viewport" content="width=device-width, initial-scale=1.0">`, `<title><?= htmlspecialchars($pageTitle ?? 'Dayem ERP') ?></title>`, `<script src="https://cdn.tailwindcss.com"></script>` (Tailwind CDN), Google Fonts link for "Cairo" font (Arabic font: `https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap`), `<style>body { font-family: 'Cairo', sans-serif; }</style>`, CSRF meta tag `<meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">`. `<body class="bg-gray-100 min-h-screen">`: a flex container `<div class="flex min-h-screen">`, then `<?php require ROOT_PATH . '/app/views/components/sidebar.php'; ?>`, then a main content wrapper `<div class="flex-1 flex flex-col">`, `<?php require ROOT_PATH . '/app/views/components/header.php'; ?>`, `<main class="flex-1 p-6"><?= $content ?></main>`, close divs. `</body></html>`.

- [x] T021 [P] [US2] Create `app/views/components/header.php` — the top header bar. A `<header>` element with `class="bg-[#F4C400] shadow-md px-6 py-3 flex items-center justify-between"`. Left side (which is RIGHT in RTL): the app name `<h1 class="text-xl font-bold text-[#111111]">نظام دايم</h1>` (Dayem System). Right side (LEFT in RTL): a placeholder for user info/logout that will be used by Phase 2 Auth: `<div class="text-[#111111] text-sm">مرحبا</div>` (Welcome). The header should be sticky or fixed at the top: add `sticky top-0 z-10` classes.

- [x] T022 [P] [US2] Create `app/views/components/sidebar.php` — the navigation sidebar. A `<nav>` element with `class="w-64 bg-[#111111] min-h-screen text-white flex-shrink-0"`. Inside: a logo/title section at top `<div class="p-4 border-b border-gray-700"><h2 class="text-lg font-bold text-[#F4C400]">دايم ERP</h2></div>`. Then a `<ul class="mt-4 space-y-1">` with navigation links to ALL planned modules. Each link is a `<li>` with an `<a>` inside, styled `class="block px-4 py-3 text-gray-300 hover:bg-gray-800 hover:text-[#F4C400] transition-colors"`. Links (Arabic text, href, module): "الرئيسية" (Home, `/`), "تسجيل الدخول" (Login, `/auth/login`), "الأقسام" (Departments, `/departments`), "الموظفين" (Employees, `/employees`), "المهام" (Tasks, `/tasks`), "التذاكر" (Tickets, `/tickets`), "الرواتب" (Salary, `/salary`), "الملفات" (Files, `/files`), "السجلات" (Logs, `/logs`), "لوحة التحكم" (Dashboard, `/dashboard`). The active page link should be highlighted — add an `$activePage` variable check: if `$activePage === 'home'` add classes `bg-gray-800 text-[#F4C400]`.

- [x] T023 [US2] Update `app/core/Controller.php` render method — make sure `render()` passes an `$activePage` variable to the layout. In the `render()` method, before requiring the layout, also make the page title available: `$pageTitle = $data['pageTitle'] ?? 'Dayem ERP'`. The layout file should have access to both `$content` and `$pageTitle`. Also extract `$activePage` from `$data` with a default value of `''`.

- [x] T024 [US2] Update `app/controllers/HomeController.php` — update the `index()` method to pass `'activePage' => 'home'` in the data array so the sidebar highlights the home link. Update `notFound()` to also pass `'activePage' => ''`.

- [x] T025 [P] [US2] Create `app/views/components/button.php` — reusable button component. Create a PHP function or includable partial. The file defines CSS classes for Dayem-styled buttons: primary button `bg-[#F4C400] text-[#111111] font-bold py-2 px-4 rounded hover:bg-yellow-500 transition-colors`, secondary button `bg-gray-200 text-[#111111] font-bold py-2 px-4 rounded hover:bg-gray-300 transition-colors`, danger button `bg-red-600 text-white font-bold py-2 px-4 rounded hover:bg-red-700 transition-colors`. Create a helper function `renderButton($text, $type = 'primary', $attrs = '')` that outputs the `<button>` HTML with correct classes.

- [x] T026 [P] [US2] Create `app/views/components/modal.php` — reusable modal component. A hidden overlay `<div id="modal-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">` with a modal box inside `<div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4">`. Include a title area, content area, and close button. Add a small JS snippet at the bottom: functions `openModal(title, content)` and `closeModal()` that toggle the `hidden` class. Style the close button with an X icon in the top-left corner (RTL-aware: use `start-4` instead of `left-4`).

- [x] T027 [P] [US2] Create `app/views/components/table.php` — reusable table component. A helper function `renderTable(array $headers, array $rows, array $options = [])` that generates: `<div class="overflow-x-auto">`, `<table class="w-full bg-white rounded-lg shadow">`, `<thead class="bg-[#F4C400]">` with `<th class="px-4 py-3 text-[#111111] font-bold text-right">` for each header (text-right for RTL), `<tbody>` with alternating row colors `odd:bg-white even:bg-gray-50`, each `<td class="px-4 py-3 border-t text-right">`. Support empty state: if `$rows` is empty, show a full-width cell with "لا توجد بيانات" (No data available).

- [x] T028 [P] [US2] Create `app/views/components/form.php` — reusable form helpers. Functions: `renderInput($name, $label, $type = 'text', $value = '', $required = false)` — generates a `<div class="mb-4">` with `<label class="block text-sm font-bold text-[#111111] mb-1">` and `<input class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#F4C400]">`. `renderSelect($name, $label, $options, $selected = '')` — same wrapper with a `<select>` element. `renderTextarea($name, $label, $value = '', $rows = 4)` — same with `<textarea>`. All inputs include RTL-appropriate text alignment (text-right is default due to `dir="rtl"`). `renderCsrfField()` — outputs `<input type="hidden" name="_csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">`.

**Checkpoint**: Visit `http://localhost/`. You should see the full Dayem-branded layout: yellow header with "نظام دايم", black sidebar with Arabic navigation links, and the home page content in the main area. Everything should be RTL. Visit `/xyz` and see 404 within the same layout.

---

## Phase 5: User Story 3 — System Connects to the Database (Priority: P1)

**Goal**: The system connects to MySQL via PDO and can read/write data. Connection failures show a friendly message.

**Independent Test**: Create `config/database.php` from the example, import the migration SQL, and load a page that queries the database. Verify data displays. Then break the DB credentials and verify a graceful error (not a raw PHP error).

### Implementation

- [x] T029 [US3] Create `app/models/SettingModel.php` — class `SettingModel` in namespace `App\Models`, extends `App\Core\Model`. Methods: `getAll()` — return `$this->findAll('system_settings')`. `getByKey(string $key)` — return `$this->findOne('system_settings', 'setting_key = :key', ['key' => $key])`. `getByGroup(string $group)` — return `$this->findAll('system_settings', 'setting_group = :group', ['group' => $group])`. `updateSetting(string $key, string $value)` — call `$this->update('system_settings', ['setting_value' => $value], 'setting_key = :key', ['key' => $key])`. `createSetting(string $key, string $value, string $group = 'general')` — call `$this->insert('system_settings', ['setting_key' => $key, 'setting_value' => $value, 'setting_group' => $group])`.

- [x] T030 [US3] Create `app/models/ErrorLogModel.php` — class `ErrorLogModel` in namespace `App\Models`, extends `App\Core\Model`. Methods: `logError(array $data)` — call `$this->insert('error_logs', $data)` with keys: error_level, error_message, error_file, error_line, error_trace, request_url, request_method, user_agent, ip_address. `getRecent(int $limit = 50)` — return `$this->query("SELECT * FROM error_logs ORDER BY created_at DESC LIMIT :limit", ['limit' => $limit])->fetchAll()`. `getByLevel(string $level)` — return `$this->findAll('error_logs', 'error_level = :level', ['level' => $level])`.

- [x] T031 [US3] Update `app/core/ErrorHandler.php` — modify the `logError()` method to use `ErrorLogModel` instead of raw PDO. In `logError(\Throwable $e)`: try to create a new `App\Models\ErrorLogModel` and call `$model->logError([...])`. Keep the file-based fallback in the catch block (write to `storage/logs/error.log`). This connects error handling to the database.

- [x] T032 [US3] Update `app/controllers/HomeController.php` — in the `index()` method, load the app name from the database to prove the connection works. Create a `SettingModel` instance, call `$setting = $model->getByKey('app_name')`, pass `'appNameFromDb' => $setting['setting_value'] ?? 'Dayem ERP'` to the view data. Update `app/views/home/index.php` to display this value (e.g., `<p class="text-sm text-gray-500 mt-2">تم التحميل من قاعدة البيانات: <?= htmlspecialchars($appNameFromDb) ?></p>` — "Loaded from database: Dayem ERP").

**Checkpoint**: Import `001_core_foundation.sql` into MySQL. Copy `config/database.example.php` to `config/database.php` and fill credentials. Visit `/`. You should see the home page with the app name loaded from the database. Change DB password to something wrong — you should see a graceful error page, not a raw PHP error.

---

## Phase 6: User Story 4 — Session Persistence Across Pages (Priority: P2)

**Goal**: Sessions persist across page navigations. After 30 minutes of inactivity, the session expires. CSRF tokens are generated and available for forms.

**Independent Test**: Open browser, visit `/`, check developer tools (Application > Cookies) for a PHPSESSID cookie. Navigate to another page — session persists. Open a new private window — different session. CSRF token present in page source.

### Implementation

- [x] T033 [US4] Verify `app/core/Session.php` integration — the Session class (created in T012) is already initialized by `App.php` (T007). Verify the chain works: `App.__construct()` -> `initSession()` -> `new Session(1800)` -> `session_start()` + timeout check + CSRF token generation. No new code needed if T007 and T012 are correct — this task is a verification/integration check.

- [x] T034 [US4] Update `app/views/layouts/main.php` — verify that the CSRF meta tag is in the `<head>`: `<meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">`. This makes the CSRF token available to JavaScript for AJAX requests. Also add a small JS snippet before `</body>`: `<script>window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;</script>`.

- [x] T035 [US4] Create `public/js/app.js` — base JavaScript file included in the layout. Contents: (1) Read CSRF token from meta tag: `const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || ''`, (2) Create a helper function `async function fetchApi(url, options = {})` that wraps fetch and automatically adds the CSRF token to POST/PUT/DELETE requests: if `options.method` is not GET, add `'X-CSRF-Token': csrfToken` to headers and `_csrf_token` to the body (if FormData, append it; if JSON, add to object). (3) Create `function showNotification(message, type = 'success')` that shows a toast notification div (positioned `fixed top-4 left-4 z-50` for RTL) with appropriate colors (success: green, error: red, info: blue), auto-hides after 3 seconds.

- [x] T036 [US4] Update `app/views/layouts/main.php` — add `<script src="/js/app.js"></script>` before the closing `</body>` tag, AFTER the csrfToken script.

- [x] T037 [US4] Create `app/views/errors/session-expired.php` — a view for session expiry. Content: centered div with Arabic text "انتهت الجلسة" (Session expired) as heading, "لقد انتهت جلستك بسبب عدم النشاط. يرجى تسجيل الدخول مرة أخرى" (Your session has expired due to inactivity. Please log in again) as paragraph, and a button "تسجيل الدخول" (Log in) linking to `/auth/login`. Styled with Dayem colors.

**Checkpoint**: Visit the app, open DevTools > Application > Cookies. Verify PHPSESSID exists. View page source — CSRF token should be in a meta tag. Navigate between `/` and `/nonexistent` — session cookie stays the same (session persists).

---

## Phase 7: User Story 5 — Graceful Error Display (Priority: P3)

**Goal**: In production mode, errors show a friendly Arabic page. In development mode, errors show detailed debug info. All errors are logged to the database.

**Independent Test**: Set environment to `'development'` in `config/app.php`, trigger an error (e.g., call undefined function in a controller) — see detailed trace. Set to `'production'` — see Arabic "حدث خطأ" page. Check `error_logs` table — error is recorded in both cases.

### Implementation

- [x] T038 [US5] Create `app/views/errors/500.php` — the production error page view (content only, rendered within layout). Content: centered div, large icon or "!" symbol styled `text-6xl text-[#F4C400]`, heading "حدث خطأ" (An error occurred) with `text-2xl font-bold text-[#111111] mt-4`, paragraph "عذراً، حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى لاحقاً" (Sorry, an unexpected error occurred. Please try again later) with `text-gray-600 mt-2`, a button "العودة للرئيسية" (Back to home) linking to `/` styled with `bg-[#F4C400] text-[#111111] px-6 py-2 rounded mt-6 inline-block font-bold hover:bg-yellow-500`. NO technical details shown — no file paths, no line numbers, no stack traces.

- [x] T039 [US5] Create `app/views/errors/403.php` — the forbidden/CSRF error page view. Content: centered div, "403" text styled `text-6xl font-bold text-[#F4C400]`, heading "غير مصرح" (Unauthorized) with `text-2xl font-bold text-[#111111] mt-4`, paragraph "ليس لديك صلاحية للوصول إلى هذه الصفحة" (You do not have permission to access this page) with `text-gray-600 mt-2`, back-to-home button same as 404 and 500 pages.

- [x] T040 [US5] Update `app/core/ErrorHandler.php` — improve the development mode error display. In `handleException()` when environment is `'development'`, render a well-styled debug page (NOT using the layout, use `renderWithoutLayout` or direct output): `<!DOCTYPE html><html lang="ar" dir="rtl">`, include Tailwind CDN, show: red banner with error class name and message, file + line number in a code-like box `bg-gray-900 text-green-400 p-4 rounded font-mono text-sm`, full stack trace in a pre-formatted block, request info (URL, method, GET/POST params). Style with Tailwind so it's readable. This replaces raw PHP error output with a developer-friendly debug page.

- [x] T041 [US5] Update `app/core/ErrorHandler.php` — in the production `handleException()` path, before rendering 500.php, check if the layout is available and render the error page WITH the layout (so the header/sidebar still show). Wrap the require in a try-catch: if the layout itself fails, fall back to a minimal HTML error page without layout.

**Checkpoint**: Set `config/app.php` environment to `'development'`. Add `undefinedFunction();` to `HomeController::index()`. Visit `/` — see styled debug page with error details. Remove the error. Set environment to `'production'`. Add the error back. Visit `/` — see the Arabic "حدث خطأ" page with NO technical details. Check phpMyAdmin > `error_logs` table — the error should be recorded with full details.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Final cleanup and verification across all stories

- [x] T042 Review all PHP files for syntax errors — run `find app/ config/ public/ -name "*.php" -exec php -l {} \;` from project root. Fix any parse errors.

- [x] T043 Verify all files use `<?php` opening tag with NO closing `?>` tag (PHP best practice — prevents accidental whitespace output). Check: `app/core/*.php`, `app/controllers/*.php`, `app/models/*.php`. Config files (`config/*.php`) that use `return` should also omit closing tags.

- [x] T044 Verify `htmlspecialchars()` is used on ALL user-facing output in views. Check every `<?= ... ?>` in `app/views/` — any dynamic value must be wrapped in `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`. This prevents XSS attacks.

- [x] T045 [P] Create `public/css/custom.css` — any custom CSS overrides that Tailwind CDN doesn't handle. Include: scrollbar styling for RTL, print styles, any brand-specific overrides. Link it in the layout `<head>` after the Tailwind CDN script.

- [x] T046 Run the quickstart.md verification checklist — follow every step in `specs/001-core-system-foundation/quickstart.md` and confirm each checkbox passes: home page loads, RTL works, brand colors visible, 404 page works, error handling works, DB connection works, sessions persist.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 completion — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Phase 2 — can run in parallel with US3
- **US2 (Phase 4)**: Depends on Phase 2 — can run in parallel with US1/US3 but best after US1 (US1 creates the views that US2 wraps in layout)
- **US3 (Phase 5)**: Depends on Phase 2 — can run in parallel with US1
- **US4 (Phase 6)**: Depends on Phase 2 and US2 (layout must exist for CSRF meta tag)
- **US5 (Phase 7)**: Depends on Phase 2 and US3 (error logging needs DB)
- **Polish (Phase 8)**: Depends on all user stories complete

### User Story Dependencies

```text
Phase 1 (Setup)
    │
    ▼
Phase 2 (Foundational) ──── BLOCKS ALL ────┐
    │                                       │
    ├──► Phase 3 (US1: Routing) ◄──────────┤
    │         │                             │
    ├──► Phase 5 (US3: Database) ◄─────────┤
    │         │                             │
    ├──► Phase 4 (US2: Layout) ◄───── needs US1 views
    │         │
    ├──► Phase 6 (US4: Sessions) ◄── needs US2 layout
    │         │
    └──► Phase 7 (US5: Errors) ◄──── needs US3 DB
              │
              ▼
        Phase 8 (Polish)
```

### Within Each User Story

- Models/entities first
- Controllers/services next
- Views/UI last
- Integration/wiring final

### Parallel Opportunities

**Phase 1** (all 3 tasks can run in parallel after T001):
- T002 (.gitignore) and T003 (.htaccess) are [P]

**Phase 2** (4 parallel tracks after T004):
- T005 (database.example.php), T006 (routes.php), T009 (Database.php), T010 (Controller.php), T011 (Model.php) are all [P]
- T007 (App.php) and T008 (Router.php) depend on each other
- T012 (Session.php) and T013 (ErrorHandler.php) are independent

**Phase 4** (3 parallel UI components):
- T021 (header), T022 (sidebar), T025-T028 (button, modal, table, form) are all [P]

---

## Parallel Example: Phase 2 Foundational

```text
# Track A (can all start immediately after T004):
T005 config/database.example.php
T006 config/routes.php
T009 app/core/Database.php
T010 app/core/Controller.php
T011 app/core/Model.php

# Track B (sequential):
T007 app/core/App.php → T008 app/core/Router.php → T014 public/index.php

# Track C (independent):
T012 app/core/Session.php
T013 app/core/ErrorHandler.php

# Track D (independent):
T015 database/migrations/001_core_foundation.sql
```

---

## Implementation Strategy

### MVP First (US1 Only)

1. Complete Phase 1: Setup (T001-T003)
2. Complete Phase 2: Foundational (T004-T015)
3. Complete Phase 3: US1 Routing (T016-T019)
4. **STOP and VALIDATE**: Home page loads at `/`, 404 works at `/xyz`
5. This is the minimum viable proof that the MVC framework works

### Incremental Delivery

1. Setup + Foundational → Framework skeleton ready
2. Add US1 (Routing) → Pages can be served
3. Add US2 (Layout) → Pages look branded and professional
4. Add US3 (Database) → Data can be stored and read
5. Add US4 (Sessions) → State persists, CSRF protection active
6. Add US5 (Errors) → Robust error handling complete
7. Polish → Production-ready foundation
8. Each increment adds value without breaking previous work

---

## Notes

- Every task includes the EXACT file path and detailed implementation spec
- All PHP classes use namespace `App\*` mapped to `app/*` directory
- Database naming: snake_case. PHP naming: camelCase. Both enforced throughout.
- All user-facing text is Arabic. All layouts are RTL.
- Brand colors: Yellow #F4C400, Black #111111 — used consistently
- No Composer, no npm — zero external build dependencies
- Tailwind CSS via CDN for development, pre-built CSS for production
- CSRF protection infrastructure is in place from Phase 2 but actively used from US4
- Total: 46 tasks across 8 phases
