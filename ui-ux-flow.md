# Church Management Platform — UI/UX Flow Design

## Navigation Architecture

### Role-Based Sidebar Structure

```
┌─────────────────────────────────────────────────────────────────┐
│  ADMIN                                                          │
├─────────────────────────────────────────────────────────────────┤
│  📊 Dashboard                                                   │
│  👥 Users & Roles          → User list, create, edit, deactivate│
│  ├─ User Management                                             │
│  └─ Roles & Permissions                                         │
│  📋 Attendance             → Approval queue, history, reports   │
│  ├─ Approval Queue         (pending submissions)                │
│  └─ Reports                                                     │
│  👋 Visitors & Follow-up   → Full access                        │
│  💰 Finance                → Dashboard overview                 │
│  ├─ Contributions                                               │
│  ├─ Expenses                                                    │
│  └─ Reports                                                     │
│  📢 Broadcasts             → WhatsApp/SMS management            │
│  🏢 Departments            → Create, assign leaders             │
│  📜 Audit Logs             → Activity history                   │
│  ⚙️ Settings                                                    │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  PASTOR                                                         │
├─────────────────────────────────────────────────────────────────┤
│  📊 Dashboard              (view-only metrics)                  │
│  📋 Attendance Reports     (approved data only)                 │
│  👥 Members                (view-only)                          │
│  🏢 Departments            (view all)                           │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  USHER                                                          │
├─────────────────────────────────────────────────────────────────┤
│  📊 Dashboard              (personal stats)                     │
│  📋 Record Attendance      → Service selector, member search    │
│  📝 My Submissions         → Status tracking, resubmit rejected │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  FINANCE OFFICER                                                │
├─────────────────────────────────────────────────────────────────┤
│  📊 Finance Dashboard                                           │
│  💳 Contributions          → Record, review, approve            │
│  📋 Expenses               → Log, categorize, submit            │
│  📈 Reports                → Monthly summaries, export          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  PR / FOLLOW-UP                                                 │
├─────────────────────────────────────────────────────────────────┤
│  📊 Dashboard              (visitor stats)                      │
│  👋 Visitors               → Register, view list                │
│  📞 Follow-ups             → Log contacts, set reminders        │
│  📅 Due This Week          → Priority list                      │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  DEPARTMENT LEADER                                              │
├─────────────────────────────────────────────────────────────────┤
│  📊 Dashboard              (department stats)                   │
│  👥 My Department          → View volunteers                    │
│  📢 Send Message           → Broadcast to department only       │
└─────────────────────────────────────────────────────────────────┘
```

---

## Screen Flows by User Story

### US-01: User Management Flow (Admin)

```
[Users List] ──────────────────────────────────────────────────────
│
├─ Header: "Users" + Search bar + "Add User" button
├─ Filters: Role dropdown | Status (Active/Inactive) | Department
├─ Table:
│   Name | Phone | Role | Department | Status | Last Login | Actions
│   ─────────────────────────────────────────────────────────────────
│   John D. | +233... | Usher | Media | Active | 2h ago | [Edit][...]
│
└─ Pagination: 25 per page

[Add/Edit User Modal] ─────────────────────────────────────────────
│
├─ Phone*: +233XXXXXXXXX (unique, immutable on edit)
├─ Name*: Text input
├─ Email: Optional
├─ Role*: Dropdown (admin, pastor, usher, finance, pr_follow_up, department_leader)
├─ Department: Dropdown (required if usher/dept_leader)
├─ 2FA Enabled: Toggle (for admin/finance)
│
├─ [Cancel] [Save]
└─ Validation: Phone format, duplicate check, role-department rules

[Deactivate Confirmation] ─────────────────────────────────────────
│
├─ "Are you sure you want to deactivate [Name]?"
├─ Reason*: Textarea (required, logged)
├─ Warning: "This user will no longer be able to log in"
└─ [Cancel] [Deactivate]
```

### US-02: Attendance Approval Flow (Admin)

```
[Approval Queue] ──────────────────────────────────────────────────
│
├─ Header: "Attendance Approvals" + Badge (12 pending)
├─ Filters: Date range | Service type | Submitted by
├─ Bulk actions: [Select All] [Approve Selected]
├─ Table:
│   ☐ | Service | Date | Count | Submitted By | Submitted At | Actions
│   ──────────────────────────────────────────────────────────────────
│   ☐ | Sunday  | Jan 19 | 145 | Usher A | 2h ago | [Approve][Reject]
│
└─ Empty state: "No pending attendance records"

[Reject Modal] ────────────────────────────────────────────────────
│
├─ "Rejecting attendance for [Service] on [Date]"
├─ Reason*: Textarea (max 255 chars)
├─ Info: "The usher will be notified and can resubmit"
└─ [Cancel] [Reject]

[Bulk Approve Confirmation] ───────────────────────────────────────
│
├─ "Approve 5 attendance records?"
├─ List: Service/Date summary
└─ [Cancel] [Approve All]
```

### US-03: Broadcast Flow (Admin)

```
[Broadcasts] ──────────────────────────────────────────────────────
│
├─ Tabs: [Compose] [History] [Scheduled]
│
├─ COMPOSE TAB:
│   ├─ Recipients*: Radio (All Members | Partners Only | Department)
│   │   └─ If Department: Dropdown to select
│   ├─ Channel*: Radio (WhatsApp | SMS)
│   ├─ Message*: Textarea
│   │   └─ Character counter (160 limit for SMS, red when exceeded)
│   ├─ Schedule: Toggle → Date/time picker
│   └─ [Preview] [Send Now / Schedule]
│
├─ HISTORY TAB:
│   ├─ Table: Date | Recipients | Channel | Message | Delivery Rate | Status
│   ├─ Click row → Delivery details (success/failed breakdown)
│   └─ [Retry Failed] button for partially sent
│
└─ SCHEDULED TAB:
    ├─ Upcoming broadcasts list
    └─ [Cancel] [Edit] actions

[Send Confirmation] ───────────────────────────────────────────────
│
├─ Preview message
├─ "Send to 234 members via WhatsApp?"
└─ [Cancel] [Send]
```

### US-08/09: Usher Attendance Recording Flow

```
[Record Attendance] ───────────────────────────────────────────────
│
├─ Step 1: Service Selection
│   ├─ Service Type*: Cards (Friday Night | Sunday | Midweek)
│   ├─ Date*: Date picker (max = today)
│   └─ [Continue]
│
├─ Step 2: Mark Members
│   ├─ Search: "Search member by name or phone..."
│   ├─ Quick filters: [All] [Recent] [Visitors]
│   ├─ Member list with checkboxes:
│   │   ☑ Kwame M. | Member | +233...
│   │   ☐ Ama K.   | Visitor | +233...
│   ├─ Selected count: "24 members marked"
│   ├─ Add visitor inline: [+ Add new visitor]
│   └─ [Back] [Continue]
│
├─ Step 3: Review & Submit
│   ├─ Summary card:
│   │   Service: Sunday
│   │   Date: Jan 19, 2026
│   │   Total: 24 (22 members, 2 visitors)
│   ├─ Notes: Optional textarea
│   └─ [Back] [Submit for Approval]
│
└─ Success state:
    ├─ ✓ "Attendance submitted!"
    ├─ "Awaiting admin approval"
    └─ [View My Submissions] [Record Another]

[My Submissions] ──────────────────────────────────────────────────
│
├─ Header: "My Attendance Submissions"
├─ Table:
│   Service | Date | Count | Status | Submitted | Actions
│   ─────────────────────────────────────────────────────────
│   Sunday | Jan 19 | 24 | ⏳ Pending | 2h ago | [View]
│   Friday | Jan 17 | 18 | ✓ Approved | 3d ago | [View]
│   Sunday | Jan 12 | 20 | ✗ Rejected | 5d ago | [Resubmit]
│
└─ Click rejected → Shows rejection reason + [Resubmit] button
```

### US-10/11: Finance Officer Flow

```
[Contributions] ────────────────────────────────────────────────────
│
├─ Header: "Partnership Contributions" + [Add Contribution]
├─ Summary cards:
│   │ Committed: GHS 45,000 │ Collected: GHS 38,500 │ Pending: GHS 6,500 │
│
├─ Filters: Month | Status | Partner name search
├─ Table:
│   Partner | Amount | Month | Status | Payment Date | Actions
│   ──────────────────────────────────────────────────────────────
│   John D. | GHS 500 | Jan | ⏳ Pending | — | [Record Payment][...]
│   Mary K. | GHS 1,000 | Jan | ✓ Paid | Jan 15 | [View]
│
├─ Overdue highlight: Partners >30 days overdue shown in red
└─ [Export CSV]

[Record Contribution Modal] ───────────────────────────────────────
│
├─ Partner*: Searchable dropdown (partnership tier members)
├─ Amount*: Currency input
├─ Month*: Month picker
├─ Purpose: Dropdown (Tithe | Offering | Special Seed | Other)
├─ Notes: Optional textarea
└─ [Cancel] [Save]

[Expenses] ─────────────────────────────────────────────────────────
│
├─ Header: "Expenses" + [Add Expense]
├─ Summary: Total this month: GHS 12,400
├─ Filters: Category | Date range | Status
├─ Table:
│   Category | Description | Amount | Date | Status | Actions
│   ──────────────────────────────────────────────────────────────
│   Utilities | Electricity | GHS 320 | Jan 18 | ⏳ Pending | [Edit]
│
└─ Pending expenses → Submitted to Admin for approval
```

### US-12/13/14: PR/Follow-up Flow

```
[Visitors] ─────────────────────────────────────────────────────────
│
├─ Header: "Visitors" + [Add Visitor]
├─ Stats cards:
│   │ Total: 86 │ Not Contacted: 12 │ Engaged: 45 │ Converted: 29 │
│
├─ Filters: Status | Source | Date range
├─ Table:
│   Name | Phone | Source | Status | First Visit | Last Contact | Actions
│   ──────────────────────────────────────────────────────────────────────
│   Ama K. | +233... | Friend | Contacted | Jan 15 | Jan 18 | [Follow-up]
│
└─ Click row → Visitor detail with follow-up history

[Add Visitor Modal] ───────────────────────────────────────────────
│
├─ Name*: Text
├─ Phone*: +233XXXXXXXXX
├─ Email: Optional
├─ Source*: Dropdown (Friend | Social Media | Walk-in | Other)
├─ Source Detail: If Other, text input
├─ Ministry Interests: Multi-select tags (Youth | Choir | Media | Prayer)
├─ Notes: Textarea
└─ [Cancel] [Save]

[Follow-up Log] ────────────────────────────────────────────────────
│
├─ Visitor: Ama K. | Status: Contacted
├─ Follow-up History:
│   │ Jan 18 | WhatsApp | "Expressed interest in choir" | PR Officer │
│   │ Jan 16 | Call | "No answer" | PR Officer │
│
├─ Log New Follow-up:
│   ├─ Method*: Radio (WhatsApp | SMS | Call | In-Person)
│   ├─ Notes*: Textarea
│   ├─ Update Status: Dropdown (Not Contacted → Contacted → Engaged → Converted)
│   ├─ Next Follow-up: Date picker
│   └─ [Save Follow-up]
│
└─ [Convert to Member] button (when status = Engaged)

[Due This Week] ────────────────────────────────────────────────────
│
├─ Header: "Follow-ups Due" + Badge (8 this week)
├─ Priority list:
│   │ 🔴 OVERDUE │ Ama K. | Due Jan 20 | Last: WhatsApp | [Call] [Log] │
│   │ 🟡 TODAY   │ John D. | Due Jan 23 | Last: Call | [Call] [Log] │
│   │ 🟢 UPCOMING│ Mary K. | Due Jan 25 | Last: SMS | [Call] [Log] │
│
└─ Quick action: [Call] opens dialer, [Log] opens follow-up modal
```

### US-15/16: Department Leader Flow

```
[My Department] ────────────────────────────────────────────────────
│
├─ Header: "Media Department" (auto-detected from user)
├─ Stats: 12 volunteers | 8 attended last service
├─ Member list:
│   Name | Phone | Tier | Last Attended | Actions
│   ──────────────────────────────────────────────────────────
│   Kwame M. | +233... | Member | Jan 19 | [Message]
│
└─ [Send Message to Department] → Opens broadcast (dept-scoped)

[Department Broadcast] ─────────────────────────────────────────────
│
├─ To: Media Department (12 members)
├─ Channel: Radio (WhatsApp | SMS)
├─ Message: Textarea
└─ [Send]
```

---

## Dashboard Variations by Role

### Admin Dashboard

```
┌─────────────────────────────────────────────────────────────────┐
│  ADMIN DASHBOARD                                                │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
│  │ Members  │ │ Visitors │ │Attendance│ │ Finance  │           │
│  │  1,248   │ │    86    │ │   78%    │ │ GHS 12.4k│           │
│  │ +24 week │ │ +12 Sun  │ │ Target 85│ │ +8% WoW  │           │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘           │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ PENDING ACTIONS                                          │  │
│  │ • 12 attendance records awaiting approval                │  │
│  │ • 3 expenses pending review                              │  │
│  │ • 5 overdue partner contributions                        │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌─────────────────────┐ ┌─────────────────────┐               │
│  │ Attendance Trend    │ │ Quick Actions       │               │
│  │ [Line Chart]        │ │ [Add User]          │               │
│  │                     │ │ [Approve Attendance]│               │
│  │                     │ │ [Send Broadcast]    │               │
│  │                     │ │ [View Audit Log]    │               │
│  └─────────────────────┘ └─────────────────────┘               │
└─────────────────────────────────────────────────────────────────┘
```

### Usher Dashboard

```
┌─────────────────────────────────────────────────────────────────┐
│  USHER DASHBOARD                                                │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                        │
│  │ Submitted│ │ Approved │ │ Rejected │                        │
│  │    12    │ │    10    │ │     2    │                        │
│  │ This mth │ │          │ │ Action!  │                        │
│  └──────────┘ └──────────┘ └──────────┘                        │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ QUICK RECORD                                             │  │
│  │ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐          │  │
│  │ │ 🌙 Friday  │ │ ☀️ Sunday  │ │ 📖 Midweek  │          │  │
│  │ │   Night    │ │   Service   │ │   Service   │          │  │
│  │ └─────────────┘ └─────────────┘ └─────────────┘          │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ RECENT SUBMISSIONS                                       │  │
│  │ Sunday Jan 19 | 24 | ⏳ Pending                          │  │
│  │ Friday Jan 17 | 18 | ✓ Approved                          │  │
│  │ Sunday Jan 12 | 20 | ✗ Rejected → [Resubmit]            │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### PR/Follow-up Dashboard

```
┌─────────────────────────────────────────────────────────────────┐
│  PR DASHBOARD                                                   │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
│  │ Visitors │ │ Due Today│ │ Overdue  │ │Converted │           │
│  │    86    │ │     3    │ │     5    │ │    29    │           │
│  │ +12 week │ │ Priority │ │ Action!  │ │ This mth │           │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘           │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ 🔴 OVERDUE FOLLOW-UPS                                    │  │
│  │ ┌─────────────────────────────────────────────────────┐  │  │
│  │ │ Ama K. | Due Jan 20 | WhatsApp | [Call Now] [Log]  │  │  │
│  │ │ John D.| Due Jan 18 | Call     | [Call Now] [Log]  │  │  │
│  │ └─────────────────────────────────────────────────────┘  │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                 │
│  ┌─────────────────────┐ ┌─────────────────────┐               │
│  │ Conversion Funnel   │ │ Quick Actions       │               │
│  │ Not Contacted: 12   │ │ [Add Visitor]       │               │
│  │ Contacted: 45       │ │ [Log Follow-up]     │               │
│  │ Engaged: 29         │ │ [View Due List]     │               │
│  │ Converted: 29       │ │                     │               │
│  └─────────────────────┘ └─────────────────────┘               │
└─────────────────────────────────────────────────────────────────┘
```

---

## Mobile-First Considerations

### Bottom Tab Bar (Mobile)

```
┌─────────────────────────────────────────────────────────────────┐
│  Role: Admin                                                    │
│  [Home] [Approve] [Broadcast] [Finance] [More]                 │
├─────────────────────────────────────────────────────────────────┤
│  Role: Usher                                                    │
│  [Home] [Record] [Submissions] [Profile]                       │
├─────────────────────────────────────────────────────────────────┤
│  Role: PR/Follow-up                                             │
│  [Home] [Visitors] [Follow-ups] [Due] [Profile]               │
├─────────────────────────────────────────────────────────────────┤
│  Role: Finance                                                  │
│  [Home] [Contributions] [Expenses] [Reports] [Profile]         │
├─────────────────────────────────────────────────────────────────┤
│  Role: Department Leader                                        │
│  [Home] [My Dept] [Message] [Profile]                          │
└─────────────────────────────────────────────────────────────────┘
```

### Swipe Gestures

- Swipe left on list item → Quick actions (Edit, Delete)
- Swipe right on attendance → Quick approve
- Pull to refresh on all lists

### Touch Targets

- Minimum 44×44px for all interactive elements
- Large checkboxes for attendance marking
- Full-width buttons on mobile forms

---

## Component Library Requirements

### New Components Needed

1. **ApprovalBadge** — Status indicator (Pending/Approved/Rejected)
2. **MemberSearchInput** — Autocomplete with avatar + tier badge
3. **CharacterCounter** — For SMS composition
4. **DateRangePicker** — Filter component
5. **QuickActionCard** — Dashboard action buttons
6. **FollowUpTimeline** — Vertical timeline for visitor history
7. **DeliveryStatusBadge** — Broadcast delivery indicator
8. **OverdueAlert** — Highlight for overdue items
9. **EmptyState** — Illustrated empty states per context
10. **ConfirmationModal** — Reusable confirm dialogs
11. **StepWizard** — Multi-step forms (attendance recording)
12. **RoleScopedSidebar** — Dynamic nav based on user.role

---

## Missing Views (Gap Analysis)

| User Story | Required View              | Current Status                       |
| ---------- | -------------------------- | ------------------------------------ |
| US-01      | Users Management           | ❌ Missing                           |
| US-02      | Attendance Approval Queue  | ❌ Missing                           |
| US-03      | Broadcasts                 | ❌ Missing                           |
| US-04      | Finance Dashboard          | ⚠️ Partial (needs expansion)         |
| US-05      | Department Management      | ⚠️ Partial (needs leader assignment) |
| US-08      | Usher Attendance Recording | ⚠️ Partial (needs member search)     |
| US-09      | My Submissions             | ❌ Missing                           |
| US-10      | Contributions Management   | ❌ Missing                           |
| US-12      | Visitor Management         | ⚠️ Partial (needs source, follow-up) |
| US-13      | Follow-up Logging          | ❌ Missing                           |
| US-14      | Due This Week              | ❌ Missing                           |
| US-15      | My Department              | ❌ Missing                           |
| US-17      | Sign In (phone-based)      | ⚠️ Uses email, needs phone           |
| US-18      | Audit Logs                 | ❌ Missing                           |
| US-19      | Member Tier Management     | ❌ Missing                           |

---

## Recommended Implementation Priority

### Phase 1: Core Flows (Week 1-2)

1. Update SignIn to phone-based auth
2. Users Management page
3. Attendance Approval Queue
4. Enhanced Attendance Recording (member search)
5. My Submissions (Usher view)

### Phase 2: Finance & Visitors (Week 3-4)

1. Contributions Management
2. Finance Dashboard expansion
3. Follow-up Logging
4. Due This Week view
5. Visitor source tracking

### Phase 3: Communication & Admin (Week 5-6)

1. Broadcasts page
2. Audit Logs viewer
3. Member Tier Management
4. My Department (Dept Leader)
5. Role-scoped dashboards

### Phase 4: Polish & Mobile (Week 7-8)

1. Role-based mobile tab bars
2. Offline attendance (Service Worker)
3. Empty states & loading skeletons
4. Accessibility audit
5. Performance optimization
