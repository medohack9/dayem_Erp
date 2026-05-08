# Feature Specification: Authentication

**Feature Branch**: `002-authentication`  
**Created**: 2026-04-25  
**Status**: Draft  
**Input**: Phase 2 of the Dayem ERP implementation plan — secure login system with email/phone authentication, password hashing, session management, logout, and role detection (admin/employee). Builds on the core system foundation (Phase 1).

## Clarifications

### Session 2026-04-25

- Q: How should suspended/inactive users be handled at login? → A: Block login with a generic error (same message as wrong credentials — prevents status enumeration)
- Q: Can a user have multiple active sessions simultaneously? → A: Single session only (new login destroys all previous sessions for that user)
- Q: Should failed login attempts be rate-limited? → A: Account lockout after 5 consecutive failed attempts (unlocked by admin or after 15 minutes)
- Q: Where should authentication logs be stored? → A: Dedicated `auth_logs` table (separate from `error_logs`, stores login attempts with user_id, IP, outcome)

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Log In to the System (Priority: P1)

An employee or admin opens the Dayem ERP system and needs to authenticate using their email address or phone number along with a password. The system verifies the credentials, creates a session, and redirects the user to the home page with the sidebar displaying their name and role. If the credentials are incorrect, the system shows a clear Arabic error message without revealing whether the email/phone or password is wrong. Suspended or terminated accounts are also blocked with the same generic error message to prevent account status enumeration.

**Why this priority**: Without authentication, no other module can function securely. Login is the gateway to the entire system and must work before any role-based features are meaningful.

**Independent Test**: Can be fully tested by visiting the login page, entering valid credentials, and verifying successful redirection to the home page with session persistence. Invalid credentials should produce an Arabic error message. Suspended accounts should produce the same generic error.

**Acceptance Scenarios**:

1. **Given** a registered user exists in the database, **When** they submit valid email and password on the login page, **Then** the system authenticates them, creates a session, and redirects to the home page
2. **Given** a registered user exists, **When** they submit valid phone number and password on the login page, **Then** the system authenticates them using the phone number field and redirects to the home page
3. **Given** an invalid email/phone or wrong password is submitted, **When** the login form is submitted, **Then** the system displays a generic Arabic error message "بيانات الدخول غير صحيحة" (Incorrect login credentials) without specifying which field is wrong
4. **Given** an authenticated user, **When** they navigate to the login page again, **Then** they are automatically redirected to the home page (login page is inaccessible while logged in)
5. **Given** the login form, **When** a user attempts to submit without filling required fields, **Then** the system validates the inputs and shows Arabic validation messages for empty fields
6. **Given** a user with status "suspended" or "terminated", **When** they attempt to log in with correct credentials, **Then** the system displays the same generic error message "بيانات الدخول غير صحيحة" without revealing the account status
7. **Given** a user has made 5 consecutive failed login attempts, **When** they attempt to log in again (even with correct credentials), **Then** the account is locked and a message is shown indicating the account is temporarily locked

---

### User Story 2 - Log Out of the System (Priority: P1)

An authenticated user clicks the logout button in the header/sidebar. The system destroys their session, clears session data, and redirects them to the login page. The user can no longer access protected pages — attempting to do so redirects them back to the login page.

**Why this priority**: Logout is the counterpart to login and is critical for session security. Without proper logout, sessions remain active and accessible.

**Independent Test**: Can be tested by logging in, clicking logout, and verifying the session is destroyed and pages are inaccessible.

**Acceptance Scenarios**:

1. **Given** an authenticated user, **When** they click the logout button, **Then** the session is destroyed and they are redirected to the login page
2. **Given** a user has just logged out, **When** they attempt to access any protected page via URL, **Then** they are redirected to the login page
3. **Given** a user has logged out, **When** they use the browser's back button, **Then** they cannot access the previous authenticated page (session is invalidated)

---

### User Story 3 - Role-Based Access After Login (Priority: P2)

After logging in, the system detects whether the user is an admin or an employee. The sidebar and available navigation links change based on the user's role. Admin users see all modules in the sidebar; employee users see only the modules they have access to (their own tasks, tickets, files, and salary). Access control is enforced on the backend — a direct URL request to an admin-only page by an employee returns a 403 forbidden page.

**Why this priority**: Role detection is essential for securing the system but depends on login (P1) working first. It can be developed and tested immediately after login/logout.

**Independent Test**: Can be tested by logging in as admin, verifying all sidebar links are visible, then logging in as employee, verifying restricted sidebar and 403 on admin URLs.

**Acceptance Scenarios**:

1. **Given** an admin user logs in, **When** the home page loads, **Then** all module links are visible in the sidebar (departments, employees, tasks, tickets, salary, files, logs, dashboard)
2. **Given** an employee user logs in, **When** the home page loads, **Then** only permitted links are visible in the sidebar (home, tasks, tickets, files, salary)
3. **Given** an employee user, **When** they manually navigate to an admin-only URL (e.g., /departments), **Then** the system returns a 403 forbidden page with an Arabic message
4. **Given** an admin user, **When** they access any module page, **Then** they have full read and write access to all data

---

### User Story 4 - Session Timeout and Security (Priority: P2)

A user who has been inactive for 30 minutes has their session automatically expired. When they try to interact with the system, they are shown an Arabic session-expired message and redirected to the login page. Each user may have only one active session at a time — logging in from a new device destroys any previous session. CSRF tokens are validated on all form submissions to prevent cross-site request forgery attacks.

**Why this priority**: Session security is critical for protecting user accounts but builds on the core session infrastructure established in Phase 1. It is tested alongside login but documented separately for clarity.

**Independent Test**: Can be tested by logging in, waiting for session timeout (or simulating it), and verifying the expired session redirect. Single-session enforcement can be tested by logging in from two browsers and verifying the first session is invalidated.

**Acceptance Scenarios**:

1. **Given** an active session, **When** 30 minutes of inactivity pass, **Then** the session expires and the user is redirected to a session-expired page with a login link
2. **Given** an active session, **When** the user interacts with the system before 30 minutes, **Then** the inactivity timer resets (session persists)
3. **Given** any form submission, **When** the CSRF token is missing or invalid, **Then** the request is rejected with a 403 status and an Arabic unauthorized message
4. **Given** a valid session, **When** the user logs in successfully, **Then** the session ID is regenerated to prevent session fixation attacks
5. **Given** an active session on device A, **When** the same user logs in from device B, **Then** the session on device A is invalidated and device A is redirected to the login page on the next request

---

### Edge Cases

- A user enters an email that doesn't exist in the system → same generic error as wrong password (prevents enumeration)
- A user with suspended or terminated status attempts login → blocked with generic error (prevents account status enumeration)
- A user logs in from a second device → previous session is destroyed (single-session enforcement)
- A password longer than 72 characters is submitted → bcrypt truncates at 72 bytes; the system should accept it without error but only the first 72 bytes are used for verification
- Database connection fails during login → graceful error page (handled by existing ErrorHandler from Phase 1)
- A user's role is changed by an admin while they have an active session → the session retains the original role until the user logs out and logs back in; role changes take effect on next login
- Account lockout after 5 failed attempts → user cannot log in even with correct credentials until lockout expires (15 minutes) or an admin unlocks the account

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST provide a login page accessible at `/auth/login` with fields for email/phone and password
- **FR-002**: System MUST authenticate users by matching the provided identifier (email or phone) against the database and verifying the password using bcrypt hashing
- **FR-003**: System MUST display a single generic Arabic error message for all failed login attempts without revealing which field is incorrect, including when the account is suspended or terminated
- **FR-004**: System MUST create a session upon successful authentication containing user ID, name, email, role, and last activity timestamp
- **FR-005**: System MUST regenerate the session ID after successful login to prevent session fixation
- **FR-006**: System MUST redirect unauthenticated users attempting to access protected pages to the login page
- **FR-007**: System MUST redirect already-authenticated users away from the login page to the home page
- **FR-008**: System MUST provide a logout action that destroys the session and redirects to the login page
- **FR-009**: System MUST enforce role-based access control where admin users can access all modules and employee users can only access their own data modules
- **FR-010**: System MUST restrict employee access to admin-only pages and return a 403 forbidden response with Arabic messaging
- **FR-011**: System MUST validate CSRF tokens on all state-changing requests (POST, PUT, DELETE)
- **FR-012**: System MUST expire sessions after 30 minutes of inactivity and redirect the user to a session-expired page
- **FR-013**: System MUST store passwords using bcrypt (or equivalent secure hashing) — never in plain text
- **FR-014**: System MUST update the sidebar navigation links based on the authenticated user's role
- **FR-015**: System MUST validate login form inputs server-side (non-empty fields, valid email format when email is provided)
- **FR-016**: System MUST log all login attempts (successful and failed) in a dedicated `auth_logs` table with timestamp, IP address, user agent, and outcome
- **FR-017**: System MUST display validation errors in Arabic on the login form for empty or invalid inputs
- **FR-018**: System MUST prevent access to protected routes via a middleware/authorization check before the controller action executes
- **FR-019**: System MUST enforce single-session policy — when a user logs in, any existing session for that user is destroyed, ensuring only one active session per user at a time
- **FR-020**: System MUST lock user accounts after 5 consecutive failed login attempts, preventing login even with correct credentials until the lockout expires (15 minutes) or an admin manually unlocks the account
- **FR-021**: System MUST block login attempts for users with "suspended" or "terminated" status, returning the same generic error message as invalid credentials to prevent account status enumeration

### Key Entities

- **User**: Represents a system user (admin or employee) who can authenticate. Key attributes: name, email, phone, hashed password, role (admin/employee), status (active/suspended/terminated), failed_login_attempts (counter for lockout), locked_until (timestamp for lockout expiration). Created during admin user management but referenced heavily during authentication.
- **Auth Log**: Dedicated table recording every authentication attempt (successful and failed) with user_id (nullable for unknown users), identifier attempted, IP address, user agent, timestamp, and outcome (success/failed/locked). Separate from error_logs for clean querying and security auditing.
- **Session**: Represents an authenticated user's active session. Stores user ID, role, and activity data. Managed by the existing Session class from Phase 1, extended with user-specific data upon login. Single-session enforcement means only one active session per user_id at a time.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A user can log in within 3 seconds of submitting valid credentials
- **SC-002**: Invalid login attempts always show the same generic error message, regardless of whether the email/phone or password is incorrect, or the account is suspended/terminated
- **SC-003**: Sessions expire exactly after 30 minutes of inactivity with no grace period
- **SC-004**: CSRF-protected forms reject 100% of requests missing or having invalid tokens
- **SC-005**: Employee users cannot access any admin-only page, receiving a 403 response for every attempted access
- **SC-006**: The login page is fully accessible and usable in Arabic RTL layout
- **SC-007**: Passwords are stored exclusively as bcrypt hashes — zero plain-text password storage
- **SC-008**: After 5 consecutive failed login attempts, the account is locked and login is blocked for 15 minutes or until admin unlocks
- **SC-009**: When a user logs in from a new device, their previous session is invalidated — only one active session per user at any time

## Assumptions

- The user table will be created in this phase; the first admin user will be seeded via migration
- Login supports both email and phone number as the identifier field (users can use either)
- Password hashing uses PHP's `password_hash()` with bcrypt (default algorithm)
- The existing Session class from Phase 1 will be extended to store user-specific session data
- The existing CSRF token infrastructure from Phase 1 will be used for form protection
- The existing base layout (header, sidebar, error pages) from Phase 1 will be reused
- Role values are limited to 'admin' and 'employee'; no intermediate roles are needed for MVP
- The initial admin account credentials will be set via database seed (no admin registration UI in this phase)
- Employee user creation will be handled by the Employee module (Phase 4), not by the Authentication module
- Authentication logging uses a dedicated `auth_logs` table separate from `error_logs`
- Session timeout logic already exists from Phase 1; this phase extends it with user context
- Single-session enforcement means only one active session per user; new login destroys the previous session
- Account lockout expires after 15 minutes automatically; admins can also manually unlock via direct database update (no admin UI for unlock in this phase)
- Role changes take effect on next login; active sessions retain the original role