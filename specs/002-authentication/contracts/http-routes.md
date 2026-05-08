# HTTP Route Contracts: Authentication

**Feature**: 002-authentication
**Date**: 2026-04-25

This document defines the HTTP routes exposed by the Authentication module. These routes handle login, logout, and session management.

---

## Routes

### POST /auth/login

**Description**: Authenticate a user with email/phone and password. Returns JSON response.

**Request**:
- Content-Type: `application/json`
- Body:
  ```json
  {
    "identifier": "admin@dayem.com",
    "password": "string",
    "_csrf_token": "string"
  }
  ```
- `identifier`: Required. Can be email address or phone number.
- `password`: Required. Minimum 8 characters.

**Response (Success)**:
- Status: `200 OK`
- Body:
  ```json
  {
    "success": true,
    "redirect": "/",
    "user": {
      "name": "مدير النظام",
      "role": "admin"
    }
  }
  ```

**Response (Invalid Credentials)**:
- Status: `200 OK` (intentional — prevents account enumeration via status codes)
- Body:
  ```json
  {
    "success": false,
    "message": "بيانات الدخول غير صحيحة"
  }
  ```

**Response (Account Locked)**:
- Status: `200 OK`
- Body:
  ```json
  {
    "success": false,
    "message": "الحساب مقفل مؤقتاً. حاول مرة أخرى بعد 15 دقيقة"
  }
  ```

**Response (Validation Error)**:
- Status: `422 Unprocessable Entity`
- Body:
  ```json
  {
    "success": false,
    "errors": {
      "identifier": "هذا الحقل مطلوب",
      "password": "كلمة المرور مطلوبة"
    }
  }
  ```

**Response (CSRF Token Missing/Invalid)**:
- Status: `403 Forbidden`
- Body:
  ```json
  {
    "success": false,
    "message": "طلب غير مصرح به"
  }
  ```

**Notes**: All failed login attempts are logged in `auth_logs` regardless of outcome. The `identifier` field is checked against `email` column first (if it contains `@`), then `phone` column. Password verification uses `password_verify()` with bcrypt.

---

### GET /auth/login

**Description**: Display the login page. If the user is already authenticated, redirect to `/`.

**Response**: HTML page rendered with the base layout.

**Status codes**:
- `200 OK` — Login page displayed (for unauthenticated users)
- `302 Found` — Redirect to `/` if user is already authenticated

**Notes**: The login page includes the CSRF token in a hidden form field and a meta tag for AJAX requests. The page is RTL Arabic.

---

### GET /auth/logout

**Description**: Destroy the current session, remove the session from `user_sessions`, and redirect to the login page.

**Response**:
- `302 Found` — Redirect to `/auth/login` with a flash message "تم تسجيل الخروج بنجاح" (Logged out successfully)

**Notes**: This route requires authentication. If called by an unauthenticated user, it redirects to `/auth/login` with no message.

---

## Authentication & Authorization Contract

### Route Protection Levels

All routes in the system fall into one of three protection levels:

| Level | Config | Who Can Access | Behavior if Unauthorized |
|-------|--------|---------------|------------------------|
| Public | `'auth' => false` or absent | Anyone (including unauthenticated) | N/A |
| Authenticated | `'auth' => true` | Any logged-in user (admin or employee) | Redirect to `/auth/login` |
| Role-restricted | `'auth' => true, 'roles' => ['admin']` | Only specified roles | 403 Forbidden page |

### Route Config Examples

```php
// Public route (login page)
'GET /auth/login' => ['AuthController', 'loginForm', 'auth' => false],

// Authenticated route (any logged-in user)
'GET /' => ['HomeController', 'index', 'auth' => true],

// Admin-only route
'GET /departments' => ['DepartmentController', 'index', 'auth' => true, 'roles' => ['admin']],

// Employee-accessible route (own data)
'GET /tasks' => ['TaskController', 'index', 'auth' => true, 'roles' => ['admin', 'employee']],
```

### Session Data Contract

Upon successful login, the following data is stored in `$_SESSION`:

```php
$_SESSION['user_id'] = $user['id'];           // int, users.id
$_SESSION['user_name'] = $user['name'];       // string, Arabic name
$_SESSION['user_email'] = $user['email'];     // string, email address
$_SESSION['user_role'] = $user['role'];       // string, 'admin' or 'employee'
$_SESSION['last_activity'] = time();           // int, Unix timestamp
$_SESSION['csrf_token'] = $token;             // string, 64-char hex (from Phase 1)
```

### Sidebar Navigation Contract

After authentication, the sidebar displays links based on role:

| Route | Label (Arabic) | Admin | Employee |
|-------|----------------|-------|----------|
| `/` | الرئيسية | ✓ | ✓ |
| `/departments` | الأقسام | ✓ | ✗ |
| `/employees` | الموظفين | ✓ | ✗ |
| `/tasks` | المهام | ✓ | ✓ (own only) |
| `/tickets` | التذاكر | ✓ | ✓ (own only) |
| `/salary` | الرواتب | ✓ | ✓ (own only) |
| `/files` | الملفات | ✓ | ✓ (own only) |
| `/logs` | السجلات | ✓ | ✗ |
| `/dashboard` | لوحة التحكم | ✓ | ✗ |

Employee sees only 5 links. Admin sees all 9. Backend enforcement ensures employees receive 403 on admin-only routes even if they navigate directly.

---

## CSRF Contract

All POST requests to `/auth/login` must include a valid CSRF token:
- Token is generated on session creation (Phase 1 infrastructure)
- Token is submitted in the JSON body as `_csrf_token`
- Token is validated server-side before processing the login attempt
- Invalid or missing tokens result in `403 Forbidden`

---

## Response Format Contract

### JSON Responses (AJAX endpoints)

All AJAX endpoints (currently `/auth/login` POST) follow:
- `Content-Type: application/json; charset=utf-8`
- Standard envelope: `{"success": true/false, "data": {...}, "message": "..."}`
- Arabic message strings for user-facing messages
- HTTP status codes aligned with operation result:
  - `200 OK` — Successful or expected-failure response (intentional — prevents status code enumeration)
  - `403 Forbidden` — CSRF token validation failure
  - `422 Unprocessable Entity` — Input validation errors with per-field messages

### HTML Responses (page renders)

All HTML authentication pages follow:
- `Content-Type: text/html; charset=utf-8`
- `<html lang="ar" dir="rtl">`
- Base layout from Phase 1 (header + sidebar)
- Brand colors: primary yellow (#F4C400), secondary black (#111111)
- Arabic text throughout