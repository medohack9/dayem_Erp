# Quickstart: Salary Module

## Setup

1. **Database Migration**:
   Run the migration script: `database/migrations/009_salaries.sql` to create `salaries` and `salary_configs` tables.

2. **Routes**:
   Ensure `config/routes.php` includes the new salary routes.

3. **Permissions**:
   Admins have full access. Employees can only access `/salaries/my`.

## Core Workflows

### 1. Configure Salaries
- Go to **Salaries > Configuration**.
- Set basic salary, allowances, and deductions for employees.
- If no configuration exists, the system will use the **Department Default Salary**.

### 2. Generate Monthly Payroll
- Go to **Salaries**.
- Select the Month and Year.
- Click **"Generate Salaries"**.
- System will create `unpaid` records for all active employees.

### 3. Confirm Payment
- In the salary list, click the **"Mark as Paid"** checkmark/button.
- This action is recorded in the audit logs.

### 4. Recalculate
- If you change an employee's salary configuration after generation, click **"Recalculate Unpaid"** to update current month's records before paying.

## Troubleshooting
- **Missing Employee**: Ensure employee status is 'active' (not suspended or terminated).
- **Duplicate Record**: The system prevents generating for the same month/year/employee twice.
- **Incorrect Amount**: Check if a specific `salary_configs` exists for the user; if not, check the Department's default salary.
