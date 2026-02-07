<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Expense;
use App\Models\Attendance;
use App\Models\Visitor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get financial report for a specific month
     *
     * @param string $month Format: Y-m (e.g., 2026-02)
     * @param bool $useCache
     * @return array
     */
    public function getFinancialReport(string $month, bool $useCache = true): array
    {
        $cacheKey = "report.financial.{$month}";
        $cacheTTL = 3600; // 1 hour

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Previous month for comparison
        $prevStartDate = $startDate->copy()->subMonth()->startOfMonth();
        $prevEndDate = $prevStartDate->copy()->endOfMonth();

        // Current month contributions
        $contributions = Contribution::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('SUM(amount) as total, COUNT(*) as count')
            ->first();

        // Previous month for comparison
        $prevContributions = Contribution::whereBetween('date', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        // Expenses breakdown
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('SUM(amount) as total, COUNT(*) as count, status')
            ->groupBy('status')
            ->get();

        $totalExpenses = $expenses->sum('total');
        $pendingExpensesCount = $expenses->where('status', 'pending')->first()?->count ?? 0;
        $approvedExpensesCount = $expenses->where('status', 'approved')->first()?->count ?? 0;

        // Top contributors
        $topContributors = Contribution::whereBetween('date', [$startDate, $endDate])
            ->with('member')
            ->selectRaw('member_id, SUM(amount) as total_amount')
            ->groupBy('member_id')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->member?->name ?? 'Anonymous',
                    'amount' => $item->total_amount,
                ];
            });

        // Expense breakdown by type
        $expenseBreakdown = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->with('expenseType')
            ->selectRaw('expense_type_id, SUM(amount) as total_amount')
            ->groupBy('expense_type_id')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->expenseType?->name ?? 'Unknown',
                    'amount' => $item->total_amount,
                ];
            });

        // Contributions by type
        $contributionsByType = Contribution::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('contribution_type, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('contribution_type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->contribution_type ?? 'Regular',
                    'amount' => $item->total_amount,
                    'count' => $item->count,
                ];
            });

        $contributionChange = $prevContributions > 0
            ? round((($contributions->total - $prevContributions) / $prevContributions) * 100, 2)
            : 0;

        $report = [
            'month' => $month,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'contributions' => [
                'total' => $contributions->total ?? 0,
                'count' => $contributions->count ?? 0,
                'change_percent' => $contributionChange,
                'previous_month' => $prevContributions ?? 0,
                'by_type' => $contributionsByType,
            ],
            'expenses' => [
                'total' => $totalExpenses ?? 0,
                'count' => $expenses->sum('count'),
                'pending' => $pendingExpensesCount,
                'approved' => $approvedExpensesCount,
                'breakdown' => $expenseBreakdown,
            ],
            'net' => ($contributions->total ?? 0) - ($totalExpenses ?? 0),
            'top_contributors' => $topContributors,
        ];

        if ($useCache) {
            Cache::put($cacheKey, $report, $cacheTTL);
        }

        return $report;
    }

    /**
     * Get attendance report for a date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param bool $useCache
     * @return array
     */
    public function getAttendanceReport(string $startDate, string $endDate, bool $useCache = true): array
    {
        $cacheKey = "report.attendance.{$startDate}.{$endDate}";
        $cacheTTL = 3600;

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Total attendance by service type
        $byServiceType = Attendance::whereBetween('service_date', [$start, $end])
            ->where('status', 'approved')
            ->selectRaw('service_type, SUM(count) as total, COUNT(*) as records')
            ->groupBy('service_type')
            ->get();

        // Weekly breakdown
        $weeklyBreakdown = [];
        $current = $start->copy()->startOfWeek();
        while ($current <= $end) {
            $weekEnd = $current->copy()->endOfWeek();
            $count = Attendance::whereBetween('service_date', [$current, $weekEnd])
                ->where('status', 'approved')
                ->sum('count');

            $weeklyBreakdown[] = [
                'week' => $current->format('M d') . ' - ' . $weekEnd->format('M d'),
                'count' => $count,
            ];

            $current->addWeek();
        }

        // Average attendance
        $totalDays = Attendance::whereBetween('service_date', [$start, $end])
            ->where('status', 'approved')
            ->distinct('service_date')
            ->count('service_date');

        $totalAttendance = Attendance::whereBetween('service_date', [$start, $end])
            ->where('status', 'approved')
            ->sum('count');

        $report = [
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'total_attendance' => $totalAttendance,
            'average_per_service' => $totalDays > 0 ? round($totalAttendance / $totalDays, 2) : 0,
            'by_service_type' => $byServiceType,
            'weekly_breakdown' => $weeklyBreakdown,
        ];

        if ($useCache) {
            Cache::put($cacheKey, $report, $cacheTTL);
        }

        return $report;
    }

    /**
     * Get visitor report for a date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getVisitorReport(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $visitors = Visitor::whereBetween('visit_date', [$start, $end]);

        $totalVisitors = $visitors->count();
        $byCategory = Visitor::whereBetween('visit_date', [$start, $end])
            ->selectRaw('visitor_category, COUNT(*) as count')
            ->groupBy('visitor_category')
            ->get();

        $bySource = Visitor::whereBetween('visit_date', [$start, $end])
            ->selectRaw('how_they_heard, COUNT(*) as count')
            ->groupBy('how_they_heard')
            ->get();

        return [
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'total_visitors' => $totalVisitors,
            'by_category' => $byCategory,
            'by_source' => $bySource,
        ];
    }

    /**
     * Clear report cache
     *
     * @param string|null $type
     * @return void
     */
    public function clearReportCache(?string $type = null): void
    {
        if ($type) {
            $pattern = "report.{$type}.*";
            // Note: This is a simple implementation. For production, consider using cache tags
            Cache::flush();
        } else {
            Cache::flush();
        }
    }
}
