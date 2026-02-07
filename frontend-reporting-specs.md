# Frontend Reporting & Analytics Specification
## World-Class Church Management Dashboards

This document outlines the specific charts, graphs, and reports required for each module of the Prophetic Life Embassy application. These visualizations are designed to move beyond simple data entry to provide actionable insights for church leadership.

**Technical Stack:**
- **Frontend Framework:** Vue 3 (Composition API)
- **UI Library:** CoreUI for Vue (@coreui/vue)
- **Charting Engine:** Chart.js via @coreui/vue-chartjs or vue-chartjs
- **State Management:** Pinia (for fetching/caching report data)

---

### 1. Dashboard Overview (Executive Summary)
**Goal:** Provide the Senior Pastor and Admins with a snapshot of church health in 30 seconds.

| Visualization Type | Title | Data Source | Purpose |
|-------------------|-------|-------------|---------|
| **Scorecard** | Total Members | `members` table | Real-time count of active members. |
| **Scorecard** | Weekly Attendance (Last Sunday) | `attendance` table | Immediate feedback on the most recent service. |
| **Scorecard** | Monthly Giving | `contributions` table | total sum for current month. |
| **Scorecard** | New Visitors (This Month) | `visitors` table | Tracking growth momentum. |
| **Sparkline Chart** | 12-Week Attendance Trend | `attendance` table | Visual micro-chart on the dashboard card to show trajectory (up/down). |

---

### 2. Attendance Module
**Goal:** Identify growth trends, seasonal dips, and retention issues.

| Visualization Type | Title | Chart Type | Metrics / Axes |
|-------------------|-------|------------|----------------|
| **Main Chart** | Attendance Trends (YoY) | `Line Chart` | X: Weeks, Y: Count. Series: This Year vs Last Year. |
| **Breakdown** | Service Comparison | `Bar Chart` (Stacked) | X: Month. Y: Count. Stacks: Sunday, Midweek, Special Events. |
| **Demographics** | Kids vs Adults | `Doughnut Chart` | Ratio of Main Service to Children's Church attendance. |
| **Heatmap** | Service Density (Optional) | `Heatmap` | Activity by time of day/day of week over a year. |

**Critical Reports:**
- **The "Missing in Action" Report:** List of members who attended >3 times in the last 2 months but have missed the last 3 consecutive Sundays. (Retention Alert).
- **Service Capacity:** Percentage fullness based on venue seating capacity.

---

### 3. First-Time Visitors & Growth
**Goal:** Track how effective outreach is and how well the "stickiness" process works.

| Visualization Type | Title | Chart Type | Metrics / Axes |
|-------------------|-------|------------|----------------|
| **Conversion** | Visitor Retention Funnel | `Funnel Chart` (or Bar) | 1st Visit -> 2nd Visit -> Membership Class -> Active Member. |
| **Acquisition** | "How did you hear?" | `Pie Chart` | Source: Friend, Social Media, Drive-by, Event, Website. |
| **Geography** | Visitor Heatmap | `Map` (Leaflet/Google) | Pin locations of visitors (if address captured) vs Members. |

**Critical Reports:**
- **Uncontacted Visitors:** Visitors from last Sunday who have *not* yet been marked as "Followed Up".
- **Conversion Rate:** % of visitors who become members within 6 months.

---

### 4. Finance (Contributions & Expenses)
**Goal:** Financial transparency, budget health, and donor appreciation.

| Visualization Type | Title | Chart Type | Metrics / Axes |
|-------------------|-------|------------|----------------|
| **Cashflow** | Income vs Expense | `Bar/Line Combo` | Bars: Monthly Income, Line: Monthly Expenses. |
| **Sources** | Giving Breakdown | `Doughnut Chart` | Tithes vs Offerings vs Building Fund vs Missions. |
| **Spending** | Expense by Category | `Horizontal Bar` | Top categories (e.g., Salaries, Utilities, Ministry, Events). |
| **Methods** | Giving Methods | `Pie Chart` | Cash vs Online (Stripe) vs Bank Transfer. |

**Critical Reports:**
- **Budget Variance:** Actual spending vs Allocated budget per department.
- **Top 20% Donors:** Identification of key financial partners (for gratitude/relations).
- **Lapsed Givers:** Regular givers who have stopped contributing (could indicate spiritual/personal crisis).

---

### 5. Departments & Ministry Units
**Goal:** Monitor volunteer health and department activity.

| Visualization Type | Title | Chart Type | Metrics / Axes |
|-------------------|-------|------------|----------------|
| **Distribution** | Members per Ministry | `Bar Chart` | Count of members assigned to each department (Choir, Ushers, Media). |
| **Engagement** | Active vs Inactive | `Stacked Bar` | For each department, show % of roster actually serving. |

**Critical Reports:**
- **Volunteer Burnout Risk:** People serving in >2 departments or scheduled >3 weeks in a row.
- **Empty Rosters:** Departments with <50% staffing for upcoming month.

---

### 6. Membership Stats
**Goal:** Understand the flock (Demographics).

| Visualization Type | Title | Chart Type | Metrics / Axes |
|-------------------|-------|------------|----------------|
| **Age** | Age Demographics | `Bar Histo` | Age brackets (0-12, 13-18, 19-30, 31-50, 50+). |
| **Gender** | Gender Ratio | `Pie Chart` | Male / Female distribution. |
| **Growth** | Net Membership Growth | `Line Chart` | (New Members - Left Members) over time. |

---

### 7. Implementation Guidelines (Vue 3 + Chart.js)

**Component Structure:**
Create specific analytical components in `src/components/charts/`:
- `AttendanceTrendChart.vue`
- `FinanceCashflowChart.vue`
- `VisitorSourceChart.vue`

**Data Strategy:**
- Create a `useAnalyticsStore()` in Pinia.
- Backend Recommendation: Create a dedicated `DashboardController` in Laravel that returns aggregated JSON data to avoid processing thousands of records on the client side.

**Example Payload Structure (Backend):**
```json
{
  "attendance": {
    "labels": ["Jan", "Feb", "Mar", "Apr"],
    "datasets": [
      { "label": "2024", "data": [120, 135, 140, 155] },
      { "label": "2023", "data": [100, 110, 105, 120] }
    ]
  }
}
```
