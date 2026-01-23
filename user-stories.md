# Church Management Platform – Production-Grade User Stories

**Status:** Implementation-Ready | **Version:** 1.0 | **Platform:** Vue 3 SPA + Laravel 11 API  
**Auth:** Sanctum (phone + password) | **Core Principle:** Data accuracy, role separation, auditability

---

## ADMIN USER STORIES

### US-01: User Lifecycle Management

**As an** Admin  
**I want to** create, update, deactivate, and assign roles to users  
**So that** access control is enforced and all changes are auditable

**Acceptance Criteria**

- **Create user:**
  - Required: phone (unique, format +233XXXXXXXXX), name, role, department
  - Phone duplicate check returns: "Phone already registered"
  - New user email (optional) doesn't trigger duplicate
  - Role selection: admin | pastor | usher | finance | pr_follow_up | department_leader
  - Department required for: usher, department_leader, optional for others
  - User status defaults to `active`

- **Update user:**
  - Can edit: name, department, status
  - Can change role (but cannot self-demote from admin)
  - Cannot edit phone (immutable for audit trail)
  - Changes trigger audit log with before/after values

- **Deactivate user:**
  - Soft delete: set `deactivated_at` timestamp
  - Deactivated users cannot log in
  - Deactivated user's past actions remain in audit log
  - Reactivation available (optional, set `deactivated_at = NULL`)
  - Must provide deactivation reason (logged)

- **Prevent self-demotion:**
  - System validation: cannot remove own admin role
  - Error message: "Cannot remove your own admin privileges"

- **Audit trail:**
  - Every create/update logged with: admin_id, action, user_id, changes (JSON), timestamp, ip_address
  - Changes stored as: `{before: {role: 'usher'}, after: {role: 'finance'}}`

**Definition of Done**
- ✅ Backend validation tests (duplicate phone, role rules)
- ✅ Audit log entry verified for each operation
- ✅ UI confirmation modal before deactivation
- ✅ API response time <200ms for user list (100+ users)

**Boundaries (Out of Scope)**
- ❌ Password reset (user sets on first login)
- ❌ Permission granularity beyond roles (no custom permissions)
- ❌ Bulk user import (single add only in MVP)

---

### US-02: Attendance Verification Workflow

**As an** Admin  
**I want to** review, approve, or reject attendance records submitted by Ushers  
**So that** only verified data affects dashboards and reports

**Acceptance Criteria**

- **View pending attendance:**
  - List sorted by: service_date DESC, then service_type (Friday first)
  - Columns: service_type, attendance_date, member_count, submitted_by, submitted_at, status
  - Filter by: date range, service_type, submission status
  - Pagination: 25 records per page
  - Show total pending count in header

- **Approval workflow:**
  - Single approve: saves status = `approved`, sets approved_by = admin_id, approved_at = NOW()
  - Reject: requires reason (max 255 chars), status = `rejected`, includes reason in audit log
  - Bulk approve: select multiple by date/service, confirm, approve all at once
  - Approved records locked: cannot edit after approval

- **After approval:**
  - Approved attendance counts propagate to: attendance reports, member analytics, tier progression (52-week member logic)
  - Rejected records returned to Usher as `needs_revision` status
  - Usher notified via SMS: "Your attendance for [date] was rejected: [reason]. Please resubmit."

- **Data integrity:**
  - Only approved records visible in: dashboards, reports, finance calculations
  - Pending/rejected records visible to Admin and submitting Usher only
  - Cannot approve future dates (validation: attendance_date ≤ TODAY)

**Definition of Done**
- ✅ Bulk approval performance test: 500+ records approved <2s
- ✅ Audit log captures: approver_id, approval_status, timestamp, changes
- ✅ SMS notification integration tested (Twilio sandbox)
- ✅ Data isolation: rejected records not counted in any report

**Boundaries (Out of Scope)**
- ❌ Editing approved records (requires deletion + re-submission)
- ❌ Attendance amendments (create new record if correction needed)
- ❌ Attendance scheduling (only reactive recording)

---

### US-03: Broadcast Management (WhatsApp/SMS)

**As an** Admin  
**I want to** send targeted WhatsApp/SMS messages to members, partners, or departments  
**So that** communication is centralized, trackable, and compliant

**Acceptance Criteria**

- **Recipient selection:**
  - All Members: excludes deactivated users
  - Partners Only: members with tier = `partnership`
  - Specific Department: pre-populated with department member list
  - Cannot send to empty groups (validation error)

- **Message composition:**
  - WhatsApp: unlimited length, rich text preview
  - SMS: 160 character limit with live counter (red when exceeds)
  - Message validation: min 1 char, max 1000 chars
  - Optional: scheduling for future date/time (minimum 1 minute ahead)

- **Delivery workflow:**
  - Send immediately → queues to Twilio with status = `pending`
  - Schedule → stores scheduled_for timestamp, cron sends at time
  - Twilio integration: sends WhatsApp first, fallback to SMS on delivery failure
  - Status tracking: pending → sent → failed (with error_reason)
  - Retry logic: max 3 attempts with exponential backoff (1m, 5m, 10m)

- **Broadcast history & recovery:**
  - View sent broadcasts: date, recipient_group, message, delivery_rate (X of Y sent)
  - Cannot edit/delete sent broadcasts (immutable for compliance)
  - Can retry failed broadcast: select, confirm, resend to failed recipients only
  - Export broadcast log (CSV): date, group, message, count, delivery_rate

- **Audit & compliance:**
  - Every broadcast logged: sender_id, recipient_group, channel, message, status, sent_at
  - SMS log includes phone hash (not full number, privacy)
  - Delivery failures logged with Twilio error code

**Definition of Done**
- ✅ Twilio sandbox integration tested (send/receive)
- ✅ Fallback logic verified: WhatsApp fail → SMS sent
- ✅ Broadcast history exports successfully
- ✅ Rate limiting: max 100 broadcasts/hour per admin

**Boundaries (Out of Scope)**
- ❌ Rich media (images, files) in MVP
- ❌ Message templates (free-form only)
- ❌ Two-way SMS replies (broadcast-only)
- ❌ Bulk phone number upload (single broadcast only)

---

### US-04: Finance Overview Dashboard

**As an** Admin  
**I want to** view aggregated partnership contributions, expenses, and net position  
**So that** leadership has financial visibility without accessing transaction details

**Acceptance Criteria**

- **Dashboard metrics (month-to-date):**
  - Total partner commitments: SUM(monthly_commitment) where tier = `partnership`
  - Collected this month: SUM(contributions) where status = `approved` AND contribution_month = CURRENT_MONTH
  - Pending: Total commitments - Collected
  - Total expenses: SUM(amount) where status = `approved` AND expense_date in CURRENT_MONTH
  - Net position: Collected - Expenses

- **Partner payment status:**
  - On-time: last contribution received within 5 days of expected due date
  - Overdue: last contribution >5 days late
  - Never paid: no contributions on record
  - Inactive: partner tier expired or deactivated
  - Click partner name → payment history table (date, amount, status, notes)

- **Monthly trend chart:**
  - Line chart: commitments vs. collections (last 12 months)
  - X-axis: month, Y-axis: GHS amount
  - Tooltip: exact values on hover

- **Expense breakdown (pie chart):**
  - Categories: Utilities, Maintenance, Supplies, Staff, Other
  - Shows: category, amount, percentage of total

- **Export functionality:**
  - PDF: includes dashboard snapshot, partner list (commitment/collected/balance), expense breakdown, generated date, exported_by
  - CSV: detailed lines (partner, contribution, date) for external analysis

- **Data source:**
  - All data sourced from approved records only (verified by Admin per US-02)
  - Updates in real-time as Finance Officer approves contributions/expenses

**Definition of Done**
- ✅ Dashboard loads in <1s (100+ partners)
- ✅ Charts render responsive on mobile
- ✅ PDF export includes all required fields, tested in Adobe Reader
- ✅ No sensitive data leakage (e.g., individual expense purposes not shown)

**Boundaries (Out of Scope)**
- ❌ Direct editing of contributions/expenses (Finance Officer entry point only)
- ❌ Tax reporting (basic aggregation only)
- ❌ Budgeting/forecasting (actuals only)
- ❌ Refunds or credit notes

---

### US-05: Department Structure & Leadership

**As an** Admin  
**I want to** create departments, assign leaders, and manage hierarchy  
**So that** volunteer roles are organized and leaders have clear scope

**Acceptance Criteria**

- **Department CRUD:**
  - Create: name (unique, max 50 chars), description (optional, max 500 chars), assign leader from user list
  - Edit: name, description, reassign leader
  - Delete: only if department has 0 members (validation error if members exist)
  - Soft delete available with restore option (7-day window)

- **Leadership assignment:**
  - Leader must be active user with role = `department_leader`
  - One leader per department
  - Leader cannot manage other departments (backend enforces: `user.department_id == requested_department_id`)
  - Reassign leader: old leader reverted to department_leader without department assignment

- **Member assignment:**
  - Assign members to department: via user edit (Story US-01) or bulk import
  - View all members in department: list with name, phone, tier, last_service_attended
  - Department leader visibility: can only view own department members (API filters by user.department_id)

- **Audit trail:**
  - Department creation/edit logged with admin_id, changes, timestamp
  - Leader assignment logged separately

**Definition of Done**
- ✅ Cannot delete department with members (validation tested)
- ✅ Leader field scoped correctly in API (backend enforces department_id)
- ✅ Member list loads in <500ms
- ✅ Audit log captures department changes

**Boundaries (Out of Scope)**
- ❌ Sub-departments (flat hierarchy only)
- ❌ Multiple leaders per department
- ❌ Department budgets or resource allocation

---

## PASTOR USER STORIES

### US-06: Attendance Analytics (View-Only)

**As a** Pastor  
**I want to** view verified attendance trends and member engagement  
**So that** I understand participation patterns and can plan outreach

**Acceptance Criteria**

- **Attendance overview:**
  - View by service type: Friday Night, Sunday (separate counts)
  - Select date range: week, month, custom (date picker)
  - Show: total per service, trend (↑ ↓), average attendance

- **Breakdown:**
  - Members vs. Visitors count and percentage
  - Attendance rate: (members attended / members registered) × 100

- **Charts:**
  - Line chart: attendance over time (last 12 services)
  - Bar chart: members vs. visitors per service
  - Interactive: click bar to drill down to member list (optional)

- **Data scope:**
  - View only approved attendance (via US-02)
  - No access to pending/rejected records
  - No financial data visible (role-based filtering)

- **Permissions:**
  - View-only, no edits
  - Cannot access Finance dashboards or member tiers
  - Cannot approve attendance (unless delegated per US-07)

**Definition of Done**
- ✅ Charts render in <500ms
- ✅ Mobile-responsive layout
- ✅ No sensitive data exposed (e.g., no private notes)
- ✅ Role-based API filtering verified

**Boundaries (Out of Scope)**
- ❌ Predictive analytics
- ❌ Individual member drill-down (names) without delegation
- ❌ Export functionality (Admin-only)

---

### US-07: Delegated Attendance Approval

**As a** Pastor  
**I want to** approve attendance records when Admin delegates this permission  
**So that** approval workload is shared during busy periods

**Acceptance Criteria**

- **Enabling delegation:**
  - Admin toggles: `can_approve_attendance = true` for specific Pastor
  - Toggle stored in user profile, audit logged
  - Admin can revoke anytime

- **Approval workflow (identical to US-02):**
  - View pending attendance, filter by date/service
  - Single or bulk approve
  - Reject with reason
  - Same status transitions (pending → approved/rejected)

- **Audit distinction:**
  - Approval logged with approver role = `pastor` (not admin)
  - Audit trail shows: "Approved by Pastor [name]"
  - Finance Officer and Admin can distinguish pastor vs. admin approvals

- **Scope:**
  - Pastor can only approve, not manage users or finance

**Definition of Done**
- ✅ Admin toggle for delegation works
- ✅ Audit log shows pastor as approver
- ✅ Pastor cannot self-enable this permission (backend only)
- ✅ Revoking delegation prevents further approvals immediately

**Boundaries (Out of Scope)**
- ❌ Conditional delegation (e.g., Friday services only)
- ❌ Pastor access to other Admin functions

---

## USHER USER STORIES

### US-08: Service Attendance Recording

**As an** Usher  
**I want to** record member attendance for Friday Night and Sunday services  
**So that** participation is captured accurately and in real-time

**Acceptance Criteria**

- **Record creation:**
  - Service type: Friday Night or Sunday (dropdown)
  - Attendance date: defaults to TODAY, can select any past date up to 30 days ago (validation)
  - Cannot record future dates (error: "Cannot record future attendance")
  - Member selection: search by name or phone (autocomplete, minimum 2 chars)
  - Autocomplete returns: member_id, name, phone, tier (display as `[Name] - [Tier]`)
  - Notes (optional): max 255 chars (e.g., "Arrived late", "Brought guest")

- **Submission:**
  - Click "Submit for Approval" → status = `pending`, submitted_at = NOW(), submitted_by = usher_id
  - Confirmation message: "Attendance recorded for [Member] on [Date]. Awaiting approval."
  - Record now visible in "My Submissions" dashboard

- **Data integrity:**
  - Unique constraint: same member cannot be marked twice for same service/date
  - Validation error: "[Member] already recorded for [Service] on [Date]"
  - Cannot edit submitted record (immutable until approved/rejected)

- **Offline capability (Story US-20 optional):**
  - If offline, record queues locally (IndexedDB)
  - Sync label: "Syncing..." when online
  - Offline sync doesn't block submission confirmation

- **Permissions:**
  - Only Usher role can record
  - Cannot view other Usher's submissions (scoped by usher_id)

**Definition of Done**
- ✅ Autocomplete performance <200ms for 100+ members
- ✅ Duplicate prevention tested (same member, service, date)
- ✅ Offline queue tested (add record offline, verify sync online)
- ✅ Cannot record future dates (validation works)
- ✅ UI confirms before submission

**Boundaries (Out of Scope)**
- ❌ Bulk attendance import (single record only in MVP)
- ❌ Absence marking (only presence recording)
- ❌ Editing approved records

---

### US-09: Submission Tracking & Resubmit

**As an** Usher  
**I want to** view my attendance submissions and correct rejected ones  
**So that** I understand approval status and can fix errors

**Acceptance Criteria**

- **Submission list:**
  - Display: all submissions for this Usher, sorted by submitted_at DESC
  - Columns: member_name, service_type, attendance_date, status (badge color: pending=gray, approved=green, rejected=red), submitted_at
  - Pagination: 20 per page
  - Filters: status, date range

- **Status details:**
  - Pending: shows "Awaiting Admin review" message
  - Approved: locked (read-only), shows approved_at, approved_by (Admin name)
  - Rejected: shows rejection reason, includes "Resubmit" button (highlighted red)

- **Resubmission:**
  - Click "Resubmit" → opens submission form pre-filled with original data
  - Usher can edit: attendance_date, notes (cannot change member or service_type)
  - Resubmit creates NEW record with status = `pending` (original stays as `rejected` for audit)
  - Confirmation: "Resubmitted. A new approval request has been sent."

- **No self-approval:**
  - Usher cannot approve own submissions
  - Cannot view Admin's approval notes beyond rejection reason

- **Audit trail:**
  - Resubmissions create new record with link to original (e.g., `resubmitted_from = original_record_id`)

**Definition of Done**
- ✅ Resubmit button visible only for rejected records
- ✅ Original rejected record preserved in audit log
- ✅ New resubmitted record linked to original
- ✅ Usher cannot edit locked approved records

**Boundaries (Out of Scope)**
- ❌ Bulk resubmit
- ❌ Edit approved records
- ❌ Delete submissions

---

## FINANCE OFFICER USER STORIES

### US-10: Partnership Contribution Tracking

**As a** Finance Officer  
**I want to** record, track, and review partnership contributions  
**So that** partner commitments and payments are accurate and transparent

**Acceptance Criteria**

- **Contribution entry:**
  - Add contribution: partner (member dropdown, tier = `partnership`), amount (GHS, decimal), contribution_month (date picker), purpose (optional, max 255 chars)
  - Frequency: weekly, monthly, as_able (used for reminder scheduling)
  - Expected_date: auto-calculated based on frequency (e.g., monthly = 1st of month)
  - Status defaults to: `pending_review`

- **Finance Officer review:**
  - View contributions by status: pending_review, reviewed, approved, rejected
  - Can mark as `reviewed` (Finance Officer confirms data is correct)
  - Cannot approve (Admin only, per US-02 equivalency)
  - Add notes if issues detected

- **Partner payment history:**
  - Click partner name → table: contribution_month, amount, frequency, status, payment_date (if received), paid_amount, notes
  - Filters: date range, status
  - Export: partner payment summary (CSV/PDF)

- **Overdue tracking:**
  - Flag overdue: if contribution not received by (expected_date + 7 days), status = `overdue`
  - Overdue list: show partner, amount, days_overdue
  - Manual action: record payment when received (updates payment_date, paid_amount)

- **Dashboard aggregation:**
  - Total commitments: SUM(amount) where tier = `partnership`
  - Collected this month: SUM(paid_amount) where status = `approved` AND payment_date in CURRENT_MONTH
  - Pending: commitments not yet received

- **Permissions:**
  - Cannot approve own contributions (conflict of interest)
  - Cannot edit Admin-approved contributions
  - Finance Officer can only add/review, Admin must approve via US-02

**Definition of Done**
- ✅ Cannot mark own contribution as reviewed (if Finance Officer is also a partner)
- ✅ Overdue calculation tested (expected_date + 7 days logic)
- ✅ Payment history loads in <500ms
- ✅ Audit log captures: who added, who reviewed, when approved
- ✅ Dashboard totals match verified contributions

**Boundaries (Out of Scope)**
- ❌ Refunds or credits (only contributions)
- ❌ Recurring automation (manual entry only)
- ❌ Payment method tracking (amount only)

---

### US-11: Expense Management & Tracking

**As a** Finance Officer  
**I want to** record and submit expenses for approval  
**So that** spending is documented and transparent

**Acceptance Criteria**

- **Expense entry:**
  - Required: amount (GHS, >0), category (dropdown: Utilities, Maintenance, Supplies, Staff, Other)
  - Optional: description (max 500 chars), receipt (file upload: jpg/png/pdf, max 5MB), expense_date (defaults to TODAY)
  - Status defaults to: `pending_approval`

- **Submission workflow:**
  - Click "Submit for Approval" → sends to Admin approval queue
  - Confirmation: "Expense submitted to Admin for review."
  - Notification (SMS): "Expense of GHS [amount] submitted for approval"

- **Expense tracking:**
  - View expenses by status: pending_approval, approved, rejected
  - Show: date, category, amount, status, submitted_at, approved_at, approved_by (Admin name)
  - Rejected expenses show rejection reason (max 255 chars)

- **Monthly reporting:**
  - Export: CSV with category, amount, date, description, status
  - Report totals: approved expenses only
  - Useful for budgeting/planning

- **Permissions:**
  - Finance Officer can only submit for approval
  - Cannot approve own expense (backend validation)
  - Cannot edit submitted expense (immutable for audit)
  - Cannot view other Finance Officer's expenses (if multi-officer setup)

- **Audit trail:**
  - Logged: who submitted, who approved, timestamp, amount, category

**Definition of Done**
- ✅ Cannot self-approve (validation tested)
- ✅ File upload works for jpg/png/pdf
- ✅ Rejected reasons visible to Finance Officer
- ✅ Monthly export totals only approved expenses
- ✅ Audit log complete

**Boundaries (Out of Scope)**
- ❌ Receipt OCR (upload only, no processing)
- ❌ Recurring expenses (manual entry only)
- ❌ Expense forecasting

---

## PR / FOLLOW-UP OFFICER USER STORIES

### US-12: Visitor Registration & Tracking

**As a** PR/Follow-up Officer  
**I want to** register first-time visitors and capture their interests  
**So that** follow-up outreach is structured and targeted

**Acceptance Criteria**

- **Visitor entry:**
  - Required: name (max 100 chars), phone OR email (at least one)
  - Phone format: +233XXXXXXXXX (Ghana standard, optional validation)
  - Source: dropdown: Friend, Social Media, Walk-in, Other (if Other, optional text field for detail)
  - Ministry interest: checkboxes: Youth, Counseling, Giving, Fellowship, Other
  - Initial notes (optional, max 500 chars): prayer requests, referrer name, etc.
  - First_visit_date: defaults to TODAY
  - Status defaults to: `not_contacted`

- **Duplicate prevention:**
  - Check duplicate phone (if provided): error "Phone already registered as visitor on [date]"
  - Check duplicate email (if provided): error "Email already registered as visitor on [date]"
  - Allow re-registration with different channel (e.g., same person, different phone)

- **Visitor list:**
  - View all: name, phone, source, first_visit_date, status
  - Search by: name or phone
  - Filter by: source, status, date range
  - Pagination: 25 per page

- **Data integrity:**
  - Visitor cannot be assigned to member tier until PR/Follow-up marks status = `converted`
  - When status → `converted`, PR/Follow-up can create member account (Story US-19 linkage)

- **Permissions:**
  - PR/Follow-up Officer can create, edit, delete own visitors
  - Cannot edit other PR/Follow-up Officer's visitors (if multi-staff setup)

**Definition of Done**
- ✅ Duplicate phone/email check works
- ✅ Phone format validated (optional but recommended)
- ✅ Ministry interest checkboxes function
- ✅ Visitor list filters by source/status
- ✅ Audit log: who created, when

**Boundaries (Out of Scope)**
- ❌ Automatic member account creation (manual conversion only)
- ❌ SMS opt-in/GDPR consent (basic capture, no compliance)
- ❌ Visitor-to-member scoring/ranking

---

### US-13: Follow-up Activity Logging

**As a** PR/Follow-up Officer  
**I want to** log every contact attempt with visitors  
**So that** engagement history is preserved and next steps are clear

**Acceptance Criteria**

- **Follow-up log entry:**
  - Select visitor (dropdown or search)
  - Contact method: WhatsApp, SMS, Call, In-person (radio buttons)
  - Outcome notes (max 500 chars): "Interested in youth group", "No answer", "Will attend Sunday", etc.
  - Next follow-up date (required): date picker, minimum TODAY
  - Update visitor status: not_contacted → contacted → engaged → converted (dropdown, only allows progression forward)

- **Status progression:**
  - `not_contacted`: initial state
  - `contacted`: at least one contact attempt made
  - `engaged`: showing interest, attending services, or responding to messages
  - `converted`: ready to join membership or confirmed decision to join
  - Status can only move forward (no regression), except for corrections (see Definition of Done)

- **Follow-up history:**
  - View timeline: all follow-ups for a visitor, newest first
  - Display: date, contact_method, outcome_notes, status_after, logged_by (PR Officer name)
  - Immutable: cannot edit/delete logged follow-ups (audit only)

- **Notifications:**
  - Next follow-up date triggers reminder (SMS or email): "Follow-up due: [Visitor name]"
  - Sent 1 day before due date

- **Audit trail:**
  - Every log entry saved with: visitor_id, pr_officer_id, contact_method, notes, status_change, timestamp

**Definition of Done**
- ✅ Status progression enforced (cannot skip states, e.g., not_contacted directly to converted)
- ✅ Follow-up history loads in <300ms
- ✅ Reminder SMS/email sent 1 day before due date (tested)
- ✅ Cannot delete log entries (immutable)
- ✅ Timestamp logged for every entry
- ✅ UI prevents regression (buttons only show valid next states)

**Boundaries (Out of Scope)**
- ❌ Status regression (no "un-engage")
- ❌ Bulk follow-ups (single log entry only)
- ❌ Automated follow-up scheduling (manual scheduling only)

---

### US-14: Follow-up Due List & Dashboard

**As a** PR/Follow-up Officer  
**I want to** see visitors due for follow-up this week  
**So that** outreach stays on schedule and no visitor is forgotten

**Acceptance Criteria**

- **Due list dashboard:**
  - Show: visitors with next_follow_up_date = TODAY or within 7 days
  - Sort by: due date (earliest first) OR priority (optional: manually set priority)
  - Columns: visitor_name, phone, source, last_contact_method, last_contact_date, status, days_due

- **Filters:**
  - By status: not_contacted, contacted, engaged (exclude converted as they're now members)
  - By source: friend, social_media, walk_in, other
  - By date range: due today, due this week, overdue (past due date)

- **Quick actions:**
  - Click visitor → expand inline to show: last notes, ministry interests, entire follow-up history
  - "Log Follow-up" button → pre-fills visitor_id, opens log form (US-13)
  - "Mark Converted" button → status → `converted`, triggers member account creation prompt

- **Overdue handling:**
  - Highlight overdue visitors (red badge) if next_follow_up_date < TODAY
  - Show "X days overdue"
  - Cannot dismiss overdue (must log follow-up or manually update next_follow_up_date)

- **Export:**
  - Export due list (CSV): name, phone, source, last contact, due date, status
  - Useful for offline reference or team sync

- **Notifications:**
  - Optional: daily digest email with due list (Admin configurable)

**Definition of Done**
- ✅ Due list loads in <500ms (50+ visitors)
- ✅ Overdue highlighted visually
- ✅ Quick action buttons functional
- ✅ Export CSV tested
- ✅ Mobile responsive (touch-friendly buttons)

**Boundaries (Out of Scope)**
- ❌ Automated SMS reminders (manual logging only)
- ❌ AI priority scoring
- ❌ Visitor assignment to teams

---

## DEPARTMENT LEADER USER STORIES

### US-15: Department Member Visibility

**As a** Department Leader  
**I want to** see all volunteers in my department  
**So that** I know who I manage and can coordinate effectively

**Acceptance Criteria**

- **Department roster:**
  - View: all users assigned to my department (department_id = current_user.department_id)
  - Columns: name, phone, tier (Visitor | Member | Partnership), last_service_attended, status (active | deactivated)
  - Search by: name or phone (live search)
  - Pagination: 25 per page

- **Member detail:**
  - Click member → see: name, phone, tier, tier_start_date, last_service_attended, membership_years
  - No edit capability (Admin-only)
  - No tier or role editing visible

- **Filters:**
  - By tier: visitors, members, partners (show counts)
  - By status: active, deactivated (show counts)
  - By last attendance: this week, this month, 3+ months

- **Export:**
  - CSV roster: name, phone, tier, last_attendance
  - PDF roster: formatted for printing/distribution to department members

- **Permissions:**
  - View own department only (API enforces: user.department_id == requested_department_id)
  - Cannot view other departments
  - Cannot edit member details, roles, or tiers
  - Cannot add members to department (Admin-only)

**Definition of Done**
- ✅ Cannot access other department rosters (API tested)
- ✅ Member count accurate
- ✅ Search performance <200ms (100+ members)
- ✅ Export CSV/PDF tested
- ✅ No edit buttons visible (UI reflects permissions)

**Boundaries (Out of Scope)**
- ❌ Member editing
- ❌ Role assignment
- ❌ Tier changes
- ❌ Attendance editing

---

### US-16: Department Message Broadcast

**As a** Department Leader  
**I want to** send WhatsApp/SMS to my department volunteers  
**So that** I can coordinate duties, announce changes, and schedule activities

**Acceptance Criteria**

- **Broadcast composition:**
  - Recipient group: auto-filtered to "My Department" (no other options in UI)
  - No cross-department access (API enforces: department_id == current_user.department_id)
  - Channel: WhatsApp or SMS (radio buttons)
  - Message: compose text with character counter
    - WhatsApp: unlimited length
    - SMS: 160 char limit with warning
  - Schedule option: send now or schedule for future date/time

- **Delivery workflow:**
  - Click "Send" → message queued to Twilio
  - Status: pending → sent → failed
  - WhatsApp preferred; fallback to SMS if WhatsApp fails
  - Retry on failure: max 3 attempts with exponential backoff
  - Delivery status visible: "X of Y delivered"

- **Broadcast history:**
  - View sent messages: date, member_count, delivery_status, message preview
  - Can retry failed broadcast (resend to failed numbers only)
  - History immutable (cannot edit/delete sent messages)

- **Permissions:**
  - Department Leaders cannot send to other departments (backend enforces)
  - Cannot see broadcasts from other leaders
  - Cannot broadcast to "All Members" or "Partners Only" (department-only feature)

**Definition of Done**
- ✅ API enforces department_id == current_user.department_id
- ✅ Cannot select other departments (UI doesn't offer option)
- ✅ Fallback to SMS tested (WhatsApp fail scenario)
- ✅ Retry logic works (3 attempts max)
- ✅ Broadcast history shows delivery rate
- ✅ Audit log: leader_id, department_id, message, status, timestamp

**Boundaries (Out of Scope)**
- ❌ Cross-department broadcasts (department-scoped only)
- ❌ Message templates
- ❌ Rich media (images/files)
- ❌ Two-way SMS replies

---

## SYSTEM-WIDE USER STORIES

### US-17: Authentication & Session Management

**As any** User  
**I want to** log in securely with my phone and password  
**So that** my access is role-based and my data is protected

**Acceptance Criteria**

- **Login workflow:**
  - Phone input: format +233XXXXXXXXX (Ghana standard)
  - Password input: min 8 chars (requirement shown on first login/password reset)
  - Submit → validate phone format and check if registered
  - Error messages specific:
    - "Phone not registered" (if no user found)
    - "Incorrect password" (if password wrong)
    - Rate limiting: max 5 attempts per phone per 15 minutes (error: "Too many attempts. Try again in 15 minutes")

- **Session management:**
  - Successful login → JWT token (Sanctum) + session cookie
  - Mobile app: session persists 30 days (unless logout)
  - Web browser: session persists until logout OR 24 hours (whichever first)
  - Token refresh: automatic on API calls, expires after 30 days
  - Logout: clears token and session, user redirected to login

- **Two-factor authentication (2FA, optional for Admin & Finance):**
  - After password verified, if user.has_2fa = true:
    - SMS OTP sent: "Your verification code is: XXXXXX"
    - OTP input field: 6 digits
    - OTP valid for 10 minutes only
    - Max 3 OTP attempts per login (error: "Invalid code. Try again or request a new code.")
    - "Resend code" button: max 2 resends per login
  - 2FA enabled/disabled by Admin (User Management, US-01)

- **Password security:**
  - Minimum 8 characters, must include:
    - At least 1 uppercase letter
    - At least 1 number
    - At least 1 special character (!@#$%^&*)
  - Password hash: bcrypt (Laravel default) or Argon2
  - Password reset link: sent via SMS, valid for 1 hour

- **Session data:**
  - Upon login, session includes: user_id, role, department_id, name, phone
  - Frontend stores: JWT token (localStorage or sessionStorage), user role
  - Frontend cannot enforce permissions (backend only)

**Definition of Done**
- ✅ Phone format validation works
- ✅ Rate limiting enforced (5 attempts/15 min)
- ✅ 2FA OTP SMS sent within 10 seconds
- ✅ OTP expires after 10 minutes
- ✅ Password hashing tested (bcrypt/Argon2)
- ✅ Logout clears token and session
- ✅ Mobile session persists 30 days
- ✅ Web session expires at 24 hours or logout

**Boundaries (Out of Scope)**
- ❌ Social login (phone-only)
- ❌ Biometric auth in MVP (basic auth only)
- ❌ Single sign-on (SSO)
- ❌ Password history/expiry policies

---

### US-18: Audit Trail & Compliance Logging

**As an** Admin  
**I want to** view immutable audit logs of all system actions  
**So that** the platform remains accountable and compliant

**Acceptance Criteria**

- **Audit log entry structure:**
  - Captured for every action: user_id, action (create/update/approve/reject/broadcast/delete), entity_type (user/attendance/contribution/expense/visitor), entity_id, changes (JSON: {before: {}, after: {}}), timestamp, ip_address, user_agent (optional)

- **Actions logged:**
  - Users: create_user, update_user, deactivate_user, assign_role
  - Attendance: submit_attendance, approve_attendance, reject_attendance, resubmit_attendance
  - Contributions: add_contribution, approve_contribution, reject_contribution, mark_overdue
  - Expenses: submit_expense, approve_expense, reject_expense
  - Broadcasts: send_broadcast, retry_broadcast
  - Visitors: add_visitor, log_followup, update_status
  - Departments: create_dept, edit_dept, delete_dept, assign_leader
  - Auth: login, logout, failed_login, password_reset

- **Immutability:**
  - Audit logs cannot be edited, deleted, or hidden
  - Permanent storage: PostgreSQL with index on user_id, created_at, action for performance
  - Retention: minimum 2 years (compliance)

- **Admin audit dashboard:**
  - View logs: filter by date range, user, action, entity_type
  - Search: by user_id or entity_id
  - Pagination: 50 logs per page
  - Show: user name, action, entity, changes (JSON preview), timestamp
  - Click log → expand to see full before/after values

- **Export functionality:**
  - CSV export: all selected logs with columns: timestamp, user, action, entity, changes
  - PDF report: summary of logs (e.g., all user creations in December)
  - File includes: exported_by, export_date, total_records

- **Privacy:**
  - Sensitive data (passwords, full phone numbers): not logged in audit (hashed/masked)
  - PII masked in exports (phone last 4 digits only, e.g., ...5678)

- **Performance:**
  - Query 1M+ audit records in <2 seconds (proper indexing)
  - Archival strategy: older logs (>2 years) moved to cold storage (optional)

**Definition of Done**
- ✅ Audit log entry created for every action tested
- ✅ Changes captured as JSON (before/after)
- ✅ Cannot delete audit logs (tested)
- ✅ Export CSV/PDF works
- ✅ Query performance <2s for 1M records
- ✅ Sensitive data masked in exports
- ✅ IP address and user_agent logged
- ✅ Audit log accessible only to Admin

**Boundaries (Out of Scope)**
- ❌ Real-time audit streaming (batch logging only)
- ❌ Machine learning anomaly detection
- ❌ GDPR automated data deletion (admin-driven only)

---

### US-19: Member Tier Lifecycle & Progression

**As an** Admin  
**I want to** manage member tiers (Visitor → Member → Partnership)  
**So that** communications, reports, and features align with member status

**Acceptance Criteria**

- **Tier definitions:**
  - **Visitor:** First-time attendee, not yet committed, status = `not_contacted` or `contacted` (PR tracked, US-12)
  - **Member:** Attended services for 52+ consecutive weeks (no 4+ week gap), committed to regular participation
  - **Partnership:** Active member + monthly financial commitment (recorded by Finance Officer, US-10)

- **Tier assignment:**
  - Admin can manually set tier: Visitor → Member → Partnership
  - Tier change form: select member, choose new tier, set start_date
  - For Partnership tier: capture monthly_commitment (GHS, required), frequency (weekly/monthly/as_able), commitment_start_date

- **Automatic tier progression (Member → Member only):**
  - System query (daily cron job, 2 AM):
    - Count approved attendances for each member in past 52 weeks
    - If >= 52 attendances AND no 4+ week gap → status = `member` (if currently `visitor`)
    - Notification sent (SMS): "Congratulations! You've been recognized as a member. Thank you for your commitment."
  - Manual override: Admin can promote manually without waiting 52 weeks

- **Tier change triggers:**
  - When tier changes, notification sent:
    - Visitor → Member: "Congratulations! You're now recognized as a member."
    - Member → Partnership: "Welcome to our partnership program! Your commitment supports the church."
    - Partnership → Member (downgrade): "Your partnership has ended. You remain a valued member."

- **Data impact:**
  - Tier affects:
    - **Broadcasts:** Partnership-only broadcasts visible only to partnership tier members
    - **Reports:** Member count vs. partner count
    - **Finance:** Partnership contributions tracked separately
    - **Eligibility:** Department Leader and Committee roles limited to Members+

- **Tier history:**
  - View member's tier progression: date_start, date_end, tier_name
  - Audit log: who changed tier, when, reason (optional note)

- **Permissions:**
  - Admin only can change tiers manually
  - Finance Officer cannot change tiers (can only record contributions)
  - PR/Follow-up can promote Visitor → Member on conversion (US-13)

**Definition of Done**
- ✅ Manual tier change works with notification
- ✅ Auto-promotion query runs daily (cron tested)
- ✅ 52-week attendance counted correctly (no 4+ week gaps)
- ✅ Tier change notifications sent (SMS tested)
- ✅ Tier history preserved in audit log
- ✅ Partnership tiers filtered in broadcasts (US-03)
- ✅ UI shows tier progression timeline

**Boundaries (Out of Scope)**
- ❌ Conditional tier requirements (attendance only, no tithes/giving)
- ❌ Tier demotion penalties
- ❌ Manual tier calculation (system does 52-week logic only)

---

### US-20: Offline Attendance Recording (Optional MVP+)

**As an** Usher  
**I want to** record attendance even without internet connectivity  
**So that** data collection continues during outages and is not lost

**Acceptance Criteria**

- **Offline queue:**
  - When offline (no network), attendance records queued locally in IndexedDB (web) or SQLite (native app)
  - User sees label: "Offline mode" with green/red indicator
  - Submission form still functional: add member, service, notes
  - Click "Submit" → record stored locally, no error message

- **Sync workflow:**
  - When online (network restored), app detects connection
  - Shows "Syncing..." progress indicator with count: "Syncing 3 pending records..."
  - Sends queued records to backend API in batch (max 100 per request)
  - Backend processes each record (validates, creates pending records)
  - Successful sync: "3 records synced. Waiting for approval."
  - Failed sync: "1 record failed to sync. Please try again." (with retry button)

- **Sync reliability:**
  - If sync fails mid-way, app retries:
    - 1st retry: 1 minute delay
    - 2nd retry: 5 minutes delay
    - 3rd retry: 10 minutes delay
    - Max 3 retries per record
  - Failed records after 3 retries show error in local queue (manual resync option)

- **Data persistence:**
  - Local queue persists if app closes unexpectedly
  - On app restart: show "X pending records waiting to sync"
  - Resume sync from where it left off

- **Conflict resolution:**
  - If same member marked twice for same service/date (offline, then online), backend detects duplicate
  - Sync shows: "Duplicate detected: [Member] already recorded for [Service] on [Date]."
  - Usher can delete duplicate from local queue before resync

- **Offline limitations:**
  - Cannot view approval status (requires live API)
  - Cannot search member autocomplete (cached list used if available)
  - Cannot sync with unreliable/slow connections (<50 Kbps)

**Definition of Done**
- ✅ Offline recording tested (network disabled scenario)
- ✅ Sync progress indicator visible and accurate
- ✅ Local storage persists after app close
- ✅ Duplicate detection works
- ✅ Retry logic tested (3 attempts with backoff)
- ✅ No data loss if app crashes during offline mode
- ✅ Performance: sync <2s for 100 records

**Boundaries (Out of Scope)**
- ❌ Offline member search/autocomplete (use cached list only)
- ❌ Offline approval workflow (approval happens online only)
- ❌ Sync scheduling (automatic on-demand only)

---

## CROSS-CUTTING CONCERNS

### Data Validation Rules

- **Phone numbers:** +233XXXXXXXXX format (Ghana), unique per user
- **Amounts (GHS):** decimal(10,2), > 0, required
- **Dates:** cannot be future, max 30 days in past for attendance
- **Character limits:** enforced on all text fields (noted per field)
- **Email:** valid email format or null (not required)
- **Status enums:** only valid transitions allowed (no regressions)

### Performance Standards

- **API response times:** <200ms for list queries, <500ms for dashboards
- **Search autocomplete:** <200ms for 100+ records
- **Chart rendering:** <1s for 12-month data
- **Bulk operations:** 500+ records <2s
- **Database queries:** indexed on user_id, created_at, status, date fields

### Security Standards

- **Authentication:** JWT (Sanctum), 30-day mobile session, 24-hour web session
- **Authorization:** Role-based access control (RBAC), enforced on backend only
- **Data isolation:** Department-scoped data (backend enforces)
- **Rate limiting:** 5 login attempts/15 min, max 100 broadcasts/hour
- **Encryption:** passwords (bcrypt/Argon2), TLS for transit
- **Audit trail:** immutable logs, PII masked in exports

### Mobile & Responsive Design

- Touch-friendly buttons (min 44px)
- Readable on screens 320px+ (mobile-first)
- Offline-capable (Story US-20)
- SMS as primary fallback for notifications
- No JavaScript required for core flows (progressive enhancement)

---

## STORY DEPENDENCIES & SEQUENCING

| Phase | Stories | Duration | Dependencies |
|-------|---------|----------|--------------|
| **Foundation** | US-17, US-01, US-18 | Week 1-2 | None (core infrastructure) |
| **Attendance Core** | US-08, US-09, US-02 | Week 3-4 | Foundation complete |
| **Communications** | US-03, US-16, US-12, US-13, US-14 | Week 5-6 | Attendance + Auth |
| **Finance & Reporting** | US-10, US-11, US-04 | Week 7-8 | Attendance verified |
| **Tiers & Delegation** | US-19, US-07, US-06 | Week 9 | Finance + Attendance |
| **Department Mgmt** | US-05, US-15 | Week 10 | Auth + Attendance |
| **Optional Enhancements** | US-20 (offline) | Week 11+ | Attendance stable |

---

## LOCKED DECISIONS

✅ **Frontend never enforces permissions** (backend always validates)
✅ **Only verified data in dashboards** (pending/rejected excluded)
✅ **Admin has final approval authority** (separation of concerns)
✅ **Audit trail is immutable** (compliance)
✅ **Department access always scoped** (no cross-dept visibility)
✅ **SMS fallback for all notifications** (reliability in Ghana)
✅ **Offline capability optional in MVP** (can ship without US-20)