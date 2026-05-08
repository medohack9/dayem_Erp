# Tasks: File System

**Input**: Design documents from `/specs/008-file-system/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: No automated test framework — manual testing via browser per project convention.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Database schema, user table extension, and file system setup for the files module

- [x] T001 Create migration file `database/migrations/010_files.sql` with `files` table per data-model.md and add `storage_quota` column (BIGINT, default 524288000 for 500MB) to `users` table
- [x] T002 [P] Create upload directory `storage/uploads/files/` for file storage
- [x] T003 Add file routes to `config/routes.php` per contracts/http-routes.md

**Checkpoint**: Migration executed, upload directory exists, routes registered

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core model that ALL user stories depend on. MUST be complete before any user story work begins.

- [x] T004 Create `app/models/FileModel.php` with methods: findAllActive (with search/filter/pagination by priority, user_id, date range), findById, findByUserId, create, update, softDelete, getStorageUsage (SUM file_size for user), checkQuota (compare usage vs quota), generateStoredName (UUID + timestamp), validateFileType, validateFileSize — following TicketModel pattern

**Checkpoint**: FileModel created and queryable. User story implementation can begin.

---

## Phase 3: User Story 1 - Upload Files with Metadata (Priority: P1) 🎯 MVP

**Goal**: Employees can upload files with custom metadata (name, priority, notes). Storage quota is enforced. Upload rejects files exceeding 5MB or unsupported types.

**Independent Test**: Login as employee → navigate to /files → upload a file with name, priority, notes → verify it appears in your file list. Try uploading invalid file type → see Arabic error.

### Implementation for User Story 1

- [x] T005 [US1] Create `app/controllers/FileController.php` with `createForm` method that renders the upload form with priority dropdown and file input
- [x] T006 [US1] Create `app/views/files/create.php` with RTL Arabic form: name input (required, max 200 chars), priority dropdown (منخفضة / متوسطة / عالية, default متوسطة), notes textarea (optional, max 1000 chars), file input (required, max 5MB), allowed types info text, CSRF token, submit button — Dayem brand styling
- [x] T007 [US1] Add `create` method to `app/controllers/FileController.php`: validate CSRF, validate name (required, max 200), priority (required, valid enum), notes (optional, max 1000), validate file presence, validate MIME type against allowed list (jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, zip, rar), validate file size ≤ 5MB, check user storage quota via FileModel::checkQuota before upload, generate stored_name via FileModel::generateStoredName, move file to `storage/uploads/files/`, save file metadata via FileModel::create, log action via LogModel, return JSON with file data and updated storage info
- [x] T008 [US1] Create `public/js/files.js` with AJAX handler for file upload: collect form data + file, validate file type and size client-side before upload, show upload progress indicator, show Arabic error messages for validation failures (wrong type, too large, quota exceeded), handle success with redirect to file list, handle error responses

**Checkpoint**: Employee can upload a file with metadata. Validation rejects oversized files, wrong types, and quota-exceeded uploads. File appears in database and storage directory. Arabic error messages display correctly.

---

## Phase 4: User Story 2 - View and Edit Own Files (Priority: P1)

**Goal**: Employees can view their uploaded files in a list with storage usage indicator. They can edit metadata (name, priority, notes) on their own files. Pagination shows 15 files per page.

**Independent Test**: Upload multiple files → view file list → see storage usage bar → edit a file's name → verify change saved. Try accessing another user's file via URL → get 403.

### Implementation for User Story 2

- [x] T009 [US2] Add `index` method to `app/controllers/FileController.php`: load files via FileModel::findAllActive with search and priority filters, enforce RBAC (employee: filter to Auth::id(), admin: see all), calculate storage usage via FileModel::getStorageUsage, get user's storage_quota from session or UserModel, render index view with files, pagination, and storage data
- [x] T010 [US2] Create `app/views/files/index.php` with RTL Arabic file list: storage usage indicator bar (e.g., "320MB / 500MB" with percentage bar), search input, priority filter dropdown (الكل / منخفضة / متوسطة / عالية), file table with columns (اسم الملف, الأولوية, الحجم, التاريخ, إجراءات), priority badges with colors (عالية=red, متوسطة=yellow, منخفضة=green), pagination controls, upload button, edit/delete/download action buttons per row
- [x] T011 [US2] Add `editForm` method to `app/controllers/FileController.php`: load file by ID via FileModel::findById, enforce RBAC (employee: must own file — 403 otherwise, admin: can edit any), render edit view with file data
- [x] T012 [US2] Create `app/views/files/edit.php` with RTL Arabic form: name input, priority dropdown, notes textarea, original filename display (read-only), file size display (read-only), upload date display (read-only), note text explaining file content cannot be changed, CSRF token, save button, cancel link back to file list
- [x] T013 [US2] Add `update` method to `app/controllers/FileController.php`: validate CSRF, verify ownership (employee) or admin role, validate name/priority/notes, update via FileModel::update, log action, return JSON response
- [x] T014 [US2] Add file list AJAX handlers to `public/js/files.js`: filter changes trigger page reload with query params, search with debounce, pagination click handlers, edit form submission via fetch PUT, success/error toast notifications in Arabic

**Checkpoint**: Employee sees only their own files with storage usage bar. Pagination works at 15 per page. Edit form allows metadata changes. Employee cannot access another user's file edit form (403). Admin can edit any file.

---

## Phase 5: User Story 3 - Admin Views All Files (Priority: P1)

**Goal**: Admin sees all files from all employees with employee name filter. Admin sees each employee's storage usage. Admin can edit or delete any file.

**Independent Test**: Have multiple employees upload files → login as admin → see all files with uploader names. Filter by employee name → see only that employee's files. Edit any file → changes save.

### Implementation for User Story 3

- [x] T015 [US3] Update `app/controllers/FileController.php` index method for admin view: add employee name filter (search by user.name via JOIN), load user data for each file to display uploader name, pass employee list for filter dropdown, calculate storage usage per user for admin display
- [x] T016 [US3] Update `app/views/files/index.php` for admin view: add employee name filter dropdown (admin only), show uploader column in file table (admin only), show per-employee storage usage when filtering by employee, admin sees edit/delete buttons for all files (not just own)
- [x] T017 [US3] Update `app/controllers/FileController.php` update and delete methods: allow admin to update/delete any file (remove ownership restriction for admin role), log admin actions with target user ID

**Checkpoint**: Admin sees all files from all employees. Employee name filter works. Admin can edit/delete any file. Per-employee storage usage displays correctly.

---

## Phase 6: User Story 4 - Search and Filter Files (Priority: P2)

**Goal**: Users can search files by name, filter by priority and date range. Admin can additionally filter by employee name. Filters update in real-time.

**Independent Test**: Upload files with different names and priorities → search by name → see matching files. Filter by priority عالية → see only high priority. Admin filters by employee name → see only that employee's files.

### Implementation for User Story 4

- [x] T018 [US4] Add search and date filter support to `app/models/FileModel.php` findAllActive method: add LIKE search on file name, add date range filter (created_at BETWEEN date_from AND date_to), combine with existing priority and user_id filters
- [x] T019 [US4] Update `app/controllers/FileController.php` index method: accept date_from and date_to query params, pass date filters to FileModel::findAllActive, validate date format
- [x] T020 [US4] Add date range filter UI to `app/views/files/index.php`: date from input (type date), date to input (type date), clear filters button, date inputs styled consistently with other filters
- [x] T021 [US4] Update `public/js/files.js` with date filter handlers: date input changes trigger list refresh, clear filters button resets all filters to defaults, filters persist in URL for bookmarking

**Checkpoint**: Search by name works with partial matching. Priority filter works. Date range filter works. Admin employee name filter works. All filters can be combined. Clear filters resets the view.

---

## Phase 7: User Story 5 - Download Files (Priority: P2)

**Goal**: Employees can download their own files. Admins can download any file. Downloads are secure (permission checked) and logged. Files are served with original filename.

**Independent Test**: Upload a file → download it → verify content matches original. Admin downloads another employee's file → works. Employee tries to download another employee's file via direct URL → 403 error.

### Implementation for User Story 5

- [x] T022 [US5] Add `download` method to `app/controllers/FileController.php`: find file by ID via FileModel::findById, verify file exists and is not soft-deleted, enforce RBAC (admin = any file, employee = own file only — 403 otherwise), verify physical file exists in `storage/uploads/files/`, serve file with headers (Content-Type from file_type, Content-Disposition with original_name, Content-Length from file_size), log download action via LogModel, return 404 if file not found on disk
- [x] T023 [US5] Add download link to `app/views/files/index.php` file table: download button/icon with link to `/files/{id}/download`, opens in new tab or triggers browser download
- [x] T024 [US5] Add download handling to `public/js/files.js`: download button click triggers GET to /files/{id}/download, handles error responses (403, 404) with Arabic toast messages

**Checkpoint**: Employee can download their own files with original filename. Admin can download any file. Unauthorized download attempts return 403. Downloads are logged in audit trail. Files served securely (not directly accessible via URL).

---

## Phase 8: User Story 6 - Admin Manages File Priorities (Priority: P3)

**Goal**: Admin can change file priorities on any file. Priority changes are visible to the file owner.

**Independent Test**: Admin changes a file's priority from متوسطة to عالية → employee sees the updated priority badge in their file list.

### Implementation for User Story 6

- [x] T025 [US6] Extend `app/controllers/FileController.php` update method for priority changes: admin can update priority on any file, priority is part of the update validation (already in T013), ensure log includes priority change details

**Checkpoint**: Admin can change file priorities. Priority badge updates in real-time. Employee sees updated priority. Change is logged.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Soft delete, audit logging, storage quota management, sidebar integration, and verification

- [x] T026 [P] Add `delete` method to `app/controllers/FileController.php`: validate CSRF, verify ownership (employee) or admin role, soft-delete via FileModel::softDelete, calculate freed storage (file_size), log action with freed bytes, return JSON with success and storage_freed value
- [x] T027 [P] Add `storageInfo` method to `app/controllers/FileController.php`: return JSON with storage_used, storage_quota, formatted values (e.g., "320MB", "500MB"), percentage used, unlimited flag for admins
- [x] T028 [P] Add `updateQuota` method to `app/controllers/FileController.php`: admin-only, validate CSRF, validate storage_quota value (positive integer or null for unlimited), prevent setting quota below current usage (return 400 with Arabic error), update user record via UserModel, log action, return JSON success
- [x] T029 [P] Add file links to the sidebar navigation in `app/views/components/sidebar.php`: add "الملفات" link with file icon, ensure employee and admin both see the link (placed after tickets link)
- [x] T030 [P] Add audit logging calls to all FileController mutation methods: create → logAction('files', $id, 'upload'), update → logAction('files', $id, 'edit'), delete → logAction('files', $id, 'delete'), download → logAction('files', $id, 'download'), updateQuota → logAction('users', $userId, 'quota_change')
- [x] T031 Add delete confirmation modal to `app/views/files/index.php`: Arabic confirmation text "هل أنت متأكد من حذف هذا الملف؟", delete button, cancel button, modal triggered by delete button click
- [x] T032 Update `public/js/files.js` with delete and quota management handlers: delete button click shows modal, confirmed delete sends DELETE request, success shows toast with freed storage, admin quota update form submission, storage info refresh after uploads/deletes
- [ ] T033 Run quickstart.md verification checklist: migration 010 executed, employee file upload works, storage quota enforced, storage usage indicator shows correctly, employee sees only own files, admin sees all files, filters work (search, priority, date, employee name), edit metadata works, soft delete works, download works with original filename, 403 on unauthorized access, quota management by admin works, CSRF on all forms, Arabic RTL throughout

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Foundational — no dependencies on other stories
- **US2 (Phase 4)**: Depends on Foundational + US1 (needs upload working to have files to view)
- **US3 (Phase 5)**: Depends on Foundational + US2 (extends index view for admin)
- **US4 (Phase 6)**: Depends on Foundational + US2 (extends index view with filters)
- **US5 (Phase 7)**: Depends on Foundational + US2 (adds download to file list)
- **US6 (Phase 8)**: Depends on Foundational + US2 (uses update method for priority changes)
- **Polish (Phase 9)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (Upload Files)**: Can start after Foundational — No dependencies on other stories
- **US2 (View & Edit Own Files)**: Depends on US1 (needs files to exist for viewing)
- **US3 (Admin Views All Files)**: Depends on US2 (extends employee view)
- **US4 (Search & Filter)**: Depends on US2 (extends index view)
- **US5 (Download Files)**: Depends on US2 (adds download to file list)
- **US6 (Admin Manages Priorities)**: Depends on US2 (uses edit functionality)

### Within Each User Story

- Controller methods before views
- Views before JavaScript handlers
- Story complete before moving to next priority

### Parallel Opportunities

- T002, T003 can run in parallel with T001
- T026, T027, T028, T029, T030 can run in parallel in Polish phase

---

## Parallel Example: Setup

```text
# All setup tasks can run in parallel:
Task T001: Create migration file
Task T002: Create upload directory
Task T003: Add routes
```

## Parallel Example: Polish

```text
# All polish tasks can run in parallel:
Task T026: Add delete method
Task T027: Add storageInfo method
Task T028: Add updateQuota method
Task T029: Add sidebar link
Task T030: Add audit logging
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (migration + routes + upload directory)
2. Complete Phase 2: Foundational (FileModel)
3. Complete Phase 3: User Story 1 (upload with validation)
4. **STOP and VALIDATE**: Employee can upload a file with metadata, validation works, quota enforced
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Upload files with quota → Test → Deploy (MVP!)
3. Add User Story 2 → View & edit own files → Test → Deploy
4. Add User Story 3 → Admin view all files → Test → Deploy
5. Add User Story 4 → Search & filter → Test → Deploy
6. Add User Story 5 → Download files → Test → Deploy
7. Add User Story 6 → Admin priority management → Test → Deploy
8. Add Polish → Delete, quota management, audit logging → Final deploy

### Parallel Team Strategy

With multiple developers after Foundational phase:
- Developer A: US1 (Upload) → then US2 (View & Edit)
- Developer B: US4 (Search & Filter) → then US5 (Download)
- Developer C: US3 (Admin View) → then US6 (Priority)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Manual testing per project convention (no automated test framework)
- All views must be Arabic RTL with Dayem brand colors (#F4C400/#111111)
- File validation must be both client-side (JS) and server-side (PHP)
- Storage quota: 500MB default for employees, unlimited for admins
- Files stored securely outside web root, served via controlled endpoint