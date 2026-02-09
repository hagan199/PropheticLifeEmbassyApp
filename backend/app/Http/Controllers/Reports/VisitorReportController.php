<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor;
use App\Models\FollowUp;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VisitorReportController extends Controller
{
    /**
     * Get visitor summary report
     */
    public function summary(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $totalVisitors = Visitor::count();
        $newVisitors = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])->count();

        $notContacted = Visitor::where('status', 'not_contacted')->orWhereNull('status')->count();
        $contacted = Visitor::where('status', 'contacted')->count();
        $engaged = Visitor::where('status', 'engaged')->count();
        $converted = Visitor::where('status', 'converted')->count();

        $conversionRate = $totalVisitors > 0 ? round(($converted / $totalVisitors) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_visitors' => $totalVisitors,
                'new_visitors' => $newVisitors,
                'pipeline' => [
                    'not_contacted' => $notContacted,
                    'contacted' => $contacted,
                    'engaged' => $engaged,
                    'converted' => $converted,
                ],
                'conversion_rate' => $conversionRate . '%',
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Get visitor conversion funnel
     */
    public function conversionFunnel(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $visitors = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])->count();
        $notContacted = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->where(function ($q) {
                $q->where('status', 'not_contacted')->orWhereNull('status');
            })
            ->count();
        $contacted = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'contacted')
            ->count();
        $engaged = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'engaged')
            ->count();
        $converted = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'converted')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                [
                    'stage' => 'Total Visitors',
                    'count' => $visitors,
                    'percentage' => 100,
                ],
                [
                    'stage' => 'Not Contacted',
                    'count' => $notContacted,
                    'percentage' => $visitors > 0 ? round(($notContacted / $visitors) * 100, 2) : 0,
                ],
                [
                    'stage' => 'Contacted',
                    'count' => $contacted,
                    'percentage' => $visitors > 0 ? round(($contacted / $visitors) * 100, 2) : 0,
                ],
                [
                    'stage' => 'Engaged',
                    'count' => $engaged,
                    'percentage' => $visitors > 0 ? round(($engaged / $visitors) * 100, 2) : 0,
                ],
                [
                    'stage' => 'Converted',
                    'count' => $converted,
                    'percentage' => $visitors > 0 ? round(($converted / $visitors) * 100, 2) : 0,
                ],
            ],
        ]);
    }

    /**
     * Get visitors by source
     */
    public function bySource(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']])
            ->select('source', DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'source' => $item->source ?? 'Unknown',
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get visitor trends
     */
    public function trends(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $groupBy = $request->get('group_by', 'monthly');

        $labels = [];
        $visitorData = [];
        $convertedData = [];

        switch ($groupBy) {
            case 'daily':
                $current = $dateRange['start']->copy();
                while ($current <= $dateRange['end']) {
                    $labels[] = $current->format('M d');
                    $visitorData[] = Visitor::whereDate('first_visit_date', $current)->count();
                    $convertedData[] = Visitor::whereDate('first_visit_date', $current)
                        ->where('status', 'converted')
                        ->count();
                    $current->addDay();
                }
                break;

            case 'weekly':
                $current = $dateRange['start']->copy()->startOfWeek();
                $weekNum = 1;
                while ($current <= $dateRange['end']) {
                    $weekEnd = $current->copy()->endOfWeek();
                    $labels[] = 'Week ' . $weekNum;
                    $visitorData[] = Visitor::whereBetween('first_visit_date', [$current, $weekEnd])->count();
                    $convertedData[] = Visitor::whereBetween('first_visit_date', [$current, $weekEnd])
                        ->where('status', 'converted')
                        ->count();
                    $current->addWeek();
                    $weekNum++;
                }
                break;

            default: // monthly
                $current = $dateRange['start']->copy()->startOfMonth();
                while ($current <= $dateRange['end']) {
                    $monthEnd = $current->copy()->endOfMonth();
                    $labels[] = $current->format('M Y');
                    $visitorData[] = Visitor::whereBetween('first_visit_date', [$current, $monthEnd])->count();
                    $convertedData[] = Visitor::whereBetween('first_visit_date', [$current, $monthEnd])
                        ->where('status', 'converted')
                        ->count();
                    $current->addMonth();
                }
                break;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'total_visitors' => $visitorData,
                'converted' => $convertedData,
            ],
            'group_by' => $groupBy,
        ]);
    }

    /**
     * Get follow-up effectiveness report
     */
    public function followUpEffectiveness(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $totalFollowUps = FollowUp::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();
        $completedFollowUps = FollowUp::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('status_after')
            ->where('status_after', '!=', 'pending')
            ->count();
        $pendingFollowUps = FollowUp::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where(function ($q) {
                $q->whereNull('status_after')->orWhere('status_after', 'pending');
            })
            ->count();

        $followUpRate = $totalFollowUps > 0 ? round(($completedFollowUps / $totalFollowUps) * 100, 2) : 0;

        // Visitors with follow-ups
        $visitorsWithFollowUps = Visitor::whereHas('followUps', function ($q) use ($dateRange) {
            $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        })->count();

        // Visitors converted after follow-up
        $convertedAfterFollowUp = Visitor::where('status', 'converted')
            ->whereHas('followUps', function ($q) use ($dateRange) {
                $q->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_follow_ups' => $totalFollowUps,
                'completed_follow_ups' => $completedFollowUps,
                'pending_follow_ups' => $pendingFollowUps,
                'follow_up_completion_rate' => $followUpRate . '%',
                'visitors_with_follow_ups' => $visitorsWithFollowUps,
                'converted_after_follow_up' => $convertedAfterFollowUp,
                'conversion_after_follow_up_rate' => $visitorsWithFollowUps > 0
                    ? round(($convertedAfterFollowUp / $visitorsWithFollowUps) * 100, 2) . '%'
                    : '0%',
            ],
        ]);
    }

    /**
     * Get pending follow-ups
     */
    public function pendingFollowUps(Request $request)
    {
        $limit = $request->get('limit', 50);

        $followUps = FollowUp::with('visitor')
            ->where(function ($q) {
                $q->whereNull('status_after')->orWhere('status_after', 'pending');
            })
            ->orderBy('next_contact_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($followUp) {
                return [
                    'id' => $followUp->id,
                    'visitor_name' => $followUp->visitor?->name ?? 'Unknown',
                    'visitor_phone' => $followUp->visitor?->phone,
                    'notes' => $followUp->notes,
                    'next_contact_date' => $followUp->next_contact_date?->format('Y-m-d'),
                    'days_overdue' => $followUp->next_contact_date
                        ? max(0, now()->diffInDays($followUp->next_contact_date, false) * -1)
                        : 0,
                    'created_at' => $followUp->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $followUps,
            'total' => $followUps->count(),
        ]);
    }

    /**
     * Get visitor retention by source
     */
    public function retentionBySource(Request $request)
    {
        $data = Visitor::select('source')
            ->selectRaw('COUNT(*) as total_visitors')
            ->selectRaw('SUM(CASE WHEN status = "converted" THEN 1 ELSE 0 END) as converted_count')
            ->groupBy('source')
            ->get()
            ->map(function ($item) {
                return [
                    'source' => $item->source ?? 'Unknown',
                    'total_visitors' => $item->total_visitors,
                    'converted' => $item->converted_count,
                    'conversion_rate' => $item->total_visitors > 0
                        ? round(($item->converted_count / $item->total_visitors) * 100, 2) . '%'
                        : '0%',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Export visitor report
     */
    public function export(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $status = $request->get('status', 'all');

        $query = Visitor::whereBetween('first_visit_date', [$dateRange['start'], $dateRange['end']]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $visitors = $query->orderBy('first_visit_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Export data prepared',
            'total_records' => $visitors->count(),
            'data' => $visitors->map(function ($visitor) {
                return [
                    'Name' => $visitor->name,
                    'Phone' => $visitor->phone,
                    'Email' => $visitor->email ?? 'N/A',
                    'First Visit Date' => $visitor->first_visit_date->format('Y-m-d'),
                    'Source' => $visitor->source ?? 'Unknown',
                    'Status' => ucfirst($visitor->status ?? 'not_contacted'),
                    'Last Contact' => $visitor->last_contact_date?->format('Y-m-d') ?? 'N/A',
                ];
            }),
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
