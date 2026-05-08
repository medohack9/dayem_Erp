# HTTP Route Contracts: Ticket System

**Feature**: 006-ticket-system
**Date**: 2026-04-29

All routes follow existing patterns: CSRF token required on all mutations, JSON request/response for CRUD, form rendering for pages.

## Page Routes

### GET /tickets
- **Auth**: Required (admin or employee)
- **Controller**: `TicketController::index`
- **Action**: Render ticket list page
- **Query Params**: `page`, `search`, `status`, `category`
- **Behavior**:
  - Admin: Shows all tickets from all employees, additional filter by employee name
  - Employee: Shows only tickets created by `Auth::id()`
- **Response**: HTML page with tickets, pagination, filters

### GET /tickets/create
- **Auth**: Required (employee only)
- **Controller**: `TicketController::createForm`
- **Action**: Render create ticket form
- **Response**: HTML form with category dropdown, file upload area

### GET /tickets/{id}
- **Auth**: Required (admin or ticket creator)
- **Controller**: `TicketController::show`
- **Action**: Render ticket detail with chat-style conversation view
- **Behavior**:
  - Admin: Can see any ticket, can send messages, can change status
  - Employee: Can only see their own tickets; messaging enabled on non-Closed tickets
  - Marks ticket as read for the current user
- **Response**: HTML page with ticket info, message list, message input (disabled if Closed)

## API Routes

### POST /tickets
- **Auth**: Required (employee only)
- **Controller**: `TicketController::create`
- **Request Body** (multipart/form-data for file uploads):
  - `_csrf_token`: string (required)
  - `title`: string (required, max 200)
  - `description`: string (required, max 5000)
  - `category`: string (required, one of: تقرير مشكلة, طلب صيانة, طلب معلومات, أخرى)
  - `attachments[]`: file[] (optional, max 10 files, max 5MB each)
- **Response 200**:
  ```json
  {
    "success": true,
    "message": "تم إنشاء التذكرة بنجاح",
    "ticket": { "id": 1, "ticket_code": "TKT-001", ... }
  }
  ```
- **Response 422**: Validation errors
  ```json
  {
    "success": false,
    "errors": {
      "title": "عنوان التذكرة مطلوب",
      "category": "يجب اختيار التصنيف"
    }
  }
  ```
- **Response 403**: `{ "success": false, "message": "غير مصرح بهذا الإجراء" }`

### POST /tickets/{id}/messages
- **Auth**: Required (admin or ticket creator; ticket must not be Closed for employees)
- **Controller**: `TicketController::addMessage`
- **Request Body** (multipart/form-data for file uploads):
  - `_csrf_token`: string (required)
  - `content`: string (required, max 5000)
  - `attachments[]`: file[] (optional, max 10 files, max 5MB each)
- **Behavior**:
  - Admin message: auto-sets ticket status to تم الرد (Replied)
  - Employee message on Closed ticket: rejected with 403
  - Employee message on Open/Replied ticket: accepted, status unchanged
  - Creates TicketRead record marking ticket as read for the sender
- **Response 200**:
  ```json
  {
    "success": true,
    "message": "تم إرسال الرد بنجاح",
    "data": { "message": { ... }, "attachments": [ ... ] }
  }
  ```
- **Response 403**: `{ "success": false, "message": "لا يمكن إرسال رسائل على تذكرة مغلقة" }` (employee on closed ticket) or `{ "success": false, "message": "غير مصرح بالوصول لهذه التذكرة" }` (wrong employee)
- **Response 404**: Ticket not found

### PUT /tickets/{id}/status
- **Auth**: Required, admin only
- **Controller**: `TicketController::updateStatus`
- **Request Body** (JSON):
  ```json
  {
    "_csrf_token": "string",
    "status": "مفتوح|قيد المراجعة|تم الرد|مغلق"
  }
  ```
- **Behavior**:
  - Admin can set any valid status
  - Setting to مغلق closes the ticket (blocks messaging)
  - Setting to مفتوح from مغلق reopens the ticket
- **Response 200**: `{ "success": true, "message": "تم تحديث حالة التذكرة بنجاح" }`
- **Response 403**: Employee attempting status change

### DELETE /tickets/{id}
- **Auth**: Required, admin only
- **Controller**: `TicketController::delete`
- **Request Body** (JSON):
  ```json
  { "_csrf_token": "string" }
  ```
- **Response 200**: `{ "success": true, "message": "تم حذف التذكرة بنجاح" }`
- **Response 403**: Non-admin attempting delete
- **Response 404**: Ticket not found

### GET /tickets/attachment/{id}/{filename}
- **Auth**: Required (admin or ticket creator)
- **Controller**: `TicketController::serveAttachment`
- **Action**: Serve file from secure storage with permission check
- **Behavior**:
  - Admin can download any attachment
  - Employee can only download attachments from their own tickets
  - Files served from `storage/uploads/tickets/` directory
- **Response**: File download with appropriate Content-Type header
- **Response 403**: Unauthorized access to another employee's attachment
- **Response 404**: Attachment not found

### GET /api/tickets/unread-count
- **Auth**: Required
- **Controller**: `TicketController::unreadCount`
- **Action**: Return count of tickets with unread messages for the current user
- **Response**:
  ```json
  { "success": true, "count": 3 }
  ```

### POST /tickets/{id}/mark-read
- **Auth**: Required (admin or ticket creator)
- **Controller**: `TicketController::markRead`
- **Action**: Mark ticket as read for the current user (upserts ticket_reads)
- **Response 200**: `{ "success": true }`
- **Response 403**: Unauthorized employee