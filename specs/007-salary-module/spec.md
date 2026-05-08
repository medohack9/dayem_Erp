# Feature Specification: Salary Module

**Feature Branch**: `007-salary-module`  
**Created**: 2026-04-29  
**Status**: Draft  
**Input**: User description: "Phase 7 — Salary Module: Track and manage monthly salary records, payment confirmation, paid date tracking, paid by admin tracking, and salary history view"

## Clarifications

### Session 2026-04-29
- Q: Where should employee-specific salary overrides be stored? → A: Create a separate salary configuration table (user_id, salary, and additional metadata).
- Q: Which currency should be used for the system? → A: Only Egyptian Pound (ج.م / EGP).
- Q: How to handle salary config changes after generation but before payment? → A: Provide a "Recalculate" button for unpaid records to sync with new configuration.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Generates Monthly Salary Records (Priority: P1)

As an admin, I want to generate monthly salary records for all active employees so that I have a structured payroll ledger for each month. When generating records, the system should pull each employee's current salary (from the department default or any employee-specific override) and create one salary record per active employee for the selected month. If records already exist for that month, the system should warn me and not create duplicates.

**Why this priority**: Without salary records being generated, no other salary workflow (payment, history, tracking) can function. This is the foundational action.

**Independent Test**: Can be fully tested by selecting a month, clicking "Generate Salaries," and verifying that one record per active employee appears in the salary list with the correct amount and "Unpaid" status.

**Acceptance Scenarios**:

1. **Given** there are 10 active employees and no salary records for May 2026, **When** the admin selects May 2026 and clicks "Generate Salaries," **Then** 10 salary records are created, each with status "Unpaid," the correct salary amount, and the employee's name and department.
2. **Given** salary records for May 2026 already exist, **When** the admin attempts to generate salaries for May 2026 again, **Then** the system displays a warning message and does not create duplicate records.
3. **Given** an employee is suspended or terminated, **When** the admin generates salaries for the current month, **Then** no salary record is created for that employee.
4. **Given** salary records exist for May 2026 and an employee's configuration is updated, **When** the admin clicks "Recalculate" for that month, **Then** the unpaid salary records are updated with the new amounts from the configuration table/department defaults.

---

### User Story 2 - Admin Confirms Salary Payment (Priority: P1)

As an admin, I want to mark individual salary records as "Paid" so that I can track which employees have received their monthly salary. When confirming payment, the system should record the exact payment date and which admin performed the confirmation.

**Why this priority**: Payment confirmation is the core transactional action of the salary module — without it, the system is only a ledger with no workflow.

**Independent Test**: Can be fully tested by navigating to a generated salary record, clicking "Mark as Paid," and verifying the status changes to "Paid" with the correct date and admin name recorded.

**Acceptance Scenarios**:

1. **Given** an unpaid salary record for employee "أحمد محمد" for April 2026, **When** the admin clicks "Mark as Paid," **Then** the record status changes to "Paid," the paid date is set to the current date, and the admin's name is recorded as the confirming user.
2. **Given** a salary record is already marked as "Paid," **When** the admin views it, **Then** the "Mark as Paid" action is no longer available and the payment details (date, admin) are displayed.
3. **Given** a salary record is "Unpaid," **When** a non-admin employee views it, **Then** the "Mark as Paid" action is not visible.

---

### User Story 3 - Admin Views Salary List with Filters (Priority: P2)

As an admin, I want to view a list of all salary records filterable by month, department, and payment status so that I can quickly find specific records and understand the overall payroll status at a glance.

**Why this priority**: Efficient navigation and filtering are essential for usability once records exist, but the module delivers value even without advanced filtering.

**Independent Test**: Can be tested by generating salary records for multiple months and departments, then using the filter controls to narrow results and verifying the displayed records match the filter criteria.

**Acceptance Scenarios**:

1. **Given** salary records exist for March, April, and May 2026, **When** the admin filters by "April 2026," **Then** only April records are displayed.
2. **Given** salary records exist across multiple departments, **When** the admin filters by "Engineering" department, **Then** only records for employees in Engineering are shown.
3. **Given** a mix of Paid and Unpaid records, **When** the admin filters by status "Unpaid," **Then** only unpaid records are displayed.
4. **Given** more than 15 salary records match the current filters, **When** the admin views the list, **Then** records are paginated at 15 per page.

---

### User Story 4 - Employee Views Own Salary History (Priority: P2)

As an employee, I want to see my own salary history so that I can verify my payment records and track when I was paid. I should only be able to see my own records and should not have access to other employees' salary information.

**Why this priority**: Provides transparency to employees and reduces admin inquiries, but is not required for the core admin payroll workflow.

**Independent Test**: Can be tested by logging in as an employee, navigating to the salary section, and verifying that only the logged-in employee's records are displayed with correct amounts and payment statuses.

**Acceptance Scenarios**:

1. **Given** employee "سارة علي" has salary records for January through April 2026, **When** she navigates to the salary section, **Then** she sees exactly her 4 records with month, amount, and payment status for each.
2. **Given** employee "سارة علي" is logged in, **When** she attempts to access salary records, **Then** she cannot see records belonging to other employees.
3. **Given** a salary record for March 2026 is marked as "Paid" on March 28, **When** the employee views that record, **Then** she sees the status "Paid" and the payment date "March 28, 2026."

---

### User Story 5 - Admin Views Salary Summary Statistics (Priority: P3)

As an admin, I want to see a summary at the top of the salary page showing total payroll for the selected month, the count of paid vs. unpaid records, and the total amount remaining to be disbursed so that I can quickly assess payroll completion status.

**Why this priority**: Summary statistics enhance the admin experience but are a convenience layer on top of the core record and payment functionality.

**Independent Test**: Can be tested by generating salary records and confirming some as paid, then verifying that the summary cards display correct totals, counts, and remaining amounts.

**Acceptance Scenarios**:

1. **Given** 10 salary records for May 2026 totaling 50,000 EGP with 6 paid (30,000 EGP) and 4 unpaid (20,000 EGP), **When** the admin views the salary page filtered to May 2026, **Then** summary cards show: Total Payroll = 50,000 EGP, Paid = 6 (30,000 EGP), Unpaid = 4 (20,000 EGP).
2. **Given** all salary records for a month are marked as "Paid," **When** the admin views the summary, **Then** the unpaid count shows 0 and the remaining amount shows 0 EGP.

---

### Edge Cases

- What happens when an employee's salary amount is changed mid-month after records have been generated? The generated record retains the amount at the time of generation; changes apply to future months only.
- What happens when an employee is terminated after salary records are generated but before payment? The record remains and can still be marked as Paid or left as Unpaid at the admin's discretion.
- What happens if the admin generates salaries when no active employees exist? The system displays a message "No active employees found" and does not create any records.
- What happens if an admin accidentally needs to reverse a payment confirmation? Payment confirmation is final in this version; corrections must be handled manually outside the system.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow admins to generate monthly salary records for all active employees for a selected month and year.
- **FR-002**: System MUST prevent duplicate salary record generation for the same employee and month.
- **FR-003**: System MUST set each generated salary record to "Unpaid" status by default.
- **FR-004**: System MUST pull the salary amount from the dedicated employee salary configuration table (if exists) or the department default salary at the time of generation.
- **FR-005**: System MUST allow admins to mark individual salary records as "Paid."
- **FR-006**: System MUST record the payment date and confirming admin when a salary is marked as Paid.
- **FR-007**: System MUST prevent re-marking an already-paid salary record.
- **FR-008**: System MUST display a paginated list of salary records with filters for month, department, and payment status (admin view).
- **FR-009**: System MUST allow employees to view only their own salary history.
- **FR-010**: System MUST enforce role-based access: admins see all records, employees see only their own.
- **FR-011**: System MUST display summary statistics (total payroll, paid count/amount, unpaid count/amount) for the selected month on the admin salary page.
- **FR-012**: System MUST exclude suspended and terminated employees when generating monthly salary records.
- **FR-013**: System MUST log all salary actions (generation, payment confirmation) in the audit log.
- **FR-014**: System MUST support soft deletion of salary records.
- **FR-015**: System MUST allow admins to "Recalculate" generated but unpaid salary records to synchronize with updated configuration settings.
- **FR-016**: System MUST display all salary views in Arabic RTL layout consistent with the rest of the system.

### Key Entities

- **Salary Record**: Represents a single employee's salary for a specific month and year. Key attributes include: associated employee, month, year, salary amount, payment status (Unpaid/Paid), payment date, confirming admin, and audit timestamps.
- **Employee Salary Configuration**: A dedicated table storing employee-specific salary details (user_id, salary amount, and additional metadata) used as an override for department defaults.
- **Employee**: Existing entity — the salary record and configuration reference an employee. Key relationship: one employee has many salary records (one per month).
- **Department**: Existing entity — provides the default salary amount used when generating records for employees without individual salary overrides.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admins can generate monthly salary records for all active employees in under 5 seconds.
- **SC-002**: Admins can confirm payment for an individual salary record in under 3 clicks.
- **SC-003**: The salary list page loads within 2 seconds with up to 500 records.
- **SC-004**: Employees can view their complete salary history within 2 seconds of navigating to the salary section.
- **SC-005**: 100% of salary actions (generation, payment confirmation) are captured in the audit log.
- **SC-006**: Zero duplicate salary records can exist for the same employee and month combination.
- **SC-007**: Employee users cannot access any salary record that does not belong to them.

## Assumptions

- The existing employee model includes a salary amount field or the department model includes a default salary field that can be used as the source for generated salary records.
- The existing authentication and role-based access control system will be reused to enforce admin vs. employee permissions.
- Currency is Egyptian Pound (ج.م / EGP) and no multi-currency support is needed for this version.
- The system is used internally by a single organization; no multi-tenant considerations are needed.
- Salary generation is a manual admin action (not automated on a schedule).
- Payment confirmation is irreversible in this version — no "undo" or "refund" workflow is included.
- The audit logging infrastructure (LogModel) is already in place from previous phases.
- Pagination follows the existing system default of 15 records per page.
