# Quickstart: Authentication

**Feature**: 002-authentication
**Date**: 2026-04-25

---

## Prerequisites

- Phase 1 (Core System Foundation) must be fully implemented and working
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- XAMPP (or equivalent local server with Apache + PHP + MySQL)
- Web browser (Chrome, Firefox, or Edge)
- Git

---

## Setup Steps

### 1. Switch to the authentication branch

```
git checkout 002-authentication
```

### 2. Run the database migration

- Open your MySQL client (phpMyAdmin via XAMPP, or MySQL CLI)
- Select the `dayem_erp` database
- Import the SQL file from `database/migrations/002_authentication.sql`
- This creates the `users`, `auth_logs`, and `user_sessions` tables and seeds the admin account

### 3. Verify the admin account

- Default admin credentials:
  - **Email**: `admin@dayem.com`
  - **Password**: `Dayem@2026`
- The password is stored as a bcrypt hash in the database

### 4. Verify configuration

- Ensure `config/database.php` has correct credentials (from Phase 1)
- The authentication system uses the existing session and error handling infrastructure

### 5. Access the login page

- Open your browser and navigate to `http://localhost/auth/login`
- You should see the login page with Arabic RTL layout and Dayem branding

### 6. Test login

- Enter `admin@dayem.com` and `Dayem@2026`
- Click the login button
- You should be redirected to the home page with the sidebar showing all modules (admin view)
- The header should display "مرحبا، [admin name]"

---

## Verification Checklist

After setup, verify these work:

- [ ] Login page loads at `/auth/login` with Arabic RTL layout and Dayem branding
- [ ] Successful login with email redirects to home page
- [ ] Successful login with phone number redirects to home page
- [ ] Invalid credentials show generic Arabic error "بيانات الدخول غير صحيحة"
- [ ] Empty form fields show Arabic validation errors
- [ ] Admin user sees all 9 sidebar links after login
- [ ] Logout button visible in header after login; clicking it destroys session and redirects to login
- [ ] After logout, protected pages redirect to login page
- [ ] Navigating to `/auth/login` while authenticated redirects to home page
- [ ] Unauthenticated access to `/` redirects to `/auth/login`
- [ ] Account lockout: after 5 failed attempts, login is blocked with "الحساب مقفل مؤقتاً" message
- [ ] After 15 minutes, locked account can be logged into again
- [ ] Session expires after 30 minutes of inactivity
- [ ] Login from a second browser invalidates the first session (single-session enforcement)
- [ ] CSRF token is present in the login form and validates on submission

---

## Common Issues

| Problem | Solution |
|---------|----------|
| Login page shows 404 | Ensure `/auth/login` route exists in `config/routes.php` and `.htaccess` is working |
| "بيانات الدخول غير صحيحة" with correct credentials | Verify the database has the admin seed and `password_verify()` is used (not `==`) |
| Redirect loop between login and home | Check that `AuthMiddleware` correctly identifies authenticated users by `$_SESSION['user_id']` |
| Session not persisting | Verify PHP session save path is writable; check `session.cookie_httponly` and `session.cookie_samesite` settings |
| Account lockout not expiring | Check that `locked_until` column is being compared with `NOW()` correctly; MySQL timezone should match PHP timezone |
| CSRF token mismatch on login | Ensure `$_SESSION['csrf_token']` is being included in the AJAX request body as `_csrf_token` |
| Can't access protected pages after login | Verify `user_sessions` table has a row for your session ID; check AuthMiddleware session validation logic |