# Coding Standards & Best Practices
## Prophetic Life Embassy Church Management System

> **CTO Guidelines** - These standards are mandatory for all developers working on this project.

---

## Table of Contents
1. [General Principles](#general-principles)
2. [Backend Standards (Laravel)](#backend-standards-laravel)
3. [Frontend Standards (Vue.js)](#frontend-standards-vuejs)
4. [Database Standards](#database-standards)
5. [API Design](#api-design)
6. [Security Guidelines](#security-guidelines)
7. [Git Workflow](#git-workflow)
8. [Code Review Checklist](#code-review-checklist)

---

## General Principles

### 1. **Code Quality Over Speed**
- ✅ Write clean, readable, maintainable code
- ✅ Follow SOLID principles
- ✅ DRY (Don't Repeat Yourself)
- ❌ No copy-paste programming
- ❌ No TODO comments without tickets

### 2. **Documentation**
- All public methods MUST have PHPDoc/JSDoc comments
- Complex logic MUST be explained with inline comments
- README files MUST be kept up-to-date
- API endpoints MUST be documented

### 3. **Testing**
- All new features MUST include tests
- Bug fixes MUST include regression tests
- Maintain minimum 70% code coverage
- Run tests before pushing code

---

## Backend Standards (Laravel)

### 1. **Directory Structure**

```
app/
├── Http/
│   ├── Controllers/          # One controller per resource
│   ├── Requests/             # Form Request classes (REQUIRED)
│   │   ├── Auth/
│   │   ├── User/
│   │   ├── Attendance/
│   │   └── ...
│   ├── Middleware/
│   └── Resources/            # API Resources for transformations
├── Models/                   # Eloquent models
├── Services/                 # Business logic (if complex)
└── Repositories/             # Database queries (optional)
```

### 2. **Controllers - MANDATORY RULES**

#### ✅ DO:
```php
// Use Form Request classes for ALL validation
public function store(StoreUserRequest $request)
{
    $user = User::create($request->validated());
    return response()->json(['success' => true, 'data' => $user], 201);
}

// Keep controllers thin - delegate to services for complex logic
public function generateReport(GenerateReportRequest $request)
{
    $report = $this->reportService->generate($request->validated());
    return response()->json(['data' => $report]);
}

// Use resource classes for API responses
public function show(User $user)
{
    return new UserResource($user);
}
```

#### ❌ DON'T:
```php
// ❌ NEVER use inline validation
public function store(Request $request)
{
    $request->validate([...]); // FORBIDDEN
}

// ❌ NEVER put business logic in controllers
public function store(Request $request)
{
    // 50 lines of business logic... // WRONG
}

// ❌ NEVER return raw models
public function show(User $user)
{
    return $user; // FORBIDDEN - use Resources
}
```

### 3. **Form Request Classes - REQUIRED**

Every endpoint that accepts input MUST have a dedicated Form Request class:

```php
// app/Http/Requests/User/StoreUserRequest.php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Add permission checks here
        return $this->user()->can('create-users');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'role' => 'required|in:admin,pastor,usher,finance,pr_follow_up,department_leader',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'User name is required',
            'phone.unique' => 'This phone number is already registered',
        ];
    }
}
```

**Naming Convention:**
- Store operations: `Store{Resource}Request.php`
- Update operations: `Update{Resource}Request.php`
- Custom actions: `{Action}{Resource}Request.php`

### 4. **Models**

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasUuids; // REQUIRED for all models

    // REQUIRED: Define fillable fields
    protected $fillable = [
        'name',
        'phone',
        'email',
        'role',
    ];

    // REQUIRED: Hide sensitive data
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // REQUIRED: Define casts
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Relationships MUST have return types
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Accessors MUST use Attribute class (Laravel 11)
    protected function roleName(): Attribute
    {
        return Attribute::make(
            get: fn() => match($this->role) {
                'admin' => 'Administrator',
                'pastor' => 'Pastor',
                default => $this->role,
            }
        );
    }
}
```

### 5. **Database Migrations**

```php
// REQUIRED: UUID primary keys
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('name');
    $table->string('phone')->unique();
    $table->enum('role', ['admin', 'pastor', 'usher', 'finance', 'pr_follow_up', 'department_leader']);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes(); // For models that need soft delete

    // REQUIRED: Add indexes for foreign keys and frequently queried columns
    $table->index('phone');
    $table->index('role');
});

// REQUIRED: Always add foreign key constraints
Schema::create('attendance', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('member_id')->constrained('members')->onDelete('cascade');
    $table->foreignUuid('recorded_by')->constrained('users');
    $table->timestamps();
});
```

### 6. **API Responses - STANDARD FORMAT**

All API responses MUST follow this format:

```php
// Success Response
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => 'Operation successful', // Optional
], 200);

// Success with pagination
return response()->json([
    'success' => true,
    'data' => $items,
    'total' => $total,
    'page' => $page,
    'per_page' => $perPage,
], 200);

// Error Response
return response()->json([
    'success' => false,
    'message' => 'Error message here',
    'errors' => [...], // Validation errors (optional)
], 400);

// Created Response
return response()->json([
    'success' => true,
    'data' => $resource,
    'message' => 'Resource created successfully',
], 201);
```

### 7. **Service Classes (for complex logic)**

```php
// app/Services/AttendanceService.php
namespace App\Services;

class AttendanceService
{
    public function recordAttendance(array $data): Attendance
    {
        // Complex business logic here
        $attendance = Attendance::create($data);

        // Trigger events, notifications, etc.
        event(new AttendanceRecorded($attendance));

        return $attendance;
    }

    public function approveAttendance(Attendance $attendance, User $approver): void
    {
        $attendance->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        // Log audit trail
        AuditLog::create([...]);
    }
}
```

---

## Frontend Standards (Vue.js)

### 1. **Component Structure**

```vue
<template>
  <!-- Template should be clean and readable -->
  <div class="user-list">
    <h2>{{ title }}</h2>
    <UserCard
      v-for="user in users"
      :key="user.id"
      :user="user"
      @edit="handleEdit"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '@/store/user'
import UserCard from '@/components/UserCard.vue'

// Props
const props = defineProps({
  initialUsers: Array
})

// Emits
const emit = defineEmits(['userUpdated'])

// Composables
const userStore = useUserStore()

// State
const users = ref([])
const loading = ref(false)

// Computed
const title = computed(() => `Users (${users.value.length})`)

// Methods
const handleEdit = (user) => {
  // Handle edit logic
  emit('userUpdated', user)
}

// Lifecycle
onMounted(async () => {
  await loadUsers()
})
</script>

<style scoped>
/* Component-specific styles */
.user-list {
  padding: 1rem;
}
</style>
```

### 2. **Naming Conventions**

```javascript
// Components: PascalCase
UserProfile.vue
AttendanceList.vue
DashboardCard.vue

// Composables: camelCase with 'use' prefix
useAuth.js
useAttendance.js
useApiClient.js

// Stores: camelCase with 'Store' suffix
authStore.js
userStore.js
attendanceStore.js

// Constants: UPPER_SNAKE_CASE
const API_BASE_URL = 'http://localhost:8000/api'
const MAX_UPLOAD_SIZE = 5242880 // 5MB
```

### 3. **API Client Structure**

```javascript
// frontend/src/api/config.js
export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

// frontend/src/api/client.js
import axios from 'axios'
import { API_BASE_URL } from './config'
import { useAuthStore } from '@/store/auth'

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Request interceptor
apiClient.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`
  }
  return config
})

// Response interceptor
apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      const auth = useAuthStore()
      auth.logout()
    }
    return Promise.reject(error)
  }
)

export default apiClient

// frontend/src/api/users.js
import apiClient from './client'

export const userApi = {
  getAll: () => apiClient.get('/users'),
  getById: (id) => apiClient.get(`/users/${id}`),
  create: (data) => apiClient.post('/users', data),
  update: (id, data) => apiClient.put(`/users/${id}`, data),
  delete: (id) => apiClient.delete(`/users/${id}`),
}
```

### 4. **Pinia Store Pattern**

```javascript
// frontend/src/store/user.js
import { defineStore } from 'pinia'
import { userApi } from '@/api/users'

export const useUserStore = defineStore('user', {
  state: () => ({
    users: [],
    currentUser: null,
    loading: false,
    error: null,
  }),

  getters: {
    activeUsers: (state) => state.users.filter(u => u.is_active),
    getUserById: (state) => (id) => state.users.find(u => u.id === id),
  },

  actions: {
    async fetchUsers() {
      this.loading = true
      this.error = null
      try {
        const response = await userApi.getAll()
        this.users = response.data
      } catch (error) {
        this.error = error.message
        console.error('Failed to fetch users:', error)
      } finally {
        this.loading = false
      }
    },

    async createUser(userData) {
      try {
        const response = await userApi.create(userData)
        this.users.push(response.data)
        return response.data
      } catch (error) {
        this.error = error.message
        throw error
      }
    },
  },
})
```

---

## Database Standards

### 1. **UUID Primary Keys - MANDATORY**

```php
// All models MUST use UUID
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary(); // REQUIRED
    // ...
});

// Model MUST use HasUuids trait
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Model
{
    use HasUuids; // REQUIRED
}
```

### 2. **Naming Conventions**

```sql
-- Tables: plural, snake_case
users
attendance_records
follow_ups

-- Columns: snake_case
first_name
created_at
is_active

-- Foreign keys: singular_id
user_id
department_id
recorded_by (if references users)

-- Pivot tables: alphabetical order
department_member (not member_department)
```

### 3. **Timestamps & Soft Deletes**

```php
// REQUIRED: All tables must have timestamps
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    // ... columns
    $table->timestamps(); // REQUIRED
});

// Use soft deletes for data that should be recoverable
Schema::create('users', function (Blueprint $table) {
    $table->uuid('id')->primary();
    // ... columns
    $table->timestamps();
    $table->softDeletes(); // For recoverable data
});
```

### 4. **Indexes - REQUIRED**

```php
Schema::create('attendance', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('member_id')->constrained();
    $table->date('service_date');
    $table->enum('service_type', ['friday_night', 'sunday_service', 'midweek']);
    $table->enum('status', ['pending', 'approved', 'rejected']);
    $table->timestamps();

    // REQUIRED: Index foreign keys
    $table->index('member_id');

    // REQUIRED: Index frequently queried columns
    $table->index('service_date');
    $table->index('status');
    $table->index('service_type');

    // Composite indexes for common queries
    $table->index(['member_id', 'service_date']);
});
```

---

## API Design

### 1. **RESTful Routes - MANDATORY**

```php
// Use standard REST conventions
GET    /api/users              # List all users
POST   /api/users              # Create user
GET    /api/users/{id}         # Get single user
PUT    /api/users/{id}         # Update user
DELETE /api/users/{id}         # Delete user

// Nested resources
GET    /api/users/{id}/attendance        # User's attendance
GET    /api/departments/{id}/members     # Department members

// Custom actions (use POST for non-idempotent operations)
POST   /api/attendance/{id}/approve
POST   /api/attendance/{id}/reject
POST   /api/broadcasts/{id}/resend
```

### 2. **HTTP Status Codes - REQUIRED**

```php
200 OK                  // Successful GET, PUT, PATCH
201 Created             // Successful POST
204 No Content          // Successful DELETE
400 Bad Request         // Validation errors
401 Unauthorized        // Not authenticated
403 Forbidden           // Authenticated but not authorized
404 Not Found           // Resource not found
422 Unprocessable       // Validation failed (Laravel default)
500 Internal Error      // Server error
```

### 3. **Query Parameters**

```php
// Filtering
GET /api/users?role=admin&is_active=true

// Pagination
GET /api/users?page=2&per_page=20

// Sorting
GET /api/users?sort=name&order=asc

// Search
GET /api/users?q=john

// Date ranges
GET /api/attendance?from_date=2024-01-01&to_date=2024-01-31
```

---

## Security Guidelines

### 1. **Authentication - MANDATORY**

```php
// All API routes MUST be protected
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

// Public routes (login only)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
```

### 2. **Authorization**

```php
// Use policies for authorization
class UserPolicy
{
    public function update(User $user, User $model): bool
    {
        return $user->role === 'admin' || $user->id === $model->id;
    }
}

// In controller
public function update(UpdateUserRequest $request, User $user)
{
    $this->authorize('update', $user);
    // ...
}

// In Form Request
public function authorize(): bool
{
    return $this->user()->can('create-users');
}
```

### 3. **Input Validation - MANDATORY**

```php
// NEVER trust user input
// ALWAYS use Form Requests
// ALWAYS validate file uploads
public function upload(UploadFileRequest $request)
{
    // Validation in Request class
    'file' => 'required|file|mimes:jpg,png,pdf|max:5120',
}

// ALWAYS sanitize output
{{ $user->name }} // Blade auto-escapes
{!! $htmlContent !!} // Only use when necessary
```

### 4. **SQL Injection Prevention**

```php
// ✅ DO: Use Eloquent or Query Builder
User::where('phone', $phone)->first();
DB::table('users')->where('phone', $phone)->get();

// ❌ DON'T: Never use raw queries with user input
DB::select("SELECT * FROM users WHERE phone = '$phone'"); // FORBIDDEN
```

### 5. **Mass Assignment Protection**

```php
// REQUIRED: Define fillable or guarded
class User extends Model
{
    protected $fillable = ['name', 'phone', 'email']; // Whitelist
    // OR
    protected $guarded = ['id', 'role']; // Blacklist
}
```

---

## Git Workflow

### 1. **Branch Naming**

```bash
# Feature branches
feature/attendance-approval
feature/visitor-followup

# Bug fixes
bugfix/login-2fa-issue
bugfix/attendance-duplicate

# Hotfixes (production)
hotfix/security-patch
hotfix/payment-error
```

### 2. **Commit Messages**

```bash
# Format: <type>: <description>

# Types:
feat: Add 2FA verification for admin users
fix: Resolve attendance duplicate entry bug
refactor: Move validation to Form Request classes
docs: Update API documentation for broadcasts
test: Add unit tests for AttendanceService
chore: Update dependencies

# Good examples:
git commit -m "feat: Add bulk approval for attendance records"
git commit -m "fix: Prevent duplicate visitor registration"
git commit -m "refactor: Extract broadcast logic to service class"

# Bad examples:
git commit -m "updates"           # Too vague
git commit -m "fix bug"           # Not descriptive
git commit -m "WIP"               # Should not be pushed
```

### 3. **Pull Request Rules**

```markdown
## PR Template

### Description
Brief description of changes

### Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

### Checklist
- [ ] Code follows coding standards
- [ ] Added/updated tests
- [ ] All tests passing
- [ ] Updated documentation
- [ ] No console.log or dd() left in code
- [ ] Migrations tested (if applicable)

### Related Issue
Closes #123
```

### 4. **Required Before Merge**

- ✅ Code review approval (minimum 1)
- ✅ All tests passing
- ✅ No merge conflicts
- ✅ Branch up-to-date with main
- ✅ Follows coding standards

---

## Code Review Checklist

### Backend (Laravel)

- [ ] All validation uses Form Request classes (NO inline validation)
- [ ] Controllers are thin (business logic in services)
- [ ] All models use UUID primary keys with `HasUuids` trait
- [ ] Proper error handling and logging
- [ ] Database queries use Eloquent or Query Builder (no raw SQL)
- [ ] Migrations include proper indexes and foreign keys
- [ ] API responses follow standard format
- [ ] All routes are protected with authentication
- [ ] Authorization checks in place
- [ ] No sensitive data exposed in responses
- [ ] PHPDoc comments for all public methods

### Frontend (Vue.js)

- [ ] Components follow SFC (Single File Component) structure
- [ ] Proper use of Composition API
- [ ] API calls through centralized API client
- [ ] Loading and error states handled
- [ ] No hardcoded API URLs (use env variables)
- [ ] Proper prop validation
- [ ] Events properly emitted
- [ ] Styles are scoped
- [ ] No console.log in production code
- [ ] Accessible (ARIA attributes where needed)

### General

- [ ] Code is readable and maintainable
- [ ] No code duplication (DRY principle)
- [ ] Follows naming conventions
- [ ] Tests included and passing
- [ ] Documentation updated
- [ ] No commented-out code
- [ ] Environment variables used for config
- [ ] Error messages are user-friendly

---

## Enforcement

### Automated Checks

```json
// .github/workflows/ci.yml
name: CI
on: [push, pull_request]
jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run Laravel Pint
        run: ./vendor/bin/pint --test
      - name: Run PHPUnit
        run: php artisan test
      - name: Run ESLint
        run: npm run lint
```

### Pre-commit Hooks

```bash
# .husky/pre-commit
#!/bin/sh
. "$(dirname "$0")/_/husky.sh"

# Format code
./vendor/bin/pint

# Run tests
php artisan test

# Lint frontend
cd frontend && npm run lint
```

---

## Questions or Exceptions?

If you need to deviate from these standards:
1. Discuss with the team lead first
2. Document the reason in code comments
3. Get approval before merging

**Remember**: These standards exist to ensure code quality, maintainability, and team productivity. Following them is not optional.

---

**Last Updated**: 2026-02-01
**Maintained By**: CTO
**Version**: 1.0.0
