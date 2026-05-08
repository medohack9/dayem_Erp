# Feature Specification: Employee Management

**Feature Branch**: `004-employees`  
**Created**: 2026-04-25  
**Status**: Draft  
**Input**: User description: "Employee management module for Dayem ERP - CRUD operations for employees with search, filtering, and department assignment"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View Employee List (Priority: P1)

An admin needs to see all employees in the company in a table view, search by name or employee code, and filter by department or status. This is the primary entry point for managing employees.

**Why this priority**: Without the ability to view employees, no other employee operations have context. This is the foundation of the module.

**Independent Test**: Can be fully tested by logging in as admin, navigating to the employees page, and verifying the table displays employee data with search and filter working.

**Acceptance Scenarios**:

1. **Given** an admin is logged in, **When** they navigate to the employees page, **Then** they see a table with columns: name (with avatar initial), employee code, department, phone, salary, status badge, and action buttons
2. **Given** there are multiple employees, **When** the admin types in the search box, **Then** results filter in real-time showing matches by name or employee code
3. **Given** there are employees across departments, **When** the admin selects a department filter, **Then** only employees in that department are shown
4. **Given** there are more than 15 employees, **When** the admin views the list, **Then** pagination controls appear and work correctly

---

### User Story 2 - Add New Employee (Priority: P2)

An admin needs to create a new employee record by filling out personal information (full name, email, phone, national ID, birth date), job information (salary, department, hire date), and optionally uploading a profile photo. The system auto-generates an employee code.

**Why this priority**: Adding employees is the core data entry operation and essential for onboarding.

**Independent Test**: Can be fully tested by navigating to the add employee form, filling in all required fields, and verifying the new employee appears in the list.

**Acceptance Scenarios**:

1. **Given** an admin is on the add employee page, **When** they fill in all required fields (name, email, phone, national ID, birth date, salary, department) and submit, **Then** a new employee record is created with status "active" and a unique employee code is generated
2. **Given** the admin leaves a required field empty, **When** they submit the form, **Then** a validation error is shown next to the missing field without page reload
3. **Given** an email or phone already exists in the system, **When** the admin submits with duplicate data, **Then** a clear error message indicates the conflict
4. **Given** the admin selects a department, **When** they view the salary field, **Then** the department's default salary is suggested (but can be overridden)

---

### User Story 3 - Edit Employee (Priority: P3)

An admin needs to edit an existing employee's information including personal details, job details, department assignment, and status changes (suspend, reactivate, terminate).

**Why this priority**: Employee data changes over time and must be kept up to date. This is essential for ongoing HR operations.

**Independent Test**: Can be fully tested by editing an existing employee's details and verifying the changes are persisted.

**Acceptance Scenarios**:

1. **Given** an admin clicks the edit button on an employee row, **When** the edit form loads, **Then** all current employee data is pre-filled in the form
2. **Given** an admin modifies employee data, **When** they submit the form, **Then** the changes are saved and reflected in the employee list
3. **Given** an admin changes an employee's department, **When** they save, **Then** the employee is reassigned to the new department and no longer appears under the old department
4. **Given** an admin changes an employee's status to "suspended" or "terminated", **When** they save, **Then** the employee can no longer log in and their status badge updates accordingly

---

### User Story 4 - Delete Employee (Priority: P4)

An admin needs to soft-delete an employee record, which marks the employee as deleted without permanently removing their data. If the employee was a department manager, the system must handle the manager reassignment.

**Why this priority**: Deletion is needed but less frequent than viewing, adding, or editing. It must handle the department manager edge case.

**Independent Test**: Can be tested by deleting an employee and verifying they no longer appear in the active list, and that department manager assignments are updated correctly.

**Acceptance Scenarios**:

1. **Given** an admin clicks the delete button on an employee row, **When** a confirmation dialog appears, **Then** the admin can confirm or cancel the deletion
2. **Given** an admin confirms deletion, **When** the employee is soft-deleted, **Then** the employee no longer appears in the active employee list
3. **Given** a deleted employee was a department manager, **When** they are soft-deleted, **Then** their department's manager field is cleared

---

### Edge Cases

- What happens when the system tries to create an employee with a national ID that already exists? → Validation error must be shown
- What happens when a department is deleted and its employees need reassignment? → Handled by departments module (already implemented)
- What happens when an admin tries to delete their own account? → The system must prevent this action with a clear error message
- What happens when an employee's email format is invalid? → Real-time validation must reject invalid formats
- What happens when the employee list has zero results after filtering? → A "no employees found" message is displayed
- What happens when an employee is suspended and tries to log in? → The auth system should reject the login with a clear message
- What happens when a salary value is negative or zero? → Validation must reject it
- What happens when a phone number format is invalid? → Validation must accept Egyptian phone formats

## Clarifications

### Session 2026-04-25

- Q: Should terminated employees be visible through the existing status filter or in a separate dedicated view? → A: Status filter. Terminated employees appear in the same list when filtering by status, consistent with the existing filter UI and Figma design.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST display a list of employees (active, suspended, and terminated shown via status filter) in a table with name, employee code, department, phone, salary, status, and actions. Default view shows active employees only.
- **FR-002**: System MUST allow searching employees by name or employee code with real-time filtering
- **FR-003**: System MUST allow filtering employees by department and by status (active, suspended, terminated) in the same list view, with "active" as the default filter
- **FR-004**: System MUST paginate the employee list with 15 employees per page
- **FR-005**: System MUST allow admins to create a new employee with fields: full name (required), email (required, unique), phone (required, unique, Egyptian format), national ID (required, unique, 14 digits), birth date (required), salary (required, positive number), department (required, dropdown of active departments), hire date (optional, defaults to today)
- **FR-006**: System MUST auto-generate a unique employee code in the format EMP-NNN for each new employee
- **FR-007**: System MUST validate that email, phone, and national ID are unique across all employees before creating a new record
- **FR-008**: System MUST suggest the department's default salary when a department is selected in the create form (admin can override)
- **FR-009**: System MUST allow admins to edit all employee fields including changing department assignment and status
- **FR-010**: System MUST allow admins to change employee status between active, suspended, and terminated
- **FR-011**: System MUST prevent suspended or terminated employees from logging into the system
- **FR-012**: System MUST allow admins to soft-delete an employee with a confirmation dialog
- **FR-013**: System MUST clear the department manager field if a deleted employee was managing a department
- **FR-014**: System MUST prevent an admin from deleting their own account
- **FR-015**: System MUST validate all form inputs client-side and server-side with Arabic error messages
- **FR-016**: System MUST log all employee CRUD actions (create, update, delete, status change) in the audit log
- **FR-017**: System MUST restrict all employee management operations to admin users only
- **FR-018**: System MUST show the employee's initial in a colored avatar circle on the list page
- **FR-019**: System MUST display status badges with distinct colors: green for active, yellow for suspended, red for terminated

### Key Entities

- **Employee**: A system user with role "employee". Extends the existing user with employee-specific fields: employee code (auto-generated), national ID (14 digits, unique), birth date, salary, hire date, department assignment. Status can be active, suspended, or terminated. Has a one-to-many relationship with departments (many employees belong to one department).
- **Department**: Already exists. Has a one-to-many relationship to employees (one department has many employees). The default salary field on departments is used as a suggestion when adding new employees.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An admin can view, search, and filter the employee list within 2 seconds for up to 500 employees
- **SC-002**: An admin can create a new employee record in under 1 minute with all required information
- **SC-003**: 100% of form validation errors are displayed clearly in Arabic next to the relevant field within 1 second
- **SC-004**: 95% of admin users can complete the add/edit/delete employee workflow on their first attempt without assistance
- **SC-005**: All employee data changes are recorded in the audit log with the responsible admin, timestamp, and change details

## Assumptions

- The existing `users` table will be extended with employee-specific columns (national_id, birth_date, salary, hire_date, employee_code) rather than creating a separate employees table
- Employee codes follow the format EMP-NNN (e.g., EMP-001, EMP-002) and are auto-generated sequentially
- Profile photo upload is out of scope for this phase (the UI has a placeholder but file upload will be deferred to a future phase)
- Document upload (contracts, additional documents) is out of scope for this phase
- The system only supports Egyptian phone number format (11 digits starting with 01)
- National ID is the Egyptian 14-digit format
- Currency is Egyptian Pounds (EGP)
- All text and error messages are in Arabic, matching the existing UI language
- The existing authentication and RBAC system from Phase 2 will be reused for employee login restrictions
- The existing audit log system (LogModel) from Phase 3 will be used for employee action logging
- Department manager enforcement (one-to-one) is already handled in the departments module