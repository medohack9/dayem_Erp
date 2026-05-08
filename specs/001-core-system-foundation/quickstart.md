# Quickstart: Core System Foundation

**Feature**: 001-core-system-foundation
**Date**: 2026-04-23

---

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.3+
- XAMPP (or equivalent local server with Apache + PHP + MySQL)
- Web browser (Chrome, Firefox, or Edge)
- Git

---

## Setup Steps

### 1. Clone the repository

```
git clone <repository-url> dayem-erp
cd dayem-erp
```

### 2. Configure the database

- Open your MySQL client (phpMyAdmin via XAMPP, or MySQL CLI)
- Create a new database named `dayem_erp` with character set `utf8mb4` and collation `utf8mb4_unicode_ci`
- Copy `config/database.example.php` to `config/database.php`
- Update the database credentials in `config/database.php` (host, database name, username, password)

### 3. Run the database migration

- Import the SQL file from `database/migrations/001_core_foundation.sql` into the `dayem_erp` database
- This creates the `system_settings` and `error_logs` tables

### 4. Configure Apache

- Point your Apache virtual host (or XAMPP htdocs) to the `public/` directory
- Ensure `mod_rewrite` is enabled in Apache
- The `.htaccess` file in `public/` handles URL rewriting to `index.php`

### 5. Set environment mode

- Open `config/app.php`
- Set `'environment'` to `'development'` for local work or `'production'` for deployment

### 6. Access the application

- Open your browser and navigate to `http://localhost/` (or your configured virtual host)
- You should see the Dayem ERP home page with the branded header, sidebar, and Arabic RTL layout

---

## Verification Checklist

After setup, verify these work:

- [ ] Home page loads at `/` with the base layout (header + sidebar + content)
- [ ] Layout is RTL with Arabic text
- [ ] Brand colors visible (yellow #F4C400 header/accents, black #111111 text)
- [ ] Visiting a non-existent URL (e.g., `/nonexistent`) shows the Arabic 404 error page
- [ ] Triggering an error in development mode shows detailed error information
- [ ] Database connection works (no errors on pages that query the database)
- [ ] Session persists across page navigations (verify via browser dev tools → Application → Cookies)

---

## Common Issues

| Problem | Solution |
|---------|----------|
| Blank page or 500 error | Check `storage/logs/error.log` for details; verify `config/database.php` credentials |
| 404 on all pages | Ensure Apache `mod_rewrite` is enabled and `.htaccess` is being read (check `AllowOverride All`) |
| Arabic text not displaying | Verify `<html lang="ar" dir="rtl">` is set; check that the font supports Arabic glyphs |
| Session not persisting | Check PHP session save path is writable; verify `session.cookie_httponly` is set |
| Database connection refused | Verify MySQL is running; check host/port/credentials in `config/database.php` |
