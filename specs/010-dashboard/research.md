# Research: Dashboard Feature

**Date**: 2026-05-01
**Branch**: 010-dashboard

## Research Items

### 1. Chart.js Integration via CDN

**Decision**: Use Chart.js 4.x via CDN with inline configuration

**Rationale**: 
- Constitution permits one-time exception for visualization library
- CDN avoids local file management and version conflicts
- Chart.js has excellent RTL support and Arabic label handling
- ~200KB minified, acceptable for dashboard use case

**Alternatives Considered**:
- D3.js: Too complex for bar charts, steeper learning curve
- Plotly: Heavier bundle, overkill for simple bar charts
- Custom Canvas: Would violate "no external JS" spirit, more maintenance

**Implementation Notes**:
- Load Chart.js in layout only when `pageScripts` includes it
- Use `dir: 'rtl'` option for chart container
- Arabic labels render correctly with UTF-8 encoding

---

### 2. Arabic Week Labels for Chart X-Axis

**Decision**: Use relative Arabic labels (الأسبوع الماضي, منذ أسبوعين, etc.)

**Rationale**:
- More intuitive for users scanning trends
- Requires less mental calculation than date ranges
- Matches spec clarification: "Relative labels (الأسبوع الماضي, منذ أسبوعين, etc.)"

**Label Mapping** (8 weeks):
| Week | Arabic Label |
|------|-------------|
| -1 (last) | الأسبوع الماضي |
| -2 | منذ أسبوعين |
| -3 | منذ 3 أسابيع |
| -4 | منذ 4 أسابيع |
| -5 | منذ 5 أسابيع |
| -6 | منذ 6 أسابيع |
| -7 | منذ 7 أسابيع |
| -8 | منذ 8 أسابيع |

---

### 3. Chart Data API Design

**Decision**: Create dedicated AJAX API endpoints for chart data

**Rationale**:
- Keeps dashboard view lightweight
- Allows independent chart refresh if needed later
- Follows existing project pattern (API routes for JSON responses)

**Endpoints**:
| Method | Endpoint | Response |
|--------|----------|----------|
| GET | `/api/dashboard/stats` | All 4 KPI values + status breakdown |
| GET | `/api/dashboard/tasks-chart` | 8 weeks of task created/completed counts |
| GET | `/api/dashboard/tickets-chart` | 8 weeks of ticket created/closed counts |

**Response Format**:
```json
{
  "labels": ["الأسبوع الماضي", "منذ أسبوعين", ...],
  "created": [5, 8, 3, ...],
  "completed": [4, 6, 5, ...]
}
```

---

### 4. Week Period Calculation

**Decision**: Monday-Sunday week periods, aligned with Arabic business convention

**Rationale**:
- Matches spec assumption: "Week periods are calculated as Monday–Sunday"
- Common in Middle Eastern business context
- Easier to calculate with PHP's `strtotime('monday this week')`

**Implementation**:
```php
// Get start of week for a given date
$weekStart = date('Y-m-d', strtotime('monday this week', strtotime($date)));
```

---

### 5. KPI Card Hover Tooltip

**Decision**: Use CSS + Tailwind for hover tooltips, no additional JS

**Rationale**:
- Native hover states are reliable and performant
- Tailwind's `group-hover` pattern works well
- No additional JS library needed

**Implementation Pattern**:
```html
<div class="group relative">
  <!-- KPI card content -->
  <div class="absolute hidden group-hover:block ...">
    <!-- Tooltip with status breakdown -->
  </div>
</div>
```

---

### 6. Employee Dashboard Personalization

**Decision**: Same view template with conditional rendering based on role

**Rationale**:
- Avoids code duplication
- Single source of truth for dashboard UI
- Controller passes different data based on Auth::role()

**Personalized KPIs for Employee**:
| KPI | Description |
|-----|-------------|
| My Tasks | Count of tasks assigned to user |
| My Tickets | Count of tickets created by user |
| My Files | Count of files uploaded by user |
| Storage Used | MB used of quota |

---

## Summary

All research items resolved. No NEEDS CLARIFICATION items remain. Ready for Phase 1 design.