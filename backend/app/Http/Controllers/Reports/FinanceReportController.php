<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contribution;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceReportController extends Controller
{
    /**
     * Get financial summary report
     */
    public function summary(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $totalContributions = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])->sum('amount');
        $totalExpenses = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])->sum('amount');
        $netPosition = $totalContributions - $totalExpenses;

        $contributionCount = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])->count();
        $expenseCount = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])->count();

        $approvedExpenses = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'approved')
            ->sum('amount');

        $pendingExpenses = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'pending')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_contributions' => number_format($totalContributions, 2),
                'total_expenses' => number_format($totalExpenses, 2),
                'net_position' => number_format($netPosition, 2),
                'net_position_status' => $netPosition >= 0 ? 'positive' : 'negative',
                'contribution_count' => $contributionCount,
                'expense_count' => $expenseCount,
                'approved_expenses' => number_format($approvedExpenses, 2),
                'pending_expenses' => number_format($pendingExpenses, 2),
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Get contributions by type
     */
    public function contributionsByType(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('type')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type ?? 'Other',
                    'count' => $item->count,
                    'total_amount' => number_format($item->total_amount, 2),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get contributions by payment method
     */
    public function contributionsByPaymentMethod(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'payment_method' => $item->payment_method ?? 'Cash',
                    'count' => $item->count,
                    'total_amount' => number_format($item->total_amount, 2),
                    'percentage' => 0, // Calculate in next step
                ];
            });

        $total = $data->sum(fn($item) => floatval(str_replace(',', '', $item['total_amount'])));

        $data = $data->map(function ($item) use ($total) {
            $amount = floatval(str_replace(',', '', $item['total_amount']));
            $item['percentage'] = $total > 0 ? round(($amount / $total) * 100, 2) : 0;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get expenses by category
     */
    public function expensesByCategory(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->select('category', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('category')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category ?? 'Uncategorized',
                    'count' => $item->count,
                    'total_amount' => number_format($item->total_amount, 2),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get financial trends
     */
    public function trends(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $groupBy = $request->get('group_by', 'monthly'); // daily, weekly, monthly

        $contributionData = [];
        $expenseData = [];
        $labels = [];

        switch ($groupBy) {
            case 'daily':
                $current = $dateRange['start']->copy();
                while ($current <= $dateRange['end']) {
                    $labels[] = $current->format('M d');
                    $contributionData[] = Contribution::whereDate('date', $current)->sum('amount');
                    $expenseData[] = Expense::whereDate('expense_date', $current)->sum('amount');
                    $current->addDay();
                }
                break;

            case 'weekly':
                $current = $dateRange['start']->copy()->startOfWeek();
                $weekNum = 1;
                while ($current <= $dateRange['end']) {
                    $weekEnd = $current->copy()->endOfWeek();
                    $labels[] = 'Week ' . $weekNum;
                    $contributionData[] = Contribution::whereBetween('date', [$current, $weekEnd])->sum('amount');
                    $expenseData[] = Expense::whereBetween('expense_date', [$current, $weekEnd])->sum('amount');
                    $current->addWeek();
                    $weekNum++;
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

        $netData = array_map(function ($contribution, $expense) {
            return $contribution - $expense;
        }, $contributionData, $expenseData);

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'contributions' => $contributionData,
                'expenses' => $expenseData,
                'net_position' => $netData,
            ],
            'group_by' => $groupBy,
        ]);
    }

    /**
     * Get top contributors
     */
    public function topContributors(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $limit = $request->get('limit', 10);

        $data = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->select('contributor_name', 'contributor_phone', DB::raw('COUNT(*) as contribution_count'), DB::raw('SUM(amount) as total_amount'))
            ->whereNotNull('contributor_name')
            ->groupBy('contributor_name', 'contributor_phone')
            ->orderBy('total_amount', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $item->contributor_name,
                    'phone' => $item->contributor_phone ?? 'N/A',
                    'contribution_count' => $item->contribution_count,
                    'total_amount' => number_format($item->total_amount, 2),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get expense approval report
     */
    public function expenseApprovals(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $approved = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'approved')
            ->count();

        $pending = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'pending')
            ->count();

        $rejected = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'rejected')
            ->count();

        $approvedAmount = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'approved')
            ->sum('amount');

        $pendingAmount = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'pending')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'approved' => [
                    'count' => $approved,
                    'amount' => number_format($approvedAmount, 2),
                ],
                'pending' => [
                    'count' => $pending,
                    'amount' => number_format($pendingAmount, 2),
                ],
                'rejected' => [
                    'count' => $rejected,
                ],
                'total_requests' => $approved + $pending + $rejected,
                'approval_rate' => ($approved + $pending + $rejected) > 0
                    ? round(($approved / ($approved + $pending + $rejected)) * 100, 2) . '%'
                    : '0%',
            ],
        ]);
    }

    /**
     * Get budget vs actual report
     */
    public function budgetVsActual(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        // This would require a budget table/model
        // For now, return structure
        return response()->json([
            'success' => true,
            'message' => 'Budget comparison report structure. Implement budget model for full functionality.',
            'data' => [
                'categories' => [
                    [
                        'category' => 'Operations',
                        'budgeted' => 10000,
                        'actual' => Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
                            ->where('category', 'Operations')
                            ->sum('amount'),
                        'variance' => 0,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Export finance report
     */
    public function export(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $type = $request->get('type', 'all'); // all, contributions, expenses

        $data = [];

        if ($type === 'all' || $type === 'contributions') {
            $contributions = Contribution::whereBetween('date', [$dateRange['start'], $dateRange['end']])
                ->orderBy('date', 'desc')
                ->get();

            $data['contributions'] = $contributions->map(function ($item) {
                return [
                    'Date' => $item->date->format('Y-m-d'),
                    'Type' => $item->type,
                    'Amount' => number_format($item->amount, 2),
                    'Payment Method' => $item->payment_method,
                    'Contributor' => $item->contributor_name ?? 'Anonymous',
                    'Reference' => $item->reference ?? 'N/A',
                ];
            });
        }

        if ($type === 'all' || $type === 'expenses') {
            $expenses = Expense::whereBetween('expense_date', [$dateRange['start'], $dateRange['end']])
                ->orderBy('expense_date', 'desc')
                ->get();

            $data['expenses'] = $expenses->map(function ($item) {
                return [
                    'Date' => $item->expense_date->format('Y-m-d'),
                    'Category' => $item->category,
                    'Amount' => number_format($item->amount, 2),
                    'Description' => $item->description ?? 'N/A',
                    'Status' => ucfirst($item->status),
                    'Approved By' => $item->approvedBy?->name ?? 'Pending',
                ];
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Export data prepared',
            'data' => $data,
        ]);
    }

    /**
     * Get date range from request
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
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
        };
    }
}
