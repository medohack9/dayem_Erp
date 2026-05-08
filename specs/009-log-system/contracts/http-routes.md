# HTTP Route Contracts: Log System

**Feature**: 009-log-system
**Date**: 2026-05-01

All routes follow existing patterns. Admin-only access enforced on all routes.

## Page Routes

### GET /logs
- **Auth**: Required (admin only)
- **Controller**: `LogController::index`
- **Action**: Render log list page with filters
- **Query Params**: `page`, `user_id`, `action`, `entity_type`, `entity_id`, `date_from`, `date_to`
- **Response**: HTML page with logs, filters, pagination, export buttons

## API Routes

### GET /api/logs
- **Auth**: Required (admin only)
- **Controller**: `LogController::list`
- **Action**: Return paginated logs as JSON (for potential AJAX loading)
- **Query Params**: Same as GET /logs
- **Response**:
  ```json
  {
    "success": true,
    "logs": [...],
    "total": 1250,
    "page": 1,
    "totalPages": 25
  }
  ```

### GET /logs/export
- **Auth**: Required (admin only)
- **Controller**: `LogController::export`
- **Action**: Export filtered logs to CSV
- **Query Params**: Same filters as GET /logs
- **Response**: CSV file download with headers:
  - Content-Type: text/csv; charset=utf-8
  - Content-Disposition: attachment; filename="logs_YYYYMMDD_HHMMSS.csv"

### GET /logs/export/today
- **Auth**: Required (admin only)
- **Controller**: `LogController::exportToday`
- **Action**: Quick export of today's logs
- **Response**: CSV file download (same format as export)

### GET /logs/{id}
- **Auth**: Required (admin only)
- **Controller**: `LogController::detail`
- **Action**: Return log detail as JSON (for modal display)
- **Response**:
  ```json
  {
    "success": true,
    "log": {
      "id": 1,
      "user_name": "Ahmed Mohamed",
      "action": "update",
      "entity_type": "employee",
      "entity_id": 5,
      "details": { "before": {...}, "after": {...} },
      "ip_address": "192.168.1.100",
      "created_at": "2026-05-01 10:30:00"
    }
  }
  ```

### GET /api/logs/filters
- **Auth**: Required (admin only)
- **Controller**: `LogController::filterOptions`
- **Action**: Return distinct values for filter dropdowns
- **Response**:
  ```json
  {
    "success": true,
    "actions": ["create", "delete", "download", "edit", "reply", "status_change", "update", "upload"],
    "entity_types": ["department", "employee", "file", "salary", "task", "ticket", "user"],
    "users": [
      {"id": 1, "name": "Ahmed Mohamed", "status": "active"},
      {"id": 5, "name": "Deleted User", "status": "deleted"}
    ]
  }
  ```