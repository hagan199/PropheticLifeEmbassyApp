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
- `amount NUMERIC(12,2) NOT NULL`
- `category VARCHAR(60) NOT NULL`
- `description TEXT`
- `expense_date DATE NOT NULL`
- `receipt_path VARCHAR(255)`
- `submitted_by UUID REFERENCES users(id)`
- `submitted_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`
- `approval_status VARCHAR(25) DEFAULT 'pending'`
- `approved_by UUID REFERENCES users(id)`
- `approved_at TIMESTAMP WITH TIME ZONE`
- `rejection_reason TEXT`

**Indexes:** `(category)`, `(expense_date)`, `(approval_status)`

## visitors

- `id UUID PRIMARY KEY`
- `name VARCHAR(255) NOT NULL`
- `phone VARCHAR(20)`
- `email VARCHAR(255)`
- `source VARCHAR(40) NOT NULL` (`friend`, `social_media`, `walk_in`, `other`)
- `source_detail VARCHAR(255)`
- `first_visit_date DATE`
- `ministry_interests JSONB`
- `initial_notes TEXT`
- `status VARCHAR(30) DEFAULT 'not_contacted'`
- `converted_to_member_id UUID REFERENCES members(id)`
- `converted_at TIMESTAMP WITH TIME ZONE`
- `created_by UUID REFERENCES users(id)`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`
- `updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`

**Indexes:** `(status)`, `(first_visit_date)`, `(source)`

## follow_ups

- `id UUID PRIMARY KEY`
- `visitor_id UUID REFERENCES visitors(id)`
- `contact_method VARCHAR(30)` (`whatsapp`, `sms`, `call`, `in_person`)
- `outcome_notes TEXT`
- `status_after VARCHAR(30)` (`not_contacted`, `contacted`, `engaged`, `converted`)
- `next_follow_up_date DATE`
- `logged_by UUID REFERENCES users(id)`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`

**Indexes:** `(visitor_id)`, `(next_follow_up_date)`, `(status_after)`

## broadcasts

- `id UUID PRIMARY KEY`
- `sent_by UUID REFERENCES users(id)`
- `channel VARCHAR(20)` (`sms`, `whatsapp`)
- `recipient_group VARCHAR(30)` (`all_members`, `partnerships`, `department`)
- `department_id UUID REFERENCES departments(id)`
- `message TEXT NOT NULL`
- `character_count INTEGER`
- `scheduled_for TIMESTAMP WITH TIME ZONE`
- `sent_at TIMESTAMP WITH TIME ZONE`
- `status VARCHAR(25) DEFAULT 'draft'` (`draft`, `scheduled`, `sending`, `sent`, `partially_sent`, `failed`)
- `total_recipients INTEGER`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`
- `updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`

**Indexes:** `(status)`, `(scheduled_for)`, `(department_id)`

## broadcast_deliveries

- `id UUID PRIMARY KEY`
- `broadcast_id UUID REFERENCES broadcasts(id) ON DELETE CASCADE`
- `recipient_id UUID REFERENCES users(id)`
- `channel VARCHAR(20)`
- `status VARCHAR(25)` (`pending`, `sent`, `failed`)
- `failure_reason TEXT`
- `sent_at TIMESTAMP WITH TIME ZONE`

**Indexes:** `(broadcast_id)`, `(recipient_id)`, `(status)`

## audit_logs

- `id UUID PRIMARY KEY`
- `user_id UUID REFERENCES users(id)`
- `action VARCHAR(120) NOT NULL`
- `entity_type VARCHAR(120) NOT NULL`
- `entity_id UUID`
- `changes JSONB`
- `ip_address INET`
- `user_agent VARCHAR(255)`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`

**Indexes:** `(user_id)`, `(entity_type, entity_id)`, `(created_at)`

## sessions

- `id UUID PRIMARY KEY`
- `user_id UUID REFERENCES users(id)`
- `device_name VARCHAR(120)`
- `login_ip INET`
- `last_active_at TIMESTAMP WITH TIME ZONE`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`
- `expires_at TIMESTAMP WITH TIME ZONE`

**Indexes:** `(user_id)`, `(expires_at)`

## two_factor_codes

- `id UUID PRIMARY KEY`
- `user_id UUID REFERENCES users(id)`
- `code VARCHAR(10) NOT NULL`
- `expires_at TIMESTAMP WITH TIME ZONE NOT NULL`
- `consumed_at TIMESTAMP WITH TIME ZONE`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`

**Indexes:** `(user_id)`, `(expires_at)`

## notifications

- `id UUID PRIMARY KEY`
- `user_id UUID REFERENCES users(id)`
- `type VARCHAR(120)`
- `payload JSONB`
- `read_at TIMESTAMP WITH TIME ZONE`
- `created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()`

**Indexes:** `(user_id, read_at)`

---

### Derived Views & Helpful Queries

- Attendance queue: `SELECT * FROM attendance WHERE approval_status = 'pending' ORDER BY attendance_date DESC;`
- Overdue partners: `SELECT * FROM contributions WHERE status = 'overdue' AND expected_date < NOW() - INTERVAL '7 days';`
- Follow-ups due: `SELECT v.name, f.next_follow_up_date FROM visitors v JOIN follow_ups f ON f.visitor_id = v.id WHERE f.next_follow_up_date <= CURRENT_DATE + INTERVAL '7 days' AND v.status <> 'converted';`

### Migration Notes

- Enable PostgreSQL extensions: `uuid-ossp` for UUID generation; `citext` if case-insensitive email lookup is required.
- Use Laravel enums or config constants to keep role and status values consistent across backend and frontend.
