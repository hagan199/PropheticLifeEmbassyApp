<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuditLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics']);

    // Users (Admin only)
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::post('/{id}/deactivate', [UserController::class, 'deactivate']);
        Route::post('/{id}/reactivate', [UserController::class, 'reactivate']);
    });

    // Roles & Permissions (Admin only)
    Route::prefix('roles')->group(function () {
        Route::get('/', [UserController::class, 'getRoles']);
        Route::get('/{role}/permissions', [UserController::class, 'getPermissions']);
    });

    // Attendance
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::post('/', [AttendanceController::class, 'store']);
        Route::get('/{id}', [AttendanceController::class, 'show']);
        Route::put('/{id}', [AttendanceController::class, 'update']);
        Route::delete('/{id}', [AttendanceController::class, 'destroy']);

        // Attendance Approvals (Admin)
        Route::get('/approvals/pending', [AttendanceController::class, 'pendingApprovals']);
        Route::post('/approvals/bulk-approve', [AttendanceController::class, 'bulkApprove']);
        Route::post('/approvals/bulk-reject', [AttendanceController::class, 'bulkReject']);
        Route::post('/{id}/approve', [AttendanceController::class, 'approve']);
        Route::post('/{id}/reject', [AttendanceController::class, 'reject']);

        // My Submissions (Usher)
        Route::get('/my-submissions', [AttendanceController::class, 'mySubmissions']);
    });

    // Visitors
    Route::prefix('visitors')->group(function () {
        Route::get('/', [VisitorController::class, 'index']);
        Route::post('/', [VisitorController::class, 'store']);
        Route::get('/{id}', [VisitorController::class, 'show']);
        Route::put('/{id}', [VisitorController::class, 'update']);
        Route::delete('/{id}', [VisitorController::class, 'destroy']);
        Route::get('/{id}/follow-ups', [VisitorController::class, 'followUps']);
    });

    // Follow-ups
    Route::prefix('follow-ups')->group(function () {
        Route::get('/', [FollowUpController::class, 'index']);
        Route::post('/', [FollowUpController::class, 'store']);
        Route::get('/due', [FollowUpController::class, 'dueList']);
        Route::get('/{id}', [FollowUpController::class, 'show']);
        Route::put('/{id}', [FollowUpController::class, 'update']);
        Route::delete('/{id}', [FollowUpController::class, 'destroy']);
    });

    // Finance - Contributions
    Route::prefix('contributions')->group(function () {
        Route::get('/', [FinanceController::class, 'indexContributions']);
        Route::post('/', [FinanceController::class, 'storeContribution']);
        Route::get('/{id}', [FinanceController::class, 'showContribution']);
        Route::put('/{id}', [FinanceController::class, 'updateContribution']);
        Route::delete('/{id}', [FinanceController::class, 'destroyContribution']);
        Route::get('/partner/{partnerId}', [FinanceController::class, 'partnerContributions']);
    });

    // Finance - Expenses
    Route::prefix('expenses')->group(function () {
        Route::get('/', [FinanceController::class, 'indexExpenses']);
        Route::post('/', [FinanceController::class, 'storeExpense']);
        Route::get('/{id}', [FinanceController::class, 'showExpense']);
        Route::put('/{id}', [FinanceController::class, 'updateExpense']);
        Route::delete('/{id}', [FinanceController::class, 'destroyExpense']);
        Route::post('/{id}/approve', [FinanceController::class, 'approveExpense']);
        Route::post('/{id}/reject', [FinanceController::class, 'rejectExpense']);

        // Expense Types
        Route::get('/types/all', [FinanceController::class, 'expenseTypes']);
        Route::post('/types', [FinanceController::class, 'storeExpenseType']);
    });

    // Finance - Reports
    Route::prefix('finance')->group(function () {
        Route::get('/reports/monthly', [FinanceController::class, 'monthlyReport']);
        Route::get('/reports/export', [FinanceController::class, 'exportReport']);
    });

    // Departments
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::get('/{id}', [DepartmentController::class, 'show']);
        Route::put('/{id}', [DepartmentController::class, 'update']);
        Route::delete('/{id}', [DepartmentController::class, 'destroy']);
        Route::get('/{id}/members', [DepartmentController::class, 'members']);
        Route::post('/{id}/members', [DepartmentController::class, 'addMember']);
        Route::delete('/{id}/members/{memberId}', [DepartmentController::class, 'removeMember']);

        // My Department (Department Leader)
        Route::get('/my/info', [DepartmentController::class, 'myDepartment']);
    });

    // Broadcasts
    Route::prefix('broadcasts')->group(function () {
        Route::get('/', [BroadcastController::class, 'index']);
        Route::post('/', [BroadcastController::class, 'store']);
        Route::get('/{id}', [BroadcastController::class, 'show']);
        Route::delete('/{id}', [BroadcastController::class, 'destroy']);
        Route::get('/{id}/deliveries', [BroadcastController::class, 'deliveries']);
    });

    // Audit Logs (Admin only)
    Route::prefix('audit-logs')->group(function () {
        Route::get('/', [AuditLogController::class, 'index']);
        Route::get('/{id}', [AuditLogController::class, 'show']);
        Route::get('/export', [AuditLogController::class, 'export']);
    });

    // Members
    Route::prefix('members')->group(function () {
        Route::get('/', [UserController::class, 'members']);
        Route::get('/search', [UserController::class, 'searchMembers']);
        Route::get('/{id}/tier-history', [UserController::class, 'tierHistory']);
        Route::post('/{id}/update-tier', [UserController::class, 'updateTier']);
    });
});
