# Data Model: Salary Module

## Entities

### `salaries` (Salary Record)
| Field | Type | Description |
|-------|------|-------------|
| `id` | INT AUTO_INCREMENT | PK |
| `user_id` | INT | FK to `users` |
| `month` | TINYINT | 1-12 |
| `year` | INT | e.g. 2026 |
| `basic_salary` | DECIMAL(10,2) | Pulled from config at generation |
| `allowances` | DECIMAL(10,2) | Pulled from config at generation |
| `deductions` | DECIMAL(10,2) | Pulled from config at generation |
| `net_amount` | DECIMAL(10,2) | Calculated: basic + allowances - deductions |
| `status` | ENUM('unpaid', 'paid') | Default: 'unpaid' |
| `paid_at` | DATETIME | NULL until paid |
| `paid_by` | INT | FK to `users` (Admin who confirmed) |
| `created_at` | TIMESTAMP | Audit |
| `updated_at` | TIMESTAMP | Audit |
| `deleted_at` | TIMESTAMP | Soft delete |

**Uniqueness**: Unique index on `(user_id, month, year, deleted_at)` to prevent duplicates.

### `salary_configs` (Employee Salary Configuration)
| Field | Type | Description |
|-------|------|-------------|
| `id` | INT AUTO_INCREMENT | PK |
| `user_id` | INT | FK to `users` (one-to-one) |
| `basic_salary` | DECIMAL(10,2) | Override for department default |
| `allowances` | DECIMAL(10,2) | |
| `deductions` | DECIMAL(10,2) | |
| `notes` | TEXT | Admin internal notes |
| `created_at` | TIMESTAMP | Audit |
| `updated_at` | TIMESTAMP | Audit |

**Relationship**: `1:1` with `users` (role='employee').

## Relationships

- `users` (Admin) --- confirms ---> `salaries`
- `users` (Employee) <--- owns --- `salaries`
- `users` (Employee) <--- has --- `salary_configs`
- `departments` --- provides default `basic_salary` if `salary_configs` is missing for an employee.

## State Transitions (Salaries)

1. `Unpaid` (Initial state upon generation)
2. `Paid` (Final state after admin confirmation; irreversible in MVP)
