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
