# API Resources & Helper Functions Guide

## Table of Contents
1. [API Resources](#api-resources)
2. [Helper Functions](#helper-functions)
3. [Usage Examples](#usage-examples)

---

## API Resources

API Resources transform your models into consistent JSON responses, hiding sensitive data and formatting relationships.

### Available Resources

| Resource | Purpose | Location |
|----------|---------|----------|
| [UserResource](backend/app/Http/Resources/UserResource.php) | Transform user data | `App\Http\Resources\UserResource` |
| [AttendanceResource](backend/app/Http/Resources/AttendanceResource.php) | Transform attendance records | `App\Http\Resources\AttendanceResource` |
| [VisitorResource](backend/app/Http/Resources/VisitorResource.php) | Transform visitor data | `App\Http\Resources\VisitorResource` |
| [FollowUpResource](backend/app/Http/Resources/FollowUpResource.php) | Transform follow-up records | `App\Http\Resources\FollowUpResource` |
| [DepartmentResource](backend/app/Http/Resources/DepartmentResource.php) | Transform department data | `App\Http\Resources\DepartmentResource` |
| [ContributionResource](backend/app/Http/Resources/ContributionResource.php) | Transform contribution records | `App\Http\Resources\ContributionResource` |
| [ExpenseResource](backend/app/Http/Resources/ExpenseResource.php) | Transform expense records | `App\Http\Resources\ExpenseResource` |
| [BroadcastResource](backend/app/Http/Resources/BroadcastResource.php) | Transform broadcast data | `App\Http\Resources\BroadcastResource` |
| [AuditLogResource](backend/app/Http/Resources/AuditLogResource.php) | Transform audit logs | `App\Http\Resources\AuditLogResource` |

### Basic Usage

```php
use App\Http\Resources\UserResource;
use App\Models\User;

// Single resource
public function show(User $user)
{
    return new UserResource($user);
}

// Collection
public function index()
{
    $users = User::all();
    return UserResource::collection($users);
}
```

### With Relationships

```php
public function show(User $user)
{
    // Load relationships
    $user->load('department', 'submissions');

    return new UserResource($user);
}
```

### Custom Response

```php
use App\Helpers\ResponseHelper;
use App\Http\Resources\UserResource;

public function show(User $user)
{
    return ResponseHelper::success(
        new UserResource($user),
        'User retrieved successfully'
    );
}
```

---

## Helper Functions

Helper functions provide reusable utilities across your application.

### 1. ResponseHelper

**Purpose**: Standardize API responses

**Location**: `App\Helpers\ResponseHelper`

#### Methods

```php
use App\Helpers\ResponseHelper;

// Success response
ResponseHelper::success($data, 'Success message', 200);

// Error response
ResponseHelper::error('Error message', $errors, 400);

// Created response (201)
ResponseHelper::created($resource, 'Resource created');

// Paginated response
ResponseHelper::paginated($collection, 'Data retrieved');

// Not found (404)
ResponseHelper::notFound('User not found');

// Unauthorized (401)
ResponseHelper::unauthorized('Invalid credentials');

// Forbidden (403)
ResponseHelper::forbidden('Access denied');

// Validation error (422)
ResponseHelper::validationError($errors, 'Validation failed');

// No content (204)
ResponseHelper::noContent();
```

#### Example

```php
public function store(StoreUserRequest $request)
{
    $user = User::create($request->validated());

    return ResponseHelper::created(
        new UserResource($user),
        'User created successfully'
    );
}
```

---

### 2. PhoneHelper

**Purpose**: Format and validate Ghana phone numbers

**Location**: `App\Helpers\PhoneHelper`

#### Methods

```php
use App\Helpers\PhoneHelper;

// Format phone number
PhoneHelper::format('0241234567');
// Returns: +233241234567

// Validate phone number
PhoneHelper::isValid('+233241234567');
// Returns: true

// Get local format
PhoneHelper::getLocal('+233241234567');
// Returns: 0241234567

// Mask for privacy
PhoneHelper::mask('+233241234567');
// Returns: +233XXXXX4567

// Compare phone numbers
PhoneHelper::isSame('0241234567', '+233241234567');
// Returns: true
```

#### Example

```php
public function store(StoreVisitorRequest $request)
{
    $data = $request->validated();

    // Format phone before saving
    $data['phone'] = PhoneHelper::format($data['phone']);

    $visitor = Visitor::create($data);

    return ResponseHelper::created(new VisitorResource($visitor));
}
```

---

### 3. DateHelper

**Purpose**: Handle dates and service schedules

**Location**: `App\Helpers\DateHelper`

#### Methods

```php
use App\Helpers\DateHelper;

// Get service types
DateHelper::getServiceTypes();
// Returns: ['friday_night' => 'Friday Night Service', ...]

// Get next service date
DateHelper::getNextServiceDate('sunday_service');
// Returns: Carbon instance of next Sunday at 9:00 AM

// Check if date is a service day
DateHelper::isServiceDay(Carbon::parse('2024-01-21'));
// Returns: true (if it's Fri/Sun/Wed)

// Get current month range
DateHelper::currentMonthRange();
// Returns: ['start' => Carbon, 'end' => Carbon]

// Format for display
DateHelper::formatForDisplay('2024-01-15');
// Returns: 15 Jan 2024

// Time ago
DateHelper::timeAgo('2024-01-01');
// Returns: 2 weeks ago

// Check if overdue
DateHelper::isOverdue('2024-01-01');
// Returns: true

// Convert to Ghana timezone
DateHelper::toGhanaTime(Carbon::now());
// Returns: Carbon instance in Africa/Accra timezone
```

#### Example

```php
public function store(StoreAttendanceRequest $request)
{
    $data = $request->validated();

    // Validate service day
    $serviceDate = Carbon::parse($data['service_date']);

    if (!DateHelper::isServiceDay($serviceDate)) {
        return ResponseHelper::error('Invalid service date', null, 422);
    }

    $attendance = Attendance::create($data);

    return ResponseHelper::created(new AttendanceResource($attendance));
}
```

---

### 4. AuditHelper

**Purpose**: Log audit trails

**Location**: `App\Helpers\AuditHelper`

#### Methods

```php
use App\Helpers\AuditHelper;

// General log
AuditHelper::log(
    $userId,
    'create',
    'user',
    $user->id,
    null,
    $user->toArray()
);

// Log creation
AuditHelper::logCreate($userId, 'user', $user->id, $user->toArray());

// Log update
AuditHelper::logUpdate($userId, 'user', $user->id, $oldData, $newData);

// Log deletion
AuditHelper::logDelete($userId, 'user', $user->id, $user->toArray());

// Log approval
AuditHelper::logApprove($userId, 'attendance', $attendance->id);

// Log rejection
AuditHelper::logReject($userId, 'attendance', $attendance->id, 'Duplicate entry');

// Mask sensitive data
AuditHelper::maskSensitiveData($data, ['password', 'token']);
```

#### Example

```php
public function update(UpdateUserRequest $request, User $user)
{
    $oldData = $user->toArray();

    $user->update($request->validated());

    // Log the update
    AuditHelper::logUpdate(
        auth()->id(),
        'user',
        $user->id,
        $oldData,
        $user->fresh()->toArray()
    );

    return ResponseHelper::success(new UserResource($user));
}
```

---

### 5. PermissionHelper

**Purpose**: Check permissions and roles

**Location**: `App\Helpers\PermissionHelper`

#### Methods

```php
use App\Helpers\PermissionHelper;

// Get permissions for a role
PermissionHelper::getPermissions('admin');
// Returns: ['users.view', 'users.create', ...]

// Check if role has permission
PermissionHelper::hasPermission('usher', 'attendance.record');
// Returns: true

// Check if has any permission
PermissionHelper::hasAnyPermission('usher', ['attendance.record', 'users.create']);
// Returns: true

// Check if has all permissions
PermissionHelper::hasAllPermissions('admin', ['users.view', 'users.create']);
// Returns: true

// Get all roles
PermissionHelper::getRoles();
// Returns: ['admin' => 'Administrator', ...]

// Check if role requires 2FA
PermissionHelper::requires2FA('admin');
// Returns: true

// Get role display name
PermissionHelper::getRoleName('admin');
// Returns: Administrator
```

#### Example

```php
public function approve(Attendance $attendance)
{
    // Check permission
    if (!PermissionHelper::hasPermission(auth()->user()->role, 'attendance.approve')) {
        return ResponseHelper::forbidden('You cannot approve attendance');
    }

    $attendance->update(['status' => 'approved']);

    AuditHelper::logApprove(auth()->id(), 'attendance', $attendance->id);

    return ResponseHelper::success(new AttendanceResource($attendance));
}
```

---

## Usage Examples

### Example 1: Complete CRUD with Resources & Helpers

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Helpers\ResponseHelper;
use App\Helpers\PhoneHelper;
use App\Helpers\AuditHelper;
use App\Helpers\PermissionHelper;

class UserController extends Controller
{
    /**
     * List all users
     */
    public function index()
    {
        $users = User::with('department')->paginate(20);

        return ResponseHelper::paginated(
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Get single user
     */
    public function show(User $user)
    {
        $user->load('department');

        return ResponseHelper::success(
            new UserResource($user),
            'User retrieved successfully'
        );
    }

    /**
     * Create new user
     */
    public function store(StoreUserRequest $request)
    {
        // Check permission
        if (!PermissionHelper::hasPermission(auth()->user()->role, 'users.create')) {
            return ResponseHelper::forbidden('You cannot create users');
        }

        $data = $request->validated();

        // Format phone number
        $data['phone'] = PhoneHelper::format($data['phone']);

        // Create user
        $user = User::create($data);

        // Log audit trail
        AuditHelper::logCreate(auth()->id(), 'user', $user->id, $user->toArray());

        return ResponseHelper::created(
            new UserResource($user),
            'User created successfully'
        );
    }

    /**
     * Update user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        // Check permission
        if (!PermissionHelper::hasPermission(auth()->user()->role, 'users.update')) {
            return ResponseHelper::forbidden('You cannot update users');
        }

        $oldData = $user->toArray();

        $data = $request->validated();

        // Format phone if provided
        if (isset($data['phone'])) {
            $data['phone'] = PhoneHelper::format($data['phone']);
        }

        $user->update($data);

        // Log audit trail
        AuditHelper::logUpdate(
            auth()->id(),
            'user',
            $user->id,
            $oldData,
            $user->fresh()->toArray()
        );

        return ResponseHelper::success(
            new UserResource($user),
            'User updated successfully'
        );
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Check permission
        if (!PermissionHelper::hasPermission(auth()->user()->role, 'users.delete')) {
            return ResponseHelper::forbidden('You cannot delete users');
        }

        $userData = $user->toArray();

        $user->delete();

        // Log audit trail
        AuditHelper::logDelete(auth()->id(), 'user', $user->id, $userData);

        return ResponseHelper::success(null, 'User deleted successfully');
    }
}
```

### Example 2: Attendance with Date Validation

```php
use App\Helpers\DateHelper;
use Carbon\Carbon;

public function store(StoreAttendanceRequest $request)
{
    $data = $request->validated();

    // Validate service date
    $serviceDate = Carbon::parse($data['service_date']);

    if (!DateHelper::isServiceDay($serviceDate)) {
        return ResponseHelper::error(
            'Attendance can only be recorded for service days (Friday, Sunday, Wednesday)',
            null,
            422
        );
    }

    // Check if future date
    if ($serviceDate->isFuture()) {
        return ResponseHelper::error('Cannot record attendance for future dates', null, 422);
    }

    $attendance = Attendance::create($data);

    AuditHelper::logCreate(auth()->id(), 'attendance', $attendance->id, $attendance->toArray());

    return ResponseHelper::created(new AttendanceResource($attendance));
}
```

### Example 3: Visitor with Phone Formatting

```php
use App\Helpers\PhoneHelper;

public function store(StoreVisitorRequest $request)
{
    $data = $request->validated();

    // Format phone
    $data['phone'] = PhoneHelper::format($data['phone']);

    // Check if visitor already exists
    $existingVisitor = Visitor::where('phone', $data['phone'])->first();

    if ($existingVisitor) {
        return ResponseHelper::error(
            'Visitor with phone ' . PhoneHelper::mask($data['phone']) . ' already exists',
            null,
            409
        );
    }

    $visitor = Visitor::create($data);

    AuditHelper::logCreate(auth()->id(), 'visitor', $visitor->id, $visitor->toArray());

    return ResponseHelper::created(new VisitorResource($visitor));
}
```

---

## Best Practices

### 1. Always Use Resources

```php
// ✅ Good
return ResponseHelper::success(new UserResource($user));

// ❌ Bad
return response()->json($user);
```

### 2. Always Use ResponseHelper

```php
// ✅ Good
return ResponseHelper::error('User not found', null, 404);

// ❌ Bad
return response()->json(['error' => 'User not found'], 404);
```

### 3. Format Phone Numbers

```php
// ✅ Good
$data['phone'] = PhoneHelper::format($data['phone']);

// ❌ Bad
// Saving phone without formatting
```

### 4. Log All Important Actions

```php
// ✅ Good
AuditHelper::logCreate(auth()->id(), 'user', $user->id, $user->toArray());

// ❌ Bad
// Not logging changes
```

### 5. Check Permissions

```php
// ✅ Good
if (!PermissionHelper::hasPermission(auth()->user()->role, 'users.delete')) {
    return ResponseHelper::forbidden();
}

// ❌ Bad
// Not checking permissions
```

---

## Summary

| Helper | Use For | Example |
|--------|---------|---------|
| **ResponseHelper** | All API responses | `ResponseHelper::success($data)` |
| **PhoneHelper** | Phone number formatting | `PhoneHelper::format($phone)` |
| **DateHelper** | Date operations | `DateHelper::isServiceDay($date)` |
| **AuditHelper** | Audit logging | `AuditHelper::logCreate(...)` |
| **PermissionHelper** | Permission checks | `PermissionHelper::hasPermission(...)` |

---

**Last Updated**: 2026-02-01
