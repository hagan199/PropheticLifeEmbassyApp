<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Visitor;
use App\Models\User;
use App\Models\Department;
use App\Models\FollowUp;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChartReportController extends Controller
{
    /**
     * Get attendance trend line chart data
     * Returns: { labels: [], datasets: [{ label, data, borderColor, backgroundColor }] }
     */
    public function attendanceTrendChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $groupBy = $request->get('group_by', 'daily');

        $labels = [];
        $presentData = [];
        $absentData = [];

        switch ($groupBy) {
            case 'weekly':
                $current = $dateRange['start']->copy()->startOfWeek();
                while ($current <= $dateRange['end']) {
                    $weekEnd = $current->copy()->endOfWeek();
                    $labels[] = $current->format('M d') . ' - ' . $weekEnd->format('M d');

                    $present = Attendance::whereBetween('service_date', [$current, $weekEnd])
                        ->where('present', true)->count();
                    $absent = Attendance::whereBetween('service_date', [$current, $weekEnd])
                        ->where('present', false)->count();

                    $presentData[] = $present;
                    $absentData[] = $absent;
                    $current->addWeek();
                }
                break;

            case 'monthly':
                $current = $dateRange['start']->copy()->startOfMonth();
                while ($current <= $dateRange['end']) {
                    $monthEnd = $current->copy()->endOfMonth();
                    $labels[] = $current->format('M Y');

                    $present = Attendance::whereBetween('service_date', [$current, $monthEnd])
                        ->where('present', true)->count();
                    $absent = Attendance::whereBetween('service_date', [$current, $monthEnd])
                        ->where('present', false)->count();

                    $presentData[] = $present;
                    $absentData[] = $absent;
                    $current->addMonth();
                }
                break;

            default: // daily
                $current = $dateRange['start']->copy();
                while ($current <= $dateRange['end']) {
                    $labels[] = $current->format('M d');

                    $present = Attendance::whereDate('service_date', $current)
                        ->where('present', true)->count();
                    $absent = Attendance::whereDate('service_date', $current)
                        ->where('present', false)->count();

                    $presentData[] = $present;
                    $absentData[] = $absent;
                    $current->addDay();
                }
                break;
        }

        return response()->json([
            'success' => true,
            'chart_type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Present',
                        'data' => $presentData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'Absent',
                        'data' => $absentData,
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get service type distribution pie chart
     */
    public function serviceTypePieChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->select('service_type', DB::raw('COUNT(*) as count'))
            ->groupBy('service_type')
            ->get();

        $labels = $data->pluck('service_type')->toArray();
        $values = $data->pluck('count')->toArray();
        $colors = $this->generateColors(count($labels));

        return response()->json([
            'success' => true,
            'chart_type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => $colors,
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get finance comparison bar chart (Contributions vs Expenses)
     */
    public function financeComparisonChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $groupBy = $request->get('group_by', 'monthly');

        $labels = [];
        $contributionData = [];
        $expenseData = [];

        switch ($groupBy) {
            case 'weekly':
                $current = $dateRange['start']->copy()->startOfWeek();
                while ($current <= $dateRange['end']) {
                    $weekEnd = $current->copy()->endOfWeek();
                    $labels[] = 'Week ' . $current->weekOfYear;

                    $contributionData[] = Contribution::whereBetween('date', [$current, $weekEnd])->sum('amount');
                    $expenseData[] = Expense::whereBetween('expense_date', [$current, $weekEnd])->sum('amount');
                    $current->addWeek();
                }
                break;

            default: // monthly
                $current = $dateRange['start']->copy()->startOfMonth();
                while ($current <= $dateRange['end']) {
                    $monthEnd = $current->copy()->endOfMonth();
                    $labels[] = $current->format('M Y');

                    $contributionData[] = Contribution::whereBetween('date', [$current, $monthEnd])->sum('amount');
                    $expenseData[] = Expense::whereBetween('expense_date', [$current, $monthEnd])->sum('amount');
                    $current->addMonth();
                }
                break;
        }

        return response()->json([
            'success' => true,
            'chart_type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Contributions',
                        'data' => $contributionData,
                        'backgroundColor' => '#10b981',
                        'borderColor' => '#059669',
                        'borderWidth' => 1,
                    ],
                    [
                        'label' => 'Expenses',
                        'data' => $expenseData,
                        'backgroundColor' => '#ef4444',
                        'borderColor' => '#dc2626',
                        'borderWidth' => 1,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get contribution types doughnut chart
     */
    public function contributionTypesDoughnutChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->orderBy('total', 'desc')
            ->get();

        $labels = $data->pluck('type')->toArray();
        $values = $data->pluck('total')->toArray();
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899'];

        return response()->json([
            'success' => true,
            'chart_type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => array_slice($colors, 0, count($labels)),
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get membership growth area chart
     */
    public function membershipGrowthChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $labels = [];
        $totalMembersData = [];
        $newMembersData = [];

        $current = $dateRange['start']->copy()->startOfMonth();
        while ($current <= $dateRange['end']) {
            $monthEnd = $current->copy()->endOfMonth();
            $labels[] = $current->format('M Y');

            $totalMembersData[] = User::where('created_at', '<=', $monthEnd)->count();
            $newMembersData[] = User::whereBetween('created_at', [$current, $monthEnd])->count();
            $current->addMonth();
        }

        return response()->json([
            'success' => true,
            'chart_type' => 'area',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total Members',
                        'data' => $totalMembersData,
                        'borderColor' => '#3b82f6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                    [
                        'label' => 'New Members',
                        'data' => $newMembersData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                        'fill' => true,
                        'tension' => 0.4,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get visitor conversion funnel chart
     */
    public function visitorConversionFunnelChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $totalVisitors = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])->count();
        $contacted = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'contacted')->count();
        $engaged = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'engaged')->count();
        $converted = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'converted')->count();

        return response()->json([
            'success' => true,
            'chart_type' => 'funnel',
            'data' => [
                'labels' => ['Total Visitors', 'Contacted', 'Engaged', 'Converted'],
                'datasets' => [
                    [
                        'data' => [$totalVisitors, $contacted, $engaged, $converted],
                        'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get department distribution horizontal bar chart
     */
    public function departmentDistributionChart(Request $request)
    {
        $departments = Department::withCount('members')
            ->orderBy('members_count', 'desc')
            ->limit(10)
            ->get();

        $labels = $departments->pluck('name')->toArray();
        $data = $departments->pluck('members_count')->toArray();

        return response()->json([
            'success' => true,
            'chart_type' => 'horizontalBar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Members',
                        'data' => $data,
                        'backgroundColor' => '#3b82f6',
                        'borderColor' => '#2563eb',
                        'borderWidth' => 1,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get unit attendance comparison radar chart
     */
    public function unitAttendanceRadarChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $units = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('unit')
            ->select('unit', DB::raw('COUNT(*) as count'))
            ->groupBy('unit')
            ->orderBy('count', 'desc')
            ->limit(8)
            ->get();

        $labels = $units->pluck('unit')->toArray();
        $data = $units->pluck('count')->toArray();

        return response()->json([
            'success' => true,
            'chart_type' => 'radar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Attendance Count',
                        'data' => $data,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                        'borderColor' => '#3b82f6',
                        'borderWidth' => 2,
                        'pointBackgroundColor' => '#3b82f6',
                        'pointBorderColor' => '#fff',
                        'pointRadius' => 4,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get expense categories breakdown polar chart
     */
    public function expenseCategoriesPolarChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        $labels = $data->pluck('category')->toArray();
        $values = $data->pluck('total')->toArray();
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#14b8a6'];

        return response()->json([
            'success' => true,
            'chart_type' => 'polarArea',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => array_slice($colors, 0, count($labels)),
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get member role distribution chart
     */
    public function memberRoleDistributionChart(Request $request)
    {
        $data = User::select('role', DB::raw('COUNT(*) as count'))
            ->whereNull('deactivated_at')
            ->groupBy('role')
            ->get();

        $labels = $data->pluck('role')->toArray();
        $values = $data->pluck('count')->toArray();
        $colors = $this->generateColors(count($labels));

        return response()->json([
            'success' => true,
            'chart_type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => $colors,
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get payment methods distribution chart
     */
    public function paymentMethodsChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $labels = $data->pluck('payment_method')->toArray();
        $values = $data->pluck('count')->toArray();
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6'];

        return response()->json([
            'success' => true,
            'chart_type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => array_slice($colors, 0, count($labels)),
                        'borderWidth' => 2,
                        'borderColor' => '#ffffff',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get visitor sources chart
     */
    public function visitorSourcesChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get();

        $labels = $data->pluck('source')->map(fn($s) => $s ?? 'Unknown')->toArray();
        $values = $data->pluck('count')->toArray();

        return response()->json([
            'success' => true,
            'chart_type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Visitor Count',
                        'data' => $values,
                        'backgroundColor' => '#8b5cf6',
                        'borderColor' => '#7c3aed',
                        'borderWidth' => 1,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get net position trend chart (Income - Expenses over time)
     */
    public function netPositionTrendChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $labels = [];
        $netPositionData = [];

        $current = $dateRange['start']->copy()->startOfMonth();
        while ($current <= $dateRange['end']) {
            $monthEnd = $current->copy()->endOfMonth();
            $labels[] = $current->format('M Y');

            $contributions = Contribution::whereBetween('date', [$current, $monthEnd])->sum('amount');
            $expenses = Expense::whereBetween('expense_date', [$current, $monthEnd])->sum('amount');
            $netPositionData[] = $contributions - $expenses;

            $current->addMonth();
        }

        return response()->json([
            'success' => true,
            'chart_type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Net Position',
                        'data' => $netPositionData,
                        'borderColor' => '#10b981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'borderWidth' => 3,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get attendance rate percentage chart (gauge/progress)
     */
    public function attendanceRateGaugeChart(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $totalMembers = User::whereNull('deactivated_at')->count();
        $attendanceCount = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->where('present', true)
            ->distinct('member_id')
            ->count('member_id');

        $rate = $totalMembers > 0 ? round(($attendanceCount / $totalMembers) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'chart_type' => 'gauge',
            'data' => [
                'value' => $rate,
                'max' => 100,
                'label' => 'Attendance Rate',
                'color' => $rate >= 70 ? '#10b981' : ($rate >= 50 ? '#f59e0b' : '#ef4444'),
            ],
        ]);
    }

    /**
     * Get multiple KPI metrics for dashboard
     */
    public function dashboardKpiCharts(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $totalMembers = User::count();
        $totalContributions = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])->sum('amount');
        $totalExpenses = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])->sum('amount');
        $visitorConversionRate = $this->calculateVisitorConversionRate($dateRange);

        return response()->json([
            'success' => true,
            'chart_type' => 'kpi',
            'data' => [
                [
                    'label' => 'Total Members',
                    'value' => $totalMembers,
                    'trend' => '+12%',
                    'color' => '#3b82f6',
                    'icon' => 'users',
                ],
                [
                    'label' => 'Contributions',
                    'value' => 'GHS ' . number_format($totalContributions, 2),
                    'trend' => '+8%',
                    'color' => '#10b981',
                    'icon' => 'trending-up',
                ],
                [
                    'label' => 'Expenses',
                    'value' => 'GHS ' . number_format($totalExpenses, 2),
                    'trend' => '+3%',
                    'color' => '#ef4444',
                    'icon' => 'trending-down',
                ],
                [
                    'label' => 'Conversion Rate',
                    'value' => $visitorConversionRate . '%',
                    'trend' => '+5%',
                    'color' => '#8b5cf6',
                    'icon' => 'percent',
                ],
            ],
        ]);
    }

    /**
     * Helper: Calculate visitor conversion rate
     */
    private function calculateVisitorConversionRate($dateRange)
    {
        $totalVisitors = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])->count();
        $converted = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'converted')->count();

        return $totalVisitors > 0 ? round(($converted / $totalVisitors) * 100, 1) : 0;
    }

    /**
     * Helper: Generate color palette
     */
    private function generateColors($count)
    {
        $baseColors = [
            '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6',
            '#ef4444', '#06b6d4', '#ec4899', '#14b8a6',
            '#f97316', '#84cc16', '#6366f1', '#a855f7',
        ];

        return array_slice($baseColors, 0, $count);
    }

    /**
     * Helper: Get date range from request
     */
    private function getDateRange(Request $request)
    {
        $range = $request->get('range', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($range === 'custom' && $startDate && $endDate) {
            return [
                'start' => Carbon::parse($startDate)->startOfDay(),
                'end' => Carbon::parse($endDate)->endOfDay(),
            ];
        }

        $now = Carbon::now();

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
            'year' => [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
            ],
            'last6months' => [
                'start' => $now->copy()->subMonths(6)->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
        };
    }
}
