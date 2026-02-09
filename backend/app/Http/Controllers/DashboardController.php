<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Visitor;
use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\FollowUp;
use App\Models\AuditLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * Returns role-specific stats based on logged-in user
     */
    public function stats(Request $request)
    {
        try {
            // Get user role from authenticated user
            $user = $request->user();
            $role = $user?->role ?? 'admin';

            // Get date range from request
            $range = $request->get('range', 'week');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            // Calculate date range
            $dateRange = $this->getDateRange($range, $startDate, $endDate);

            $stats = match ($role) {
                'admin' => $this->getAdminStats($dateRange),
                'pastor' => $this->getPastorStats(),
                'usher' => $this->getUsherStats(),
                'finance' => $this->getFinanceStats(),
                'pr_follow_up' => $this->getPRStats(),
                'department_leader' => $this->getDepartmentLeaderStats(),
                default => $this->getAdminStats($dateRange),
            };

            return response()->json([
                'success' => true,
                'data' => $stats,
                'range' => $range,
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate date range based on range type
     */
    private function getDateRange($range, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();

        if ($range === 'custom' && $startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay(),
            ];
        }

        return match ($range) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            default => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
        };
    }

    /**
     * Get analytics data
     */
    public function analytics(Request $request)
    {
        $range = $request->get('range', 'week');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Calculate date range
        $dateRange = $this->getDateRange($range, $startDate, $endDate);
        $start = $dateRange['start'];
        $end = $dateRange['end'];

        // Get real attendance data
        $attendanceLabels = [];
        $attendanceData = [];

        if ($range === 'today') {
            // Today - by hour (mock for now as we don't track hourly)
            $attendanceLabels = ['8AM', '9AM', '10AM', '11AM', '12PM'];
            $todayCount = Attendance::whereDate('service_date', Carbon::today())->count();
            $attendanceData = [$todayCount, $todayCount, $todayCount, $todayCount, $todayCount];
        } elseif ($range === 'week') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $attendanceLabels[] = $date->format('D');
                $count = Attendance::whereDate('service_date', $date->toDateString())->count();
                $attendanceData[] = $count;
            }
        } elseif ($range === 'month') {
            // Last 4 weeks
            for ($i = 3; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                $attendanceLabels[] = 'Week ' . (4 - $i);
                $count = Attendance::whereBetween('service_date', [$startOfWeek, $endOfWeek])->count();
                $attendanceData[] = $count;
            }
        } elseif ($range === 'custom') {
            // Custom range - group by day or week depending on span
            $daysDiff = $start->diffInDays($end);

            if ($daysDiff <= 14) {
                // Show daily data
                $current = $start->copy();
                while ($current <= $end) {
                    $attendanceLabels[] = $current->format('M d');
                    $count = Attendance::whereDate('service_date', $current->toDateString())->count();
                    $attendanceData[] = $count;
                    $current->addDay();
                }
            } else {
                // Show weekly data
                $current = $start->copy()->startOfWeek();
                $weekNum = 1;
                while ($current <= $end) {
                    $weekEnd = $current->copy()->endOfWeek();
                    $attendanceLabels[] = 'Week ' . $weekNum;
                    $count = Attendance::whereBetween('service_date', [$current, $weekEnd])->count();
                    $attendanceData[] = $count;
                    $current->addWeek();
                    $weekNum++;
                }
            }
        }

        // Get real finance data - based on range
        $financeLabels = [];
        $contributions = [];
        $expenses = [];

        if ($range === 'custom') {
            $daysDiff = $start->diffInDays($end);

            if ($daysDiff <= 31) {
                // Show weekly data for finance
                $current = $start->copy()->startOfWeek();
                $weekNum = 1;
                while ($current <= $end) {
                    $weekEnd = $current->copy()->endOfWeek();
                    $financeLabels[] = 'Week ' . $weekNum;
                    $contributions[] = Contribution::whereBetween('date', [$current, $weekEnd])->sum('amount');
                    $expenses[] = Expense::whereBetween('expense_date', [$current, $weekEnd])->sum('amount');
                    $current->addWeek();
                    $weekNum++;
                }
            } else {
                // Show monthly data
                $current = $start->copy()->startOfMonth();
                while ($current <= $end) {
                    $monthEnd = $current->copy()->endOfMonth();
                    $financeLabels[] = $current->format('M');
                    $contributions[] = Contribution::whereBetween('date', [$current, $monthEnd])->sum('amount');
                    $expenses[] = Expense::whereBetween('expense_date', [$current, $monthEnd])->sum('amount');
                    $current->addMonth();
                }
            }
        } else {
            // Default - last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $financeLabels[] = $month->format('M');
                $contributions[] = Contribution::whereYear('date', $month->year)
                    ->whereMonth('date', $month->month)
                    ->sum('amount');
                $expenses[] = Expense::whereYear('expense_date', $month->year)
                    ->whereMonth('expense_date', $month->month)
                    ->sum('amount');
            }
        }

        // Visitor conversion stats
        $visitorConversion = [
            'not_contacted' => Visitor::where('status', 'not_contacted')->orWhereNull('status')->count(),
            'contacted' => Visitor::where('status', 'contacted')->count(),
            'engaged' => Visitor::where('status', 'engaged')->count(),
            'converted' => Visitor::where('status', 'converted')->count(),
        ];

        // Follow-up Stats
        $followUpStats = [
            'due' => Visitor::dueForFollowUp()->count(),
            'completed' => FollowUp::whereBetween('updated_at', [$start, $end])->count(),
        ];

        // Conversion Rate
        $conversionRate = [
            'rate' => Visitor::where('status', 'converted')->count() > 0
                ? round((Visitor::where('status', 'converted')->count() / Visitor::count()) * 100)
                : 0,
            'converted' => Visitor::where('status', 'converted')->count(),
            'total' => Visitor::count(),
        ];

        $analytics = [
            'attendance_trend' => [
                'labels' => $attendanceLabels,
                'data' => $attendanceData,
            ],
            'finance_trend' => [
                'labels' => $financeLabels,
                'contributions' => $contributions,
                'expenses' => $expenses,
            ],
            'visitor_conversion' => $visitorConversion,
            'follow_up_stats' => $followUpStats,
            'conversion_rate' => $conversionRate,
            'department_distribution' => [],
            // Finance Breakdowns
            'finance_category' => [
                'labels' => Contribution::whereBetween('date', [$start, $end])
                    ->select('type')
                    ->distinct()
                    ->pluck('type'),
                'data' => Contribution::whereBetween('date', [$start, $end])
                    ->groupBy('type')
                    ->selectRaw('sum(amount) as total')
                    ->pluck('total')
            ],
            'finance_method' => [
                'labels' => Contribution::whereBetween('date', [$start, $end])
                    ->select('payment_method')
                    ->distinct()
                    ->pluck('payment_method'),
                'data' => Contribution::whereBetween('date', [$start, $end])
                    ->groupBy('payment_method')
                    ->selectRaw('sum(amount) as total')
                    ->pluck('total')
            ],
            'expense_category' => [
                'labels' => Expense::whereBetween('expense_date', [$start, $end])
                    ->select('category')
                    ->distinct()
                    ->pluck('category'),
                'data' => Expense::whereBetween('expense_date', [$start, $end])
                    ->groupBy('category')
                    ->selectRaw('sum(amount) as total')
                    ->pluck('total')
            ],
            // Visitor Analytics
            'visitor_sources' => [
                'labels' => Visitor::whereBetween('first_visit_date', [$start, $end])
                    ->select('source')
                    ->distinct()
                    ->pluck('source'),
                'data' => Visitor::whereBetween('first_visit_date', [$start, $end])
                    ->groupBy('source')
                    ->selectRaw('count(*) as total')
                    ->pluck('total')
            ],
            // Member Stats
            'user_growth' => [
                'labels' => User::selectRaw("TO_CHAR(created_at, 'YYYY-MM') as date")
                    ->whereBetween('created_at', [$start->copy()->subMonths(6), $end]) // Always show 6 month trend
                    ->groupBy('date')
                    ->pluck('date'),
                'data' => User::selectRaw("count(*) as total")
                    ->whereBetween('created_at', [$start->copy()->subMonths(6), $end])
                    ->groupBy(User::raw("TO_CHAR(created_at, 'YYYY-MM')"))
                    ->pluck('total')
            ],
            'user_roles' => [
                'labels' => User::select('role')->distinct()->pluck('role'),
                'data' => User::groupBy('role')->selectRaw('count(*) as total')->pluck('total')
            ],
            // Department Stats
            'department_distribution' => [
                'labels' => \App\Models\Department::pluck('name'),
                // Members per department
                'data' => \App\Models\User::selectRaw('department_id, count(*) as total')
                    ->groupBy('department_id')
                    ->pluck('total'),
                // Department growth (monthly trend)
                'growth' => \App\Models\User::selectRaw("department_id, TO_CHAR(created_at, 'YYYY-MM') as month, count(*) as total")
                    ->groupBy('department_id', 'month')
                    ->get(),
                // Active departments (with active members)
                'active' => \App\Models\Department::whereHas('members', function ($q) {
                    $q->where('status', 'active');
                })->pluck('name'),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'range' => $range,
        ]);
    }

    // ========== Role-specific Stats ==========

    /**
     * Admin dashboard stats - fetches real data from database
     */
    private function getAdminStats($dateRange = null)
    {
        // Default to this week if no date range provided
        if (!$dateRange) {
            $dateRange = [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek(),
            ];
        }

        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Get real counts from database for the date range
        $totalMembers = User::count();
        $membersInRange = User::whereBetween('created_at', [$startDate, $endDate])->count();

        $totalVisitors = Visitor::count();
        $visitorsInRange = Visitor::whereBetween('created_at', [$startDate, $endDate])->count();

        // Attendance stats for the range
        $attendanceInRange = Attendance::whereBetween('service_date', [$startDate, $endDate])->count();
        $attendancePercentage = $totalMembers > 0 ? round(($attendanceInRange / $totalMembers) * 100) : 0;
        // Cap at 100%
        $attendancePercentage = min($attendancePercentage, 100);

        // Finance stats for the range
        $contributionsInRange = Contribution::whereBetween('date', [$startDate, $endDate])->sum('amount');

        // Get previous period for comparison
        $rangeDays = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($rangeDays + 1);
        $previousEnd = $startDate->copy()->subDay();

        $contributionsPrevious = Contribution::whereBetween('date', [$previousStart, $previousEnd])->sum('amount');
        $financeChange = $contributionsPrevious > 0
            ? round((($contributionsInRange - $contributionsPrevious) / $contributionsPrevious) * 100)
            : ($contributionsInRange > 0 ? 100 : 0);

        // Follow-ups in range
        $pendingFollowUps = FollowUp::whereNull('status_after')
            ->orWhere('status_after', 'pending')
            ->count();

        // Pending approvals
        $pendingAttendance = Attendance::where('status', 'pending')->count();
        $pendingExpenses = Expense::where('status', 'pending')->count();

        // Conversion rate (visitors who became members)
        $convertedVisitors = Visitor::where('status', 'converted')->count();
        $conversionRate = $totalVisitors > 0 ? round(($convertedVisitors / $totalVisitors) * 100) : 0;

        // Recent members in the range

        $recentMembers = User::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone ?? null,
                    'status' => $user->status ?? 'active',
                    'joined' => $user->created_at->diffForHumans(),
                ];
            });

        // If no members in range, get most recent overall
        if ($recentMembers->isEmpty()) {
            $recentMembers = User::orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone ?? null,
                        'status' => $user->status ?? 'active',
                        'joined' => $user->created_at->diffForHumans(),
                    ];
                });
        }

        // Recent activities from audit log in range
        $recentActivities = AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'action' => $log->action,
                    'user' => $log->user?->name ?? 'System',
                    'time' => $log->created_at->diffForHumans(),
                ];
            });

        // If no activities in range, get most recent overall
        if ($recentActivities->isEmpty()) {
            $recentActivities = AuditLog::orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($log) {
                    return [
                        'action' => $log->action,
                        'user' => $log->user?->name ?? 'System',
                        'time' => $log->created_at->diffForHumans(),
                    ];
                });
        }

        // Format contribution amount
        $formattedContributions = $contributionsInRange >= 1000
            ? 'GHS ' . number_format($contributionsInRange / 1000, 1) . 'k'
            : 'GHS ' . number_format($contributionsInRange);

        // Determine label based on range
        $rangeLabel = match (true) {
            $startDate->isSameDay($endDate) => 'Today',
            $startDate->diffInDays($endDate) <= 7 => 'This Week',
            $startDate->diffInDays($endDate) <= 31 => 'This Month',
            default => 'Period'
        };

        return [
            'quick_actions' => [
                ['label' => 'Pending Approvals', 'count' => $pendingAttendance, 'link' => '/attendance-approvals'],
                ['label' => 'New Visitors', 'count' => $visitorsInRange, 'link' => '/visitors'],
                ['label' => 'Pending Expenses', 'count' => $pendingExpenses, 'link' => '/expense'],
            ],
            'overview' => [
                ['label' => 'Total Members', 'value' => $totalMembers, 'change' => '+' . $membersInRange . ' new', 'trend' => $membersInRange > 0 ? 'up' : 'neutral'],
                ['label' => $rangeLabel . ' Attendance', 'value' => $attendanceInRange, 'change' => $attendancePercentage . '% rate', 'trend' => 'up'],
                ['label' => 'New Visitors', 'value' => $visitorsInRange, 'change' => 'of ' . $totalVisitors . ' total', 'trend' => $visitorsInRange > 0 ? 'up' : 'neutral'],
                ['label' => $rangeLabel . ' Contributions', 'value' => $formattedContributions, 'change' => ($financeChange >= 0 ? '+' : '') . $financeChange . '%', 'trend' => $financeChange >= 0 ? 'up' : 'down'],
                ['label' => 'Conversion Rate', 'value' => $conversionRate . '%', 'change' => $convertedVisitors . ' converted', 'trend' => 'neutral'],
                ['label' => 'Pending Follow-ups', 'value' => $pendingFollowUps, 'change' => '0', 'trend' => 'neutral'],
                ['label' => 'Scheduled Events', 'value' => 0, 'change' => '0', 'trend' => 'neutral'],
            ],
            'recent_activities' => $recentActivities->toArray(),
            'recent_members' => $recentMembers->toArray(),
        ];
    }

    /**
     * Pastor dashboard stats
     */
    private function getPastorStats()
    {
        return [
            'overview' => [
                ['label' => 'Total Members', 'value' => 245, 'change' => '+12', 'trend' => 'up'],
                ['label' => 'This Week Attendance', 'value' => 185, 'change' => '+8', 'trend' => 'up'],
                ['label' => 'Average Attendance', 'value' => 178, 'change' => '+5', 'trend' => 'up'],
                ['label' => 'New Visitors (Month)', 'value' => 12, 'change' => '+3', 'trend' => 'up'],
            ],
            'attendance_summary' => [
                ['service' => 'Sunday Service', 'attendance' => 185, 'date' => 'Today'],
                ['service' => 'Friday Night', 'attendance' => 92, 'date' => '2 days ago'],
                ['service' => 'Midweek', 'attendance' => 78, 'date' => '3 days ago'],
            ],
        ];
    }

    /**
     * Usher dashboard stats
     */
    private function getUsherStats()
    {
        return [
            'my_submissions' => [
                ['label' => 'Pending', 'count' => 5, 'color' => 'warning'],
                ['label' => 'Approved', 'count' => 42, 'color' => 'success'],
                ['label' => 'Rejected', 'count' => 1, 'color' => 'danger'],
            ],
            'quick_actions' => [
                ['label' => 'Record Attendance', 'icon' => 'bi-check-circle', 'link' => '/attendance'],
                ['label' => 'View My Submissions', 'icon' => 'bi-list-check', 'link' => '/my-submissions'],
            ],
            'recent_records' => [
                ['member' => 'Emmanuel Agyei', 'service' => 'Sunday', 'status' => 'approved', 'date' => '2 days ago'],
                ['member' => 'Grace Addo', 'service' => 'Friday', 'status' => 'pending', 'date' => '3 days ago'],
            ],
        ];
    }

    /**
     * Finance dashboard stats
     */
    private function getFinanceStats()
    {
        return [
            'overview' => [
                ['label' => 'Monthly Contributions', 'value' => '₵53,000', 'change' => '+7%', 'trend' => 'up'],
                ['label' => 'Monthly Expenses', 'value' => '₵33,000', 'change' => '+2%', 'trend' => 'up'],
                ['label' => 'Net Position', 'value' => '₵20,000', 'change' => '+15%', 'trend' => 'up'],
                ['label' => 'Active Partners', 'value' => 45, 'change' => '+3', 'trend' => 'up'],
            ],
            'pending_approvals' => [
                ['description' => 'Sound equipment', 'amount' => '₵1,200', 'date' => 'Today'],
                ['description' => 'Office supplies', 'amount' => '₵350', 'date' => 'Yesterday'],
            ],
            'top_contributors' => [
                ['name' => 'Emmanuel Agyei', 'amount' => '₵5,000'],
                ['name' => 'Joseph Owusu', 'amount' => '₵4,500'],
                ['name' => 'Rebecca Mensah', 'amount' => '₵4,200'],
            ],
        ];
    }

    /**
     * PR/Follow-up dashboard stats
     */
    private function getPRStats()
    {
        return [
            'overview' => [
                ['label' => 'Total Visitors', 'value' => 28, 'change' => '+5', 'trend' => 'up'],
                ['label' => 'Pending Follow-ups', 'value' => 7, 'change' => '-2', 'trend' => 'down'],
                ['label' => 'Engaged Visitors', 'value' => 12, 'change' => '+3', 'trend' => 'up'],
                ['label' => 'Conversion Rate', 'value' => '54%', 'change' => '+8%', 'trend' => 'up'],
            ],
            'due_follow_ups' => [
                ['name' => 'Abena Mensah', 'phone' => '+233241222001', 'due_date' => 'Today'],
                ['name' => 'Kwabena Osei', 'phone' => '+233241222002', 'due_date' => 'Tomorrow'],
            ],
            'visitor_pipeline' => [
                ['status' => 'Not Contacted', 'count' => 5],
                ['status' => 'Contacted', 'count' => 12],
                ['status' => 'Engaged', 'count' => 8],
                ['status' => 'Converted', 'count' => 15],
            ],
        ];
    }

    /**
     * Department Leader dashboard stats
     */
    private function getDepartmentLeaderStats()
    {
        return [
            'department_info' => [
                'name' => 'Youth Ministry',
                'member_count' => 24,
                'active_members' => 20,
            ],
            'overview' => [
                ['label' => 'Total Members', 'value' => 24, 'change' => '+2', 'trend' => 'up'],
                ['label' => 'Active Members', 'value' => 20, 'change' => '0', 'trend' => 'neutral'],
                ['label' => 'Recent Activities', 'value' => 3, 'change' => '+1', 'trend' => 'up'],
            ],
            'recent_activities' => [
                ['date' => '2 days ago', 'activity' => 'Youth worship night', 'attendance' => 18],
                ['date' => '1 week ago', 'activity' => 'Bible study', 'attendance' => 15],
            ],
            'top_members' => [
                ['name' => 'Emmanuel Agyei', 'attendance_rate' => '95%'],
                ['name' => 'Grace Addo', 'attendance_rate' => '90%'],
            ],
        ];
    }
}
