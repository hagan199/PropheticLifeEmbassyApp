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

// Report Controllers
use App\Http\Controllers\Reports\AttendanceReportController;
use App\Http\Controllers\Reports\FinanceReportController;
use App\Http\Controllers\Reports\MembershipReportController;
use App\Http\Controllers\Reports\VisitorReportController;
use App\Http\Controllers\Reports\DepartmentReportController;
use App\Http\Controllers\Reports\ChartReportController;

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
        Route::put('/{role}/permissions', [UserController::class, 'updatePermissions']);
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
        
        // Ministry Unit Attendance
        Route::post('/unit', [AttendanceController::class, 'storeUnitAttendance']);

        // Reports
        Route::get('/reports/weekly', [AttendanceController::class, 'weeklyReport']);
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
        Route::put('/types/{id}', [FinanceController::class, 'updateExpenseType']);
        Route::delete('/types/{id}', [FinanceController::class, 'deleteExpenseType']);
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


    // Import Insights (Admin & Finance)
    Route::prefix('import-insights')->group(function () {
        Route::get('/attendance', [\App\Http\Controllers\ImportInsightController::class, 'attendanceImportInsights']);
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

    // ============================================
    // REPORTS API - Comprehensive Reporting System
    // ============================================

    // Attendance Reports
    Route::prefix('reports/attendance')->group(function () {
        Route::get('/summary', [AttendanceReportController::class, 'summary']);
        Route::get('/by-service-type', [AttendanceReportController::class, 'byServiceType']);
        Route::get('/trends', [AttendanceReportController::class, 'trends']);
        Route::get('/by-unit', [AttendanceReportController::class, 'byUnit']);
        Route::get('/member-records', [AttendanceReportController::class, 'memberRecords']);
        Route::get('/top-attendees', [AttendanceReportController::class, 'topAttendees']);
        Route::get('/by-time-period', [AttendanceReportController::class, 'byTimePeriod']);
        Route::get('/export', [AttendanceReportController::class, 'export']);
    });

    // Finance Reports
    Route::prefix('reports/finance')->group(function () {
        Route::get('/summary', [FinanceReportController::class, 'summary']);
        Route::get('/contributions-by-type', [FinanceReportController::class, 'contributionsByType']);
        Route::get('/contributions-by-payment-method', [FinanceReportController::class, 'contributionsByPaymentMethod']);
        Route::get('/expenses-by-category', [FinanceReportController::class, 'expensesByCategory']);
        Route::get('/trends', [FinanceReportController::class, 'trends']);
        Route::get('/top-contributors', [FinanceReportController::class, 'topContributors']);
        Route::get('/expense-approvals', [FinanceReportController::class, 'expenseApprovals']);
        Route::get('/budget-vs-actual', [FinanceReportController::class, 'budgetVsActual']);
        Route::get('/export', [FinanceReportController::class, 'export']);
    });

    // Membership Reports
    Route::prefix('reports/membership')->group(function () {
        Route::get('/summary', [MembershipReportController::class, 'summary']);
        Route::get('/by-role', [MembershipReportController::class, 'byRole']);
        Route::get('/by-department', [MembershipReportController::class, 'byDepartment']);
        Route::get('/growth-trends', [MembershipReportController::class, 'growthTrends']);
        Route::get('/demographics', [MembershipReportController::class, 'demographics']);
        Route::get('/active-vs-inactive', [MembershipReportController::class, 'activeVsInactive']);
        Route::get('/new-members', [MembershipReportController::class, 'newMembers']);
        Route::get('/retention', [MembershipReportController::class, 'retention']);
        Route::get('/status-distribution', [MembershipReportController::class, 'statusDistribution']);
        Route::get('/export', [MembershipReportController::class, 'export']);
    });

    // Visitor Reports
    Route::prefix('reports/visitors')->group(function () {
        Route::get('/summary', [VisitorReportController::class, 'summary']);
        Route::get('/conversion-funnel', [VisitorReportController::class, 'conversionFunnel']);
        Route::get('/by-source', [VisitorReportController::class, 'bySource']);
        Route::get('/trends', [VisitorReportController::class, 'trends']);
        Route::get('/follow-up-effectiveness', [VisitorReportController::class, 'followUpEffectiveness']);
        Route::get('/pending-follow-ups', [VisitorReportController::class, 'pendingFollowUps']);
        Route::get('/retention-by-source', [VisitorReportController::class, 'retentionBySource']);
        Route::get('/export', [VisitorReportController::class, 'export']);
    });

    // Department Reports
    Route::prefix('reports/departments')->group(function () {
        Route::get('/summary', [DepartmentReportController::class, 'summary']);
        Route::get('/performance', [DepartmentReportController::class, 'performance']);
        Route::get('/size-distribution', [DepartmentReportController::class, 'sizeDistribution']);
        Route::get('/{departmentId}/details', [DepartmentReportController::class, 'details']);
        Route::get('/member-growth', [DepartmentReportController::class, 'memberGrowth']);
        Route::get('/engagement', [DepartmentReportController::class, 'engagement']);
        Route::get('/without-leaders', [DepartmentReportController::class, 'withoutLeaders']);
        Route::get('/top-performing', [DepartmentReportController::class, 'topPerforming']);
        Route::get('/export', [DepartmentReportController::class, 'export']);
    });

    // ============================================
    // CHART/GRAPH REPORTS - Visualization Ready
    // ============================================

    Route::prefix('reports/charts')->group(function () {
        // Attendance Charts
        Route::get('/attendance-trend', [ChartReportController::class, 'attendanceTrendChart']);
        Route::get('/service-type-pie', [ChartReportController::class, 'serviceTypePieChart']);
        Route::get('/unit-attendance-radar', [ChartReportController::class, 'unitAttendanceRadarChart']);
        Route::get('/attendance-rate-gauge', [ChartReportController::class, 'attendanceRateGaugeChart']);

        // Finance Charts
        Route::get('/finance-comparison', [ChartReportController::class, 'financeComparisonChart']);
        Route::get('/contribution-types-doughnut', [ChartReportController::class, 'contributionTypesDoughnutChart']);
        Route::get('/expense-categories-polar', [ChartReportController::class, 'expenseCategoriesPolarChart']);
        Route::get('/payment-methods', [ChartReportController::class, 'paymentMethodsChart']);
        Route::get('/net-position-trend', [ChartReportController::class, 'netPositionTrendChart']);

        // Membership Charts
        Route::get('/membership-growth', [ChartReportController::class, 'membershipGrowthChart']);
        Route::get('/member-role-distribution', [ChartReportController::class, 'memberRoleDistributionChart']);
        Route::get('/department-distribution', [ChartReportController::class, 'departmentDistributionChart']);

        // Visitor Charts
        Route::get('/visitor-conversion-funnel', [ChartReportController::class, 'visitorConversionFunnelChart']);
        Route::get('/visitor-sources', [ChartReportController::class, 'visitorSourcesChart']);

        // Dashboard KPI
        Route::get('/dashboard-kpi', [ChartReportController::class, 'dashboardKpiCharts']);
    });
});
