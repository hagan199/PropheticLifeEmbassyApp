<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DepartmentReportController extends Controller
{
    /**
     * Get departments summary report
     */
    public function summary(Request $request)
    {
        $totalDepartments = Department::count();
        $departmentsWithLeaders = Department::whereNotNull('leader_id')->count();
        $totalMembers = User::whereNotNull('department_id')->count();
        $averageMembersPerDept = $totalDepartments > 0 ? round($totalMembers / $totalDepartments, 2) : 0;

        $activeDepartments = Department::whereHas('members', function ($q) {
            $q->whereNull('deactivated_at');
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_departments' => $totalDepartments,
                'active_departments' => $activeDepartments,
                'departments_with_leaders' => $departmentsWithLeaders,
                'departments_without_leaders' => $totalDepartments - $departmentsWithLeaders,
                'total_members_in_departments' => $totalMembers,
                'average_members_per_department' => $averageMembersPerDept,
            ],
        ]);
    }

    /**
     * Get department performance
     */
    public function performance(Request $request)
    {
        $data = Department::withCount([
            'members',
            'members as active_members_count' => function ($q) {
                $q->whereNull('deactivated_at');
            }
        ])
            ->with('leader')
            ->get()
            ->map(function ($dept) {
                return [
                    'department_id' => $dept->id,
                    'department_name' => $dept->name,
                    'leader' => $dept->leader?->name ?? 'No Leader',
                    'total_members' => $dept->members_count,
                    'active_members' => $dept->active_members_count,
                    'inactive_members' => $dept->members_count - $dept->active_members_count,
                    'member_growth_rate' => '0%', // Calculate if you track historical data
                ];
            })
            ->sortByDesc('total_members')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get department size distribution
     */
    public function sizeDistribution(Request $request)
    {
        $departments = Department::withCount('members')->get();

        $small = $departments->filter(fn($d) => $d->members_count <= 10)->count();
        $medium = $departments->filter(fn($d) => $d->members_count > 10 && $d->members_count <= 30)->count();
        $large = $departments->filter(fn($d) => $d->members_count > 30)->count();

        return response()->json([
            'success' => true,
            'data' => [
                [
                    'size' => 'Small (1-10 members)',
                    'count' => $small,
                    'percentage' => $departments->count() > 0 ? round(($small / $departments->count()) * 100, 2) : 0,
                ],
                [
                    'size' => 'Medium (11-30 members)',
                    'count' => $medium,
                    'percentage' => $departments->count() > 0 ? round(($medium / $departments->count()) * 100, 2) : 0,
                ],
                [
                    'size' => 'Large (30+ members)',
                    'count' => $large,
                    'percentage' => $departments->count() > 0 ? round(($large / $departments->count()) * 100, 2) : 0,
                ],
            ],
        ]);
    }

    /**
     * Get specific department details
     */
    public function details(Request $request, $departmentId)
    {
        $department = Department::with(['leader', 'members' => function ($q) {
            $q->whereNull('deactivated_at');
        }])->findOrFail($departmentId);

        $dateRange = $this->getDateRange($request);

        // Get new members in period
        $newMembers = User::where('department_id', $departmentId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        // Get department attendance (if unit matches department name)
        $departmentAttendance = Attendance::where('unit', $department->name)
            ->whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
            ->where('present', true)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'department' => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'description' => $department->description,
                    'leader' => [
                        'id' => $department->leader?->id,
                        'name' => $department->leader?->name ?? 'No Leader',
                    ],
                ],
                'statistics' => [
                    'total_members' => $department->members->count(),
                    'new_members_in_period' => $newMembers,
                    'attendance_count' => $departmentAttendance,
                ],
                'members' => $department->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'role' => $member->role,
                        'joined_date' => $member->created_at->format('Y-m-d'),
                    ];
                }),
            ],
        ]);
    }

    /**
     * Get department member growth
     */
    public function memberGrowth(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $groupBy = $request->get('group_by', 'monthly');

        $departments = Department::all();

        $data = $departments->map(function ($dept) use ($dateRange, $groupBy) {
            $labels = [];
            $memberData = [];

            switch ($groupBy) {
                case 'weekly':
                    $current = $dateRange['start']->copy()->startOfWeek();
                    $weekNum = 1;
                    while ($current <= $dateRange['end']) {
                        $weekEnd = $current->copy()->endOfWeek();
                        $labels[] = 'Week ' . $weekNum;
                        $memberData[] = User::where('department_id', $dept->id)
                            ->where('created_at', '<=', $weekEnd)
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
                        $memberData[] = User::where('department_id', $dept->id)
                            ->where('created_at', '<=', $monthEnd)
                            ->count();
                        $current->addMonth();
                    }
                    break;
            }

            return [
                'department' => $dept->name,
                'data' => $memberData,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels ?? [],
                'departments' => $data,
            ],
            'group_by' => $groupBy,
        ]);
    }

    /**
     * Get department engagement report
     */
    public function engagement(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        $data = Department::withCount('members')
            ->get()
            ->map(function ($dept) use ($dateRange) {
                // Attendance engagement (using unit as proxy for department)
                $attendanceCount = Attendance::where('unit', $dept->name)
                    ->whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
                    ->where('present', true)
                    ->count();

                $uniqueAttendees = Attendance::where('unit', $dept->name)
                    ->whereBetween('service_date', [$dateRange['start'], $dateRange['end']])
                    ->where('present', true)
                    ->distinct('member_id')
                    ->count('member_id');

                $engagementRate = $dept->members_count > 0
                    ? round(($uniqueAttendees / $dept->members_count) * 100, 2)
                    : 0;

                return [
                    'department' => $dept->name,
                    'total_members' => $dept->members_count,
                    'unique_attendees' => $uniqueAttendees,
                    'total_attendance_records' => $attendanceCount,
                    'engagement_rate' => $engagementRate . '%',
                ];
            })
            ->sortByDesc('engagement_rate')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get departments without leaders
     */
    public function withoutLeaders(Request $request)
    {
        $departments = Department::whereNull('leader_id')
            ->withCount('members')
            ->get()
            ->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'member_count' => $dept->members_count,
                    'created_at' => $dept->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $departments,
            'total' => $departments->count(),
        ]);
    }

    /**
     * Get top performing departments
     */
    public function topPerforming(Request $request)
    {
        $limit = $request->get('limit', 5);
        $metric = $request->get('metric', 'members'); // members, growth, engagement

        $data = Department::withCount('members')
            ->orderBy('members_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($dept, $index) {
                return [
                    'rank' => $index + 1,
                    'department' => $dept->name,
                    'leader' => $dept->leader?->name ?? 'No Leader',
                    'member_count' => $dept->members_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'metric' => $metric,
            'total' => $data->count(),
        ]);
    }

    /**
     * Export department report
     */
    public function export(Request $request)
    {
        $departments = Department::with(['leader', 'members'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Export data prepared',
            'total_records' => $departments->count(),
            'data' => $departments->map(function ($dept) {
                return [
                    'Department Name' => $dept->name,
                    'Description' => $dept->description ?? 'N/A',
                    'Leader' => $dept->leader?->name ?? 'No Leader',
                    'Total Members' => $dept->members->count(),
                    'Active Members' => $dept->members->where('deactivated_at', null)->count(),
                    'Created Date' => $dept->created_at->format('Y-m-d'),
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
