# Church Management Platform — Database Schema (PostgreSQL)

All primary keys use UUIDs generated through Laravel `HasUuids`. Timestamps follow Laravel defaults (`created_at`, `updated_at`). Monetary fields use `NUMERIC(12,2)`. Nullable columns are noted explicitly. Unless otherwise stated, foreign keys `SET NULL` on delete to preserve historical records.

## users

- `id UUID PRIMARY KEY`
- `phone VARCHAR(20) UNIQUE NOT NULL`
- `name VARCHAR(255) NOT NULL`
- `email VARCHAR(255) UNIQUE` (nullable for SMS-only accounts)
- `password_hash VARCHAR(255) NOT NULL`
- `role VARCHAR(50) NOT NULL` (`admin`, `pastor`, `usher`, `finance`, `pr_follow_up`, `department_leader`)
- `department_id UUID REFERENCES departments(id)`
- `can_approve_attendance BOOLEAN DEFAULT FALSE`
- `has_2fa BOOLEAN DEFAULT FALSE`
- `status VARCHAR(25) DEFAULT 'active'`
- `last_login_at TIMESTAMP WITH TIME ZONE`

**Indexes:** `(role)`, `(department_id)`, `(status)`

## departments

- `id UUID PRIMARY KEY`
- `name VARCHAR(120) UNIQUE NOT NULL`
- `description TEXT`
- `leader_id UUID REFERENCES users(id)`
- `status VARCHAR(25) DEFAULT 'active'`

**Indexes:** `(leader_id)`

## members

- `id UUID PRIMARY KEY`
- `user_id UUID REFERENCES users(id) UNIQUE`
- `tier VARCHAR(30) DEFAULT 'visitor'` (`visitor`, `member`, `partnership`)
- `first_service_date DATE`
- `first_visit_date DATE`
- `membership_start_date DATE`
- `partnership_start_date DATE`
- `monthly_commitment NUMERIC(12,2)`
- `commitment_frequency VARCHAR(30)` (`weekly`, `monthly`, `as_able`)
- `total_attendance_count INTEGER DEFAULT 0`
- `last_service_attended DATE`
- `status VARCHAR(25) DEFAULT 'active'`

**Indexes:** `(tier)`, `(status)`, `(monthly_commitment)`

## member_tier_history

- `id UUID PRIMARY KEY`
- `member_id UUID REFERENCES members(id)`
- `old_tier VARCHAR(30)`
- `new_tier VARCHAR(30)`
- `changed_by UUID REFERENCES users(id)`
- `reason TEXT`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`

**Indexes:** `(member_id)`, `(changed_by)`

## attendance

- `id UUID PRIMARY KEY`
- `member_id UUID REFERENCES members(id)` (nullable when storing aggregate counts)
- `service_type VARCHAR(40) NOT NULL` (`friday_night`, `sunday`, `midweek`, etc.)
- `attendance_date DATE NOT NULL`
- `notes TEXT`
- `submitted_by UUID REFERENCES users(id)`
- `submitted_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`
- `approval_status VARCHAR(25) DEFAULT 'pending'` (`pending`, `approved`, `rejected`)
- `approved_by UUID REFERENCES users(id)`
- `approved_at TIMESTAMP WITH TIME ZONE`
- `rejection_reason TEXT`
- `resubmitted_from UUID REFERENCES attendance(id)`

**Indexes:** `(attendance_date)`, `(service_type)`, `(approval_status)`, `(submitted_by)`, `(approved_by)`

## contributions

- `id UUID PRIMARY KEY`
- `member_id UUID REFERENCES members(id)`
- `amount NUMERIC(12,2) NOT NULL`
- `contribution_month DATE NOT NULL`
- `frequency VARCHAR(30)`
- `expected_date DATE`
- `purpose VARCHAR(120)`
- `status VARCHAR(25) DEFAULT 'pending'` (`pending`, `approved`, `rejected`, `overdue`)
- `reviewed_by UUID REFERENCES users(id)`
- `review_notes TEXT`
- `approved_by UUID REFERENCES users(id)`
- `payment_date DATE`
- `paid_amount NUMERIC(12,2)`
- `days_overdue INTEGER`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`
- `updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`

**Indexes:** `(member_id, contribution_month)`, `(status)`, `(expected_date)`

## expenses

- `id UUID PRIMARY KEY`
