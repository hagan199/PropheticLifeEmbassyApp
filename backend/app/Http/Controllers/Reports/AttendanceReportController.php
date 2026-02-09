<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    /**
     * Get attendance summary report
     */
    public function summary(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $totalRecords = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])->count();
        $totalAttendance = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->where('present', true)
            ->count();

        $approvedRecords = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'approved')
            ->count();

        $pendingRecords = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'pending')
            ->count();

        $totalMembers = User::whereNull('deactivated_at')->count();
        $averageAttendanceRate = $totalMembers > 0 ? round(($totalAttendance / $totalMembers) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_records' => $totalRecords,
                'total_attendance' => $totalAttendance,
                'approved_records' => $approvedRecords,
                'pending_records' => $pendingRecords,
                'total_members' => $totalMembers,
                'average_attendance_rate' => $averageAttendanceRate . '%',
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Get attendance by service type
     */
    public function byServiceType(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->select('service_type', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count'))
            ->groupBy('service_type')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'service_type' => $item->service_type,
                    'total_records' => $item->total,
                    'present_count' => $item->present_count,
                    'attendance_rate' => $item->total > 0 ? round(($item->present_count / $item->total) * 100, 2) . '%' : '0%',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get attendance trends (daily/weekly/monthly)
     */
    public function trends(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $groupBy = $request->get('group_by', 'daily'); // daily, weekly, monthly

        $query = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']]);

        switch ($groupBy) {
            case 'weekly':
                $data = $query
                    ->select(
                        DB::raw('YEARWEEK(service_date) as period'),
                        DB::raw('COUNT(*) as total'),
                        DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count')
                    )
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'period' => 'Week ' . substr($item->period, -2),
                            'total_records' => $item->total,
                            'present_count' => $item->present_count,
                            'attendance_rate' => $item->total > 0 ? round(($item->present_count / $item->total) * 100, 2) : 0,
                        ];
                    });
                break;

            case 'monthly':
                $data = $query
                    ->select(
                        DB::raw('DATE_FORMAT(service_date, "%Y-%m") as period'),
                        DB::raw('COUNT(*) as total'),
                        DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count')
                    )
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'period' => Carbon::parse($item->period . '-01')->format('M Y'),
                            'total_records' => $item->total,
                            'present_count' => $item->present_count,
                            'attendance_rate' => $item->total > 0 ? round(($item->present_count / $item->total) * 100, 2) : 0,
                        ];
                    });
                break;

            default: // daily
                $data = $query
                    ->select(
                        'service_date',
                        DB::raw('COUNT(*) as total'),
                        DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count')
                    )
                    ->groupBy('service_date')
                    ->orderBy('service_date')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'date' => Carbon::parse($item->service_date)->format('Y-m-d'),
                            'day' => Carbon::parse($item->service_date)->format('D, M d'),
                            'total_records' => $item->total,
                            'present_count' => $item->present_count,
                            'attendance_rate' => $item->total > 0 ? round(($item->present_count / $item->total) * 100, 2) : 0,
                        ];
                    });
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'group_by' => $groupBy,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get attendance by unit
     */
    public function byUnit(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('unit')
            ->select('unit', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count'))
            ->groupBy('unit')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'unit' => $item->unit,
                    'total_records' => $item->total,
                    'present_count' => $item->present_count,
                    'absent_count' => $item->total - $item->present_count,
                    'attendance_rate' => $item->total > 0 ? round(($item->present_count / $item->total) * 100, 2) . '%' : '0%',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get member attendance records
     */
    public function memberRecords(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $memberId = $request->get('member_id');

        $query = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']]);

        if ($memberId) {
            $query->where('member_id', $memberId);
        }

        $records = $query
            ->select('member_id', 'member_name', DB::raw('COUNT(*) as total_records'), DB::raw('SUM(CASE WHEN present = 1 THEN 1 ELSE 0 END) as present_count'))
            ->groupBy('member_id', 'member_name')
            ->orderBy('total_records', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($item) {
                return [
                    'member_id' => $item->member_id,
                    'member_name' => $item->member_name,
                    'total_services' => $item->total_records,
                    'attended' => $item->present_count,
                    'absent' => $item->total_records - $item->present_count,
                    'attendance_rate' => $item->total_records > 0 ? round(($item->present_count / $item->total_records) * 100, 2) . '%' : '0%',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $records,
            'total' => $records->count(),
        ]);
    }

    /**
     * Get top attendees
     */
    public function topAttendees(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $limit = $request->get('limit', 10);

        $data = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->where('present', true)
            ->whereNotNull('member_name')
            ->select('member_id', 'member_name', DB::raw('COUNT(*) as attendance_count'))
            ->groupBy('member_id', 'member_name')
            ->orderBy('attendance_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item, $index) {
                return [
                    'rank' => $index + 1,
                    'member_id' => $item->member_id,
                    'member_name' => $item->member_name,
                    'attendance_count' => $item->attendance_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get attendance by time period (morning/afternoon/evening)
     */
    public function byTimePeriod(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Attendance::whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('service_time')
            ->select('service_time', DB::raw('COUNT(*) as total'))
            ->groupBy('service_time')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Export attendance report
     */
    public function export(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $format = $request->get('format', 'csv'); // csv, pdf, excel

        $records = Attendance::with(['submittedBy', 'approvedBy'])
            ->whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->orderBy('service_date', 'desc')
            ->get();

        // For now, return JSON data structure
        // In production, you would use Laravel Excel or DomPDF
        return response()->json([
            'success' => true,
            'message' => 'Export data prepared. Implement ' . $format . ' export logic.',
            'total_records' => $records->count(),
            'data' => $records->map(function ($record) {
                return [
                    'Service Date' => $record->service_date->format('Y-m-d'),
                    'Service Type' => $record->service_type,
                    'Unit' => $record->unit ?? 'N/A',
                    'Member Name' => $record->member_name ?? 'N/A',
                    'Present' => $record->present ? 'Yes' : 'No',
                    'Status' => ucfirst($record->status),
                    'Submitted By' => $record->submittedBy?->name ?? 'Unknown',
                    'Submitted At' => $record->submitted_at?->format('Y-m-d H:i'),
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
