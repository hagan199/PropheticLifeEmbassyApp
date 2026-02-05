# Quick Reference Guide
> Fast lookup for common patterns and conventions

## 🚀 Quick Commands

```bash
# Backend
cd backend
php artisan serve              # Start server (http://localhost:8000)
php artisan test               # Run tests
php artisan migrate            # Run migrations
php artisan make:request User/StoreUserRequest  # Create Form Request
./vendor/bin/pint              # Format code

# Frontend
cd frontend
npm run dev                    # Start dev server (http://localhost:5173)
npm run build                  # Production build
npm run lint                   # Lint code
npm run test                   # Run tests

# Docker
docker-compose up -d           # Start all services
docker-compose down            # Stop all services
docker-compose logs -f backend # View backend logs
```

---

## 📋 Common Patterns

### Create New API Endpoint (5 Steps)

1. **Create Form Request**
```bash
php artisan make:request Attendance/StoreAttendanceRequest
```

2. **Add Validation Rules**
```php
// app/Http/Requests/Attendance/StoreAttendanceRequest.php
public function rules(): array
{
    return [
        'member_id' => 'required|string',
        'service_date' => 'required|date',
    ];
}
```

3. **Create/Update Controller Method**
```php
// app/Http/Controllers/AttendanceController.php
use App\Http\Requests\Attendance\StoreAttendanceRequest;

public function store(StoreAttendanceRequest $request)
{
    $attendance = Attendance::create($request->validated());
    return response()->json(['success' => true, 'data' => $attendance], 201);
}
```

4. **Add Route**
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/attendance', [AttendanceController::class, 'store']);
});
```

5. **Create Frontend API Call**
```javascript
// frontend/src/api/attendance.js
export const attendanceApi = {
  create: (data) => apiClient.post('/attendance', data)
}
```

---

## 🎯 Must-Follow Rules

| ✅ DO | ❌ DON'T |
|-------|----------|
| Use Form Request classes | Inline validation in controllers |
| UUID primary keys with `HasUuids` | Auto-increment IDs |
| Standard API response format | Inconsistent responses |
| API client for all requests | Direct axios calls |
| Pinia stores for state | Props drilling |
| Route protection with auth:sanctum | Public API routes |

---

## 📁 File Naming

```
Backend:
✅ StoreUserRequest.php
✅ UserController.php
✅ AttendanceService.php
❌ store_user_request.php
❌ userController.php

Frontend:
✅ UserProfile.vue
✅ useAuth.js
✅ userStore.js
❌ user-profile.vue
❌ UseAuth.js
```

---

## 🔐 Standard API Response

```javascript
// Success
{
  "success": true,
  "data": {...},
  "message": "Optional message"
}

// Error
{
  "success": false,
  "message": "Error description",
  "errors": {...}  // Validation errors
}
```

---

## 📊 HTTP Status Codes

```
200 OK          - GET/PUT success
201 Created     - POST success
204 No Content  - DELETE success
400 Bad Request - Client error
401 Unauthorized - Not logged in
403 Forbidden   - No permission
404 Not Found   - Resource missing
422 Unprocessable - Validation failed
500 Server Error - Backend issue
```

---

## 🔄 Git Workflow

```bash
# 1. Create branch
git checkout -b feature/attendance-approval

# 2. Make changes and commit
git add .
git commit -m "feat: Add attendance approval workflow"

# 3. Push and create PR
git push -u origin feature/attendance-approval

# 4. After approval, merge via GitHub
```

### Commit Message Format
```
feat: Add new feature
fix: Fix bug
refactor: Refactor code
docs: Update documentation
test: Add tests
chore: Update dependencies
```

---

## 🧪 Testing

```php
// Backend Test
php artisan make:test AttendanceTest

public function test_user_can_record_attendance()
{
    $user = User::factory()->create(['role' => 'usher']);
    $response = $this->actingAs($user)
        ->postJson('/api/attendance', [
            'member_id' => 'mem-001',
            'service_date' => '2024-01-15',
        ]);
    $response->assertStatus(201);
}
```

```javascript
// Frontend Test (Vitest)
import { describe, it, expect } from 'vitest'

describe('UserCard', () => {
  it('displays user name', () => {
    const wrapper = mount(UserCard, {
      props: { user: { name: 'John' } }
    })
    expect(wrapper.text()).toContain('John')
  })
})
```

---

## 🚨 Common Issues & Solutions

### Backend

**Issue**: Validation not working
```php
// ❌ Wrong
public function store(Request $request) {
    $request->validate([...]);
}

// ✅ Correct
public function store(StoreUserRequest $request) {
    // Validation automatic
}
```

**Issue**: CORS error
```php
// config/cors.php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
],
```

**Issue**: Auth not working
```php
// Make sure route is protected
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes here
});
```

### Frontend

**Issue**: API 401 error
```javascript
// Check if token is set
const auth = useAuthStore()
console.log(auth.token) // Should not be null
```

**Issue**: Can't access API
```javascript
// Check .env file
VITE_API_BASE_URL=http://localhost:8000/api
```

---

## 📞 Need Help?

1. Check [CODING_STANDARDS.md](CODING_STANDARDS.md)
2. Ask in team Slack channel
3. Create GitHub issue with `question` label

---

**Last Updated**: 2026-02-01
