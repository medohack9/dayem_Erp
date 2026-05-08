# DAYEM ERP — SYSTEM CONSTITUTION

## 1. SYSTEM PURPOSE

Dayem ERP is a web-based internal system designed to manage employees, departments, tasks, tickets, salaries, files, and logs for Dayem company in Egypt.

The system must deliver:
- High performance
- Strong security
- Clean architecture
- Long-term scalability
- Excellent Arabic (RTL) user experience

The system is built as a foundation for future expansion into a full enterprise ERP platform.

---

## 2. TECHNOLOGY STACK

### Frontend
- HTML5
- Tailwind CSS
- Vanilla JavaScript (no external JS libraries)

### Backend
- Native PHP (custom MVC architecture)

### Database
- MySQL (InnoDB engine)

### Environment
- Development: Local (XAMPP)
- Production: Hostinger (shared hosting)

---

## 3. CORE ARCHITECTURE PRINCIPLES

### 3.1 MVC STRUCTURE (MANDATORY)

The system MUST follow strict separation of concerns:

- Controllers → handle HTTP requests and responses
- Models → handle database logic and queries
- Views → handle UI rendering only

Business logic MUST NOT exist inside views.

---

### 3.2 SINGLE ENTRY POINT

All requests MUST pass through:

/public/index.php

Routing must be centralized and controlled.

---

### 3.3 MODULAR SYSTEM DESIGN

The system is divided into independent modules:

- Authentication
- Employees
- Departments
- Tasks
- Tickets
- Salary
- Files
- Logs
- Dashboard

Each module MUST be isolated, maintainable, and extendable.

---

### 3.4 FEATURE-FIRST DEVELOPMENT

Development MUST be done feature-by-feature:

Each feature must include:
- Database structure
- Backend logic
- Frontend UI

A feature is not complete unless all three layers are complete.

---

## 4. CODING STANDARDS

### 4.1 NAMING CONVENTION

- Database: snake_case
- PHP / JavaScript: camelCase

---

### 4.2 CLEAN CODE RULES

- No duplicated logic
- Reusable components are mandatory
- Functions must be small and single-purpose
- Code must be readable and maintainable

---

### 4.3 VERSION CONTROL (MANDATORY)

- Git must be used
- Repository must be private
- Daily commits required
- Clear commit messages required

---

## 5. SECURITY RULES (CRITICAL)

The system MUST implement:

- password_hash() for password storage
- Prepared statements (PDO) for all database queries
- CSRF protection for all forms and requests
- Input validation and sanitization
- File upload validation (type and size ≤ 20MB)
- Session timeout after 30 minutes of inactivity

### STRICTLY FORBIDDEN:

- Storing plain text passwords
- Trusting user input
- Executing uploaded files
- Exposing system errors in production

---

## 6. AUTHORIZATION & ACCESS CONTROL

### Roles:

#### Admin:
- Full access to all modules and data

#### Employee:
- Access limited to:
  - Own tasks
  - Own tickets
  - Own files
  - Own salary

Access control MUST be enforced at backend level (not frontend only).

---

## 7. DATABASE RULES

### 7.1 SOFT DELETE

All major entities MUST implement soft delete:

deleted_at column is required.

Hard delete is NOT allowed unless explicitly justified.

---

### 7.2 AUDIT FIELDS

Tables SHOULD include:

- created_at
- updated_at (when applicable)

---

### 7.3 DATA INTEGRITY

- Use foreign keys where applicable
- Maintain relational integrity
- Avoid redundant data

---

## 8. FILE STORAGE RULES

All uploaded files MUST be stored in:

/storage/uploads/

Organized by module:

- employees/
- tasks/
- tickets/
- files/

### Rules:
- File size ≤ 20MB
- Unique file naming required
- File type validation required
- Prevent direct execution of uploaded files

---

## 9. LOGGING SYSTEM

All system actions MUST be logged.

### Log format:
(user_name, action, department, timestamp)

### Examples:
- Created employee
- Updated task
- Deleted file
- Paid salary

Logs MUST:
- Be stored in database
- Be exportable as CSV per day

---

## 10. FRONTEND RULES

### 10.1 DESIGN SYSTEM

UI must follow Dayem brand identity:

- Primary: Yellow (#F4C400)
- Secondary: Black (#111111)
- Layout: RTL (Right-to-Left)
- Language: Arabic (Egyptian colloquial)

---

### 10.2 UI COMPONENTS (MANDATORY)

Reusable components must be created:

- Buttons
- Tables
- Forms
- Modals

---

### 10.3 INTERACTION MODEL

Frontend MUST use:
- AJAX (fetch API)
- JSON-based communication

Full page reloads should be minimized.

---

## 11. PERFORMANCE RULES

- Use pagination for large data tables
- Optimize database queries
- Avoid unnecessary API calls
- Compress large files when possible

---

## 12. ERROR HANDLING

### Development:
- Show detailed errors

### Production:
- Hide errors from users
- Log errors internally

---

## 13. BACKUP POLICY

- Database backup must run every 4 days (cron job)
- Backup must be restorable

---

## 14. LOCALIZATION

- Language: Arabic only
- Layout: RTL only
- Currency: Egyptian Pound (EGP)
- Tone: Egyptian colloquial

---

## 15. SCALABILITY REQUIREMENTS

The system MUST be designed to support future:

- API layer
- Mobile applications
- Multi-branch support
- Attendance system integration

---

## 16. DEVELOPMENT DISCIPLINE

### NEVER:
- Skip validation
- Mix frontend and backend logic
- Ignore architecture rules
- Hardcode sensitive data

### ALWAYS:
- Write clean, structured code
- Follow MVC strictly
- Validate all inputs
- Think about scalability

---

## 17. FINAL PRINCIPLE

Dayem ERP is not just an MVP.

It is the foundation of a scalable ERP platform.

Every decision must support:
- Maintainability
- Security
- Future growth