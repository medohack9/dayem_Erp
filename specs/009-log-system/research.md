# Research: Log System

**Feature**: 009-log-system
**Date**: 2026-05-01

## Research Tasks

### 1. Log Table Structure

**Decision**: Use existing `logs` table from Phase 1 with no modifications.

**Rationale**: The logs table was created in Phase 1 with all necessary fields: id, user_id, action, entity_type, entity_id, details (JSON), ip_address, created_at. No additional fields needed for viewing/filtering/exporting.

**Alternatives considered**:
- Add indexed columns for filtering: Rejected — existing indexes sufficient for expected data volume (< 200 users × daily actions).

### 2. Filter Dropdown Data Sources

**Decision**: Populate filter dropdowns dynamically from distinct values in the logs table.

**Rationale**: Action types and entity types are free-form strings set by each module. Populating from actual data ensures dropdowns reflect reality without hardcoding. User dropdown from users table (active + deleted users shown with status indicator).

**Alternatives considered**:
- Hardcode action/entity types: Rejected — would require updates when modules add new actions.
- Separate config table: Rejected — over-engineering for MVP.

### 3. CSV Export Implementation

**Decision**: Generate CSV directly in PHP using stream output with proper Arabic encoding (UTF-8 with BOM for Excel compatibility).

**Rationale**: Simple implementation without external libraries. UTF-8 BOM ensures Excel displays Arabic correctly. Stream to php://output avoids memory issues with large exports.

**Alternatives considered**:
- External CSV library: Rejected — constitution forbids external JS libraries; PHP native fputcsv sufficient.
- Chunked export for very large datasets: Deferred — 10,000 limit keeps memory manageable.

### 4. Pagination Strategy

**Decision**: 50 items per page (higher density than other modules' 15 items).

**Rationale**: Logs are narrow rows (text only, no images). Higher density matches audit tool expectations. Spec explicitly requests 50 per page.

**Alternatives considered**:
- Standard 15 items: Rejected — would create excessive pagination for log-heavy systems.

### 5. Entity Linking in Log Details

**Decision**: Display entity information without clickable links to the entity. Show entity type + ID + "Deleted" indicator if applicable.

**Rationale**: Logs are historical records. Linking to entities that may be deleted creates broken UX. Simpler to show what the log recorded without navigation.

**Alternatives considered**:
- Link to entity if exists: Rejected — adds complexity for limited value; audit logs should show what happened, not navigate.

### 6. JSON Details Display

**Decision**: Parse JSON details field and display as key-value pairs in a table format within a modal.

**Rationale**: JSON is already stored. Pretty-printing as key-value pairs is readable for admins. Handle common patterns (before/after values for updates) with special formatting.

**Alternatives considered**:
- Raw JSON display: Rejected — not user-friendly for non-technical admins.
- Specialized diff view: Deferred — key-value display sufficient for MVP.