# Feature Specification: File System

**Feature Branch**: `008-file-system`  
**Created**: 2026-05-01  
**Status**: Draft  
**Input**: Phase 8 of the Dayem ERP implementation plan — user file management with upload, metadata (name, priority, notes), user-only access, admin full access, and search/filter capabilities.

## Clarifications

### Session 2026-05-01

- Q: Can employees upload multiple files with the same display name? → A: Yes - duplicate names allowed, files distinguished by internal ID.
- Q: Can users replace/update an existing file with a new version? → A: No - delete and re-upload (edit metadata only).
- Q: Maximum storage limit per user? → A: Employees: 500MB default; Admins: unlimited; Admins can allocate additional storage to employees.
- Q: Show storage usage indicator in file list? → A: Yes - show usage bar/indicator.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Upload Files with Metadata (Priority: P1)

As an employee, I want to upload files with custom metadata (name, priority, notes) so I can store and organize my documents in the system. The file is uploaded to my personal file storage and I can immediately see it in my file list.

**Why this priority**: File upload is the core entry point for the module. Without it, no other feature (viewing, editing, filtering, downloading) can function.

**Independent Test**: Can be fully tested by logging in as an employee, navigating to the files page, uploading a file with metadata, and verifying it appears in the file list. Delivers immediate value by enabling employees to store documents.

**Acceptance Scenarios**:

1. **Given** an employee is logged in, **When** they navigate to the files page and upload a file with name, priority, and notes, **Then** the file is stored and appears in their file list.
2. **Given** an employee is uploading a file, **When** they submit without a file or name, **Then** a validation error is shown in Arabic indicating the required fields.
3. **Given** an employee is uploading a file, **When** they attach a file exceeding 5MB or an unsupported file type, **Then** a validation error is shown indicating the file constraint.
4. **Given** an admin is logged in, **When** they upload a file, **Then** the file is stored with their user ID and appears in the admin's personal file list.

---

### User Story 2 - View and Edit Own Files (Priority: P1)

As an employee, I want to view my uploaded files in a list and edit their metadata (name, priority, notes) so I can keep my documents organized. I can see file details including upload date, file size, and type. I can only see and edit files I uploaded myself.

**Why this priority**: Viewing and editing is essential for document management after upload — users need to manage their files immediately after creating them.

**Independent Test**: Can be tested by uploading a file, viewing it in the list, editing its metadata, and verifying the changes are saved.

**Acceptance Scenarios**:

1. **Given** an employee has uploaded files, **When** they view the files page, **Then** they see only their own files with name, priority, upload date, and file size.
2. **Given** an employee views their file list, **When** they click edit on a file, **Then** they can modify the name, priority, and notes fields.
3. **Given** an employee edits a file's metadata, **When** they save the changes, **Then** the updated information is displayed in the file list.
4. **Given** an employee tries to access another employee's file via direct URL, **Then** the system denies access with a 403 response.
5. **Given** an employee views a large number of files, **When** the list loads, **Then** results are paginated (15 per page).

---

### User Story 3 - Admin Views All Files (Priority: P1)

As an admin, I want to view all files uploaded by all employees so I can monitor and manage the document storage system. I can see who uploaded each file, when it was uploaded, and all metadata. I have full access to view, edit, or delete any file.

**Why this priority**: Admin oversight is a core requirement — admins must be able to see all user files for management and support purposes.

**Independent Test**: Can be tested by having multiple employees upload files, then logging in as admin and verifying all files are visible with employee names.

**Acceptance Scenarios**:

1. **Given** multiple employees have uploaded files, **When** an admin views the files page, **Then** they see all files from all employees with uploader name.
2. **Given** an admin views the file list, **When** they click on any file, **Then** they can view full details including metadata and download the file.
3. **Given** an admin views the file list, **When** they edit any file's metadata, **Then** the changes are saved and visible to the original uploader.
4. **Given** an admin deletes a file, **Then** the file is soft-deleted and no longer visible to the employee.

---

### User Story 4 - Search and Filter Files (Priority: P2)

As an employee or admin, I want to search and filter files by name, priority, and date so I can quickly find specific documents. Admins can additionally filter by employee name. Results update in real-time as I type or select filters.

**Why this priority**: Search and filter is critical for usability as the number of files grows, but depends on files existing first.

**Independent Test**: Admin filters files by priority "عالية" (High) and only high-priority files appear. Employee searches by filename and matching results are shown.

**Acceptance Scenarios**:

1. **Given** multiple files exist, **When** a user types in the search box, **Then** files matching the name are displayed.
2. **Given** multiple files exist with different priorities, **When** a user filters by priority, **Then** only files with that priority are shown.
3. **Given** an admin views files, **When** they filter by employee name, **Then** only files uploaded by that employee are shown.
4. **Given** a user applies multiple filters, **When** they clear all filters, **Then** the full file list returns (paginated).

---

### User Story 5 - Download Files (Priority: P2)

As an employee, I want to download files I uploaded so I can retrieve my stored documents. As an admin, I want to download any file in the system. Downloads are secure and logged.

**Why this priority**: Download capability completes the document management cycle, but requires files to exist first.

**Independent Test**: Employee uploads a file, then downloads it and verifies the content matches the original.

**Acceptance Scenarios**:

1. **Given** an employee has uploaded a file, **When** they click download, **Then** the file is downloaded to their device.
2. **Given** an admin views any file, **When** they click download, **Then** the file downloads successfully.
3. **Given** an employee tries to download another employee's file via direct URL, **Then** access is denied with a 403 response.
4. **Given** a file is downloaded, **When** the download completes, **Then** the action is logged in the audit trail.

---

### User Story 6 - Admin Manages File Priorities (Priority: P3)

As an admin, I want to change file priorities to organize and highlight important documents across all employees. This allows me to mark certain files as high priority for attention or low priority for archiving.

**Why this priority**: Priority management is useful for organization but depends on the core file management workflow being established first.

**Independent Test**: Admin changes a file's priority from "متوسطة" (Medium) to "عالية" (High) and the change is reflected in the list and visible to the employee.

**Acceptance Scenarios**:

1. **Given** an admin views any file, **When** they change the priority, **Then** the new priority is saved and displayed.
2. **Given** an admin changes a file's priority, **When** the employee views their file list, **Then** they see the updated priority.

---

### Edge Cases

- What happens when an employee tries to access another employee's file directly via URL? → The system denies access with a 403 response.
- What happens when a file upload fails (server error, file too large)? → An Arabic error is shown indicating the upload failed, and the file is not saved.
- What happens when an admin deletes a file? → The file is soft-deleted (hidden from active lists but preserved in the database).
- What happens when the uploaded file type is not in the allowed list? → A validation error is shown indicating the supported file types.
- What happens when an employee's account is suspended or terminated? → Their existing files remain accessible to admins; they cannot upload new files.
- What happens when storage quota is exceeded? → An Arabic error indicates storage limit reached. Admins can increase employee quota.
- What happens when a file name contains special characters or is very long? → The name is sanitized and truncated if necessary, with the original preserved.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Employees MUST be able to upload files with: file (required), name (required, max 200 chars), priority (required: منخفضة / متوسطة / عالية), and notes (optional, max 1000 chars).
- **FR-002**: File uploads MUST be validated for type and size. Allowed types: images (jpg, jpeg, png, gif), documents (pdf, doc, docx, xls, xlsx), and archives (zip, rar). Maximum file size: 5MB.
- **FR-003**: Files MUST be stored securely outside the web root and served via a controlled download endpoint that checks permissions.
- **FR-004**: Employees MUST only see and access files they uploaded; admins MUST see all files across all employees.
- **FR-005**: File metadata (name, priority, notes) MUST be editable by the file owner (employee) or any admin.
- **FR-006**: System MUST support filtering files by: name search, priority, and date range. Admin view MUST additionally support filtering by employee name.
- **FR-007**: File list MUST support pagination (15 items per page) for both employee and admin views.
- **FR-008**: System MUST soft-delete files (deleted_at column). Hard delete is forbidden.
- **FR-009**: All file actions (upload, edit metadata, download, delete) MUST be logged in the audit log with user ID, timestamp, and action details.
- **FR-010**: CSRF protection MUST be enforced on all file upload and edit forms.
- **FR-011**: All file-related pages and messages MUST be in Arabic (RTL layout, Egyptian colloquial tone).
- **FR-012**: Admins MUST be able to delete (soft-delete) any file; employees MUST only be able to delete their own files.
- **FR-013**: System MUST prevent employees from accessing files uploaded by other employees via direct URL (403 forbidden response).
- **FR-014**: When an employee's status changes to suspended or terminated, their existing files MUST remain accessible to admins but the employee MUST NOT be able to upload new files.
- **FR-015**: Download endpoint MUST verify user permissions before serving any file (employee: own files only; admin: all files).
- **FR-016**: File metadata MUST include: original_filename, stored_filename, file_path, file_size, file_type, user_id, created_at, updated_at, deleted_at.
- **FR-017**: System MUST allow duplicate display names per user; files are distinguished by unique internal IDs, not by name.
- **FR-018**: File replacement/versioning is NOT supported; users MUST delete and re-upload to change file content. Metadata editing does NOT modify the file itself.
- **FR-019**: System MUST enforce storage quotas: 500MB default for employees, unlimited for admins. Admins MUST be able to allocate additional storage to individual employees.
- **FR-020**: System MUST display current storage usage (e.g., "320MB / 500MB used") in the file list view. Admin view MUST show each employee's usage and quota.

### Key Entities

- **File**: Represents an uploaded document. Key attributes: name (display name, max 200 chars), original_name (original filename), file_path (secure storage path), file_size (bytes), file_type (MIME type), priority (منخفضة / متوسطة / عالية), notes (max 1000 chars), user_id (uploader), created_at, updated_at, deleted_at. Relationship: belongs to one User (uploader).
- **User Storage**: Each user has a storage_quota field (bytes, default 500MB for employees, null/unlimited for admins). Admins can modify storage_quota for employees.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An employee can upload a file with metadata in under 10 seconds.
- **SC-002**: File list with 500+ records loads within 2 seconds with pagination.
- **SC-003**: 100% of file access attempts are verified against ownership (employee) or admin role — no unauthorized access possible.
- **SC-004**: File downloads complete within 5 seconds for files up to 5MB.
- **SC-005**: 100% of file actions (upload, edit, download, delete) are logged in the audit trail.
- **SC-006**: Employees can NEVER see or access files uploaded by other employees — verified by both UI restrictions and backend enforcement.

## Assumptions

- The existing auth system (Phase 2) and employee module (Phase 4) are already complete and functional.
- File storage uses the server filesystem with a secure directory structure; cloud storage is out of scope for MVP.
- Arabic language throughout, using Egyptian colloquial for labels and messages.
- Pagination follows the existing 15-items-per-page convention established in previous modules.
- Audit logging uses the same generic `logs` table already in place.
- Priority levels are fixed (منخفضة, متوسطة, عالية) and not user-configurable.
- Soft deletion is used for files — records are marked as deleted but preserved in the database.
- Only image, document, and archive file types are allowed (jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, zip, rar).
- The existing CSRF protection infrastructure from Phase 1 will be used for all file forms.
- File name sanitization handles special characters; original filename is preserved in metadata.
- Storage quota: 500MB default for employees, unlimited for admins. Admins can allocate additional storage to individual employees. Usage displayed with visual indicator.
- When an employee is suspended or terminated, they can still view their existing files but cannot upload new files.