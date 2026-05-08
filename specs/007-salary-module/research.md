# Research: Salary Module

## Decision 1: Salary Configuration Schema
**Decision**: Use a structured but flexible approach for `salary_configs`.
**Rationale**: The user requested "user_id, salary + more data". To support Egyptian payroll standards without hardcoding complex tax laws, we will use a column-based approach for primary components and a JSON field for miscellaneous allowances/deductions if needed for future expansion. However, for the MVP, we will stick to:
- `basic_salary`
- `allowances`
- `deductions`
- `net_salary` (stored to avoid repeated recalculation)
- `effective_date` (to track when this config started)

**Alternatives Considered**:
- Key-Value pair table (more flexible but harder to query for summaries).
- Single `salary` field with everything else in a JSON blob (less performant for reports).

## Decision 2: Recalculation Logic
**Decision**: Atomic update of existing `Unpaid` records.
**Rationale**: When an admin clicks "Recalculate", the system will fetch the current configuration for that employee and update the existing `salaries` record for that month, *only if* the status is `Unpaid`. This preserves the existing record ID and audit history.
**Alternatives Considered**: 
- Delete and Re-generate: Risky if IDs are used elsewhere; more disruptive.

## Decision 3: Summary Statistics Optimization
**Decision**: Use a single aggregate query per filter set.
**Rationale**: To meet the < 2s load time goal, summary cards (Total Payroll, Paid, Unpaid) will be calculated using `SUM()` and `COUNT()` in one SQL pass rather than iterating in PHP.

## Decision 4: Currency & Formatting
**Decision**: Use `number_format($amount, 2)` with "ج.م" suffix.
**Rationale**: Consistent with existing Department module UI and user clarification.

## Decision 5: "More Data" components
**Decision**: Based on common Egyptian payroll practices, we will include fields for:
- `user_id` (FK)
- `basic_salary`
- `allowances` (Housing, Transport, etc. - can be grouped for MVP)
- `deductions` (Social insurance, Tax - can be grouped for MVP)
- `notes`
- `updated_at` (to see when the pay scale was last adjusted)
