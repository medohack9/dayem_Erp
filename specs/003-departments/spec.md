# Feature Specification: Departments Module

**Feature Branch**: `003-departments`  
**Created**: 2026-04-25  
**Status**: Draft  
**Input**: User description: "Phase 3 — Departments Module: Manage company departments with create, edit, delete (with reassignment), assign manager, and default salary features."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Create Department (Priority: P1)

As an admin, I want to create a new department so that I can organize the company structure and associate employees with specific organizational units.

**Why this priority**: Departments are a foundational entity — employees, tasks, and salary modules all depend on departments existing. Without this, no other department-related feature can function.

**Independent Test**: Can be fully tested by creating a department via a form and verifying it appears in the department list. Delivers immediate organizational structure value.

**Acceptance Scenarios**:

1. **Given** I am logged in as admin, **When** I navigate to the departments section and fill in the department name, manager (optional), and default salary (optional), **Then** the department is created and appears in the department list
2. **Given** I am on the create department form, **When** I submit the form with an empty department name, **Then** I see a validation error indicating the name is required
3. **Given** I am on the create department form, **When** I submit a department name that already exists, **Then** I see an error indicating the name must be unique
4. **Given** I am an employee, **When** I attempt to access the create department page, **Then** I am denied access (403)

---

### User Story 2 - View Department List (Priority: P1)

As an admin, I want to see a list of all departments so that I can browse, search, and manage the organizational structure.

**Why this priority**: The list view is essential for navigating, editing, and deleting departments. It serves as the entry point for all department management actions.

**Independent Test**: Can be tested by creating several departments and verifying they all appear in the list with correct details, and can be searched/filtered.

**Acceptance Scenarios**:

1. **Given** departments exist in the system, **When** I navigate to the departments page, **Then** I see all departments listed with name, manager name, default salary, employee count, and status
2. **Given** multiple departments exist, **When** I type a search term, **Then** the list filters to show only matching departments
3. **Given** I am an employee, **When** I navigate to the departments section, **Then** I can view the department list in read-only mode (no create/edit/delete actions visible)

---

### User Story 3 - Edit Department (Priority: P2)

As an admin, I want to edit an existing department so that I can keep organizational information up to date as the company evolves.

**Why this priority**: Editing is essential for maintaining accurate data but depends on departments already existing (P1).

**Independent Test**: Can be tested by editing a department's name, manager, or default salary and verifying the changes persist.

**Acceptance Scenarios**:

1. **Given** I am an admin viewing a department, **When** I click edit and modify the department name, manager, or default salary, **Then** the changes are saved and reflected in the department list and detail view
2. **Given** I am editing a department, **When** I change the name to one that already exists, **Then** I see a validation error
3. **Given** I am an employee, **When** I attempt to access the edit department page, **Then** I am denied access (403)

---

### User Story 4 - Delete Department with Reassignment (Priority: P2)

As an admin, I want to delete a department and reassign its employees to another department so that I can reorganize the company structure without losing employee data.

**Why this priority**: Deletion with reassignment is crucial for maintaining data integrity when organizational changes happen, but it depends on P1 features.

**Independent Test**: Can be tested by creating a department with employees, then deleting it while choosing a reassignment target, and verifying employees moved correctly.

**Acceptance Scenarios**:

1. **Given** a department has employees, **When** I initiate deletion, **Then** a modal dialog appears prompting me to select a target department for employee reassignment before the deletion proceeds
2. **Given** a department has no employees, **When** I initiate deletion, **Then** the department is soft-deleted immediately without requiring reassignment
3. **Given** I am deleting a department, **When** I select a target department for reassignment and confirm, **Then** all employees are moved to the target department and the original department is soft-deleted
4. **Given** I am the only department in the system, **When** I attempt to delete it, **Then** I am prevented from deleting the last department and shown an error message

---

### User Story 5 - Assign Department Manager (Priority: P3)

As an admin, I want to assign a manager to a department so that each department has a designated responsible person.

**Why this priority**: Manager assignment enhances organizational structure and can be done during creation or editing, but the core CRUD operations take precedence.

**Independent Test**: Can be tested by creating/editing a department and selecting an employee as manager, then verifying the manager is displayed.

**Acceptance Scenarios**:

1. **Given** I am creating or editing a department, **When** I select an employee from a dropdown as manager, **Then** the manager is associated with the department and displayed in the department list
2. **Given** a department has a manager assigned, **When** the manager's employee record is terminated, **Then** the department's manager field is cleared or flagged

---

### User Story 6 - Default Department Salary (Priority: P3)

As an admin, I want to set a default salary for a department so that new employees added to the department automatically inherit this salary baseline.

**Why this priority**: Default salary is a convenient feature for rapid employee onboarding but is not required for core department management.

**Independent Test**: Can be tested by setting a default salary on a department, then creating an employee in that department and verifying the salary is pre-filled.

**Acceptance Scenarios**:

1. **Given** I am creating or editing a department, **When** I enter a default salary value, **Then** the value is saved and displayed in the department details
2. **Given** a department has a default salary of 5000, **When** a new employee is added to that department, **Then** the salary field is pre-filled with 5000
3. **Given** a department has no default salary set, **When** a new employee is added, **Then** the salary field is left empty for manual entry

---

### Edge Cases

- What happens when a department name contains special characters or Arabic text? — Names are stored and displayed correctly with UTF-8 encoding
- What happens when deleting a department that is the only one in the system? — Deletion is blocked with an error message
- What happens when reassigning employees to a department that is also being deleted? — The system prevents selecting the department being deleted as a reassignment target
- What happens when an assigned manager is deleted or terminated? — The manager field is cleared
- What happens if two admins edit the same department simultaneously? — Last write wins; no optimistic locking for MVP
- What happens when searching departments with no matches? — A "no results" message is displayed

## Clarifications

### Session 2026-04-25

- Q: Can one employee manage multiple departments, or is it one-to-one? → A: Strict one-to-one — each employee manages at most one department, each department has at most one manager
- Q: How should the delete confirmation/reassignment prompt be presented? → A: Modal dialog on the same page — select target department and confirm inline (AJAX)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin MUST be able to create a new department with name (required), manager (optional), and default salary (optional)
- **FR-002**: System MUST enforce unique department names (case-insensitive)
- **FR-003**: Admin MUST be able to view a paginated list of all departments with search/filter capability
- **FR-004**: Admin MUST be able to edit department details (name, manager, default salary)
- **FR-005**: Admin MUST be able to soft-delete a department with mandatory employee reassignment via modal dialog if employees exist
- **FR-006**: System MUST prevent deletion of the last remaining department in the system
- **FR-007**: System MUST exclude soft-deleted departments from active lists but preserve them in the database for audit
- **FR-008**: Admin MUST be able to assign an employee as department manager (strict one-to-one: each employee manages at most one department, each department has at most one manager)
- **FR-009**: System MUST display employee count per department in the department list
- **FR-010**: Employees MUST be able to view the department list in read-only mode
- **FR-011**: System MUST support Arabic text for all department fields (name, notes)
- **FR-012**: All department management actions MUST require admin role authorization
- **FR-013**: System MUST generate an audit log entry for each department create, edit, and delete action
- **FR-014**: System MUST pre-fill default salary when adding an employee to a department that has a default salary set
- **FR-015**: System MUST clear the manager assignment when the manager's employee record is terminated or deleted

### Key Entities

- **Department**: Represents an organizational unit. Key attributes: name (unique, required), manager (optional employee reference), default salary (optional numeric), employee count (computed), status (active/deleted via soft delete), timestamps (created_at, updated_at, deleted_at)
- **Employee**: Referenced as department manager. Key attributes from existing users table: id, name, role, status. Relationship: strict one-to-one — an employee can manage exactly one department, and a department can have at most one manager

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An admin can create a new department in under 30 seconds with name, manager, and default salary
- **SC-002**: Department list loads within 2 seconds even with 100+ departments
- **SC-003**: Searching/filtering departments returns results within 1 second
- **SC-004**: All department CRUD actions produce visible feedback (success message or error) within 1 second
- **SC-005**: Employee reassignment during department deletion completes without data loss — every affected employee is moved to the target department
- **SC-006**: Arabic department names are stored, displayed, and searched correctly without corruption
- **SC-007**: Employees view departments in read-only mode — no create, edit, or delete controls are visible
- **SC-008**: 100% of department management actions are logged in the audit system

## Assumptions

- Admins are authenticated users with role='admin' from the existing users table (Phase 2)
- Employees are authenticated users with role='employee' who can only view departments (not manage them)
- Department manager is an existing employee (user with role='employee' or 'admin') selected from a dropdown
- Soft deletion is used for departments — records are marked as deleted but preserved in the database
- The last remaining department cannot be deleted to ensure organizational structure integrity
- Default currency for salary is EGP (Egyptian Pound), stored as a numeric value
- Pagination defaults to 15 items per page
- Search is performed on department name only (not on manager name or salary)
- Employee count per department is computed dynamically (not cached)
- The existing MVC architecture, Session, Auth, and ErrorHandler from Phases 1-2 are reused
- All forms use CSRF protection and AJAX submission per project constitution