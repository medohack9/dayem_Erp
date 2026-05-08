# HTTP Route Contracts: Core System Foundation

**Feature**: 001-core-system-foundation
**Date**: 2026-04-23

This document defines the HTTP routes exposed by the Core System Foundation phase. These routes establish the routing infrastructure and serve the base layout.

---

## Routes

### GET /

**Description**: Home/landing page of the ERP system.

**Response**: HTML page rendered inside the base layout (header + sidebar + content area) with a welcome/dashboard placeholder.

**Status codes**:
- `200 OK` — Page rendered successfully

**Notes**: This is the default route that proves the full MVC pipeline works end-to-end. Content area shows a welcome message in Arabic.

---

### GET /404 (fallback)

**Description**: Displayed when any requested URL does not match a defined route.

**Response**: HTML error page rendered inside the base layout with a user-friendly Arabic "page not found" message.

**Status codes**:
- `404 Not Found` — Route not recognized

**Notes**: The Router dispatches to this view when no route matches. The page uses the same base layout (header + sidebar) for visual consistency.

---

### GET /500 (internal)

**Description**: Displayed when an unhandled server error occurs in production mode.

**Response**: HTML error page with a user-friendly Arabic "something went wrong" message. No technical details exposed.

**Status codes**:
- `500 Internal Server Error` — Unhandled exception

**Notes**: Only shown in production environment. In development, the error handler displays detailed debug information instead.

---

## CSRF Contract

All future routes that accept POST, PUT, or DELETE requests MUST include a CSRF token in the request body or headers.

**Token delivery**: A hidden form field `_csrf_token` is included in every form rendered by the view layer.

**Token validation**: The base Controller validates `$_POST['_csrf_token']` against `$_SESSION['csrf_token']` before processing any state-changing request. Mismatches result in a `403 Forbidden` response.

**Token format**: 64-character hex string generated via `bin2hex(random_bytes(32))`.

---

## Response Format Contract

### HTML Responses (server-rendered pages)

All HTML responses follow this structure:
- `Content-Type: text/html; charset=utf-8`
- `<html lang="ar" dir="rtl">` root element
- Base layout wrapper (header + sidebar + content area)
- Brand colors: primary yellow (#F4C400), secondary black (#111111)

### JSON Responses (AJAX endpoints — future phases)

When AJAX endpoints are introduced in future phases, they will follow:
- `Content-Type: application/json; charset=utf-8`
- Standard envelope: `{"success": true/false, "data": {...}, "message": "..."}`
- Arabic message strings for user-facing messages
- HTTP status codes aligned with operation result (200, 201, 400, 401, 403, 404, 500)

**Notes**: Phase 1 establishes the HTML response contract only. The JSON contract is documented here for forward compatibility so future modules adopt a consistent pattern from the start.
