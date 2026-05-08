# HTTP Routes: Salary Module

All routes follow the `/salaries/...` prefix. Access is restricted by Role (Admin/Employee).

## Admin Routes

| Method | Path | Action | Description |
|--------|------|--------|-------------|
| GET | `/salaries` | `index` | View all salary records with filters & summary |
| POST | `/salaries/generate` | `generate` | Generate records for a specific month/year |
| POST | `/salaries/pay/{id}` | `pay` | Mark a specific record as Paid |
| POST | `/salaries/recalculate` | `recalculate` | Sync unpaid records with current config |
| GET | `/salaries/config` | `configList` | List employees and their current pay scales |
| GET | `/salaries/config/{user_id}` | `configShow` | View/Edit specific employee configuration |
| POST | `/salaries/config/{user_id}` | `configUpdate` | Update employee salary configuration |

## Employee Routes

| Method | Path | Action | Description |
|--------|------|--------|-------------|
| GET | `/salaries/my` | `myHistory` | View personal salary history |

## AJAX Endpoints (Internal)

- `POST /salaries/pay/{id}`: Returns JSON `{ "success": true, "paid_at": "...", "admin": "..." }`
- `POST /salaries/recalculate`: Returns JSON `{ "success": true, "updated_count": X }`
