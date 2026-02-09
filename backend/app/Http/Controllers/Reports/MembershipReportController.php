<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipReportController extends Controller
{
    /**
     * Get membership summary report
     */
    public function summary(Request $request)
    {
        $totalMembers = User::count();
        $activeMembers = User::whereNull('deactivated_at')->count();
        $inactiveMembers = User::whereNotNull('deactivated_at')->count();

        $dateRange = $this->getDateRange($request);
        $newMembers = User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();

        // Get previous period for comparison
        $rangeDays = $dateRange['start']->diffInDays($dateRange['end']);
        $previousStart = $dateRange['start']->copy()->subDays($rangeDays + 1);
        $previousEnd = $dateRange['start']->copy()->subDay();
        $previousNewMembers = User::whereBetween('created_at', [$previousStart, $previousEnd])->count();

        $growthRate = $previousNewMembers > 0
            ? round((($newMembers - $previousNewMembers) / $previousNewMembers) * 100, 2)
            : ($newMembers > 0 ? 100 : 0);

        return response()->json([
            'success' => true,
            'data' => [
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'inactive_members' => $inactiveMembers,
                'new_members' => $newMembers,
                'growth_rate' => $growthRate . '%',
                'retention_rate' => $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100, 2) . '%' : '0%',
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Get membership by role
     */
    public function byRole(Request $request)
    {
        $data = User::select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) use ($request) {
                $activeCount = User::where('role', $item->role)
                    ->whereNull('deactivated_at')
                    ->count();

                return [
                    'role' => $item->role ?? 'No Role',
                    'total_count' => $item->count,
                    'active_count' => $activeCount,
                    'inactive_count' => $item->count - $activeCount,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get membership by department
     */
    public function byDepartment(Request $request)
    {
        $data = Department::withCount('members')
            ->orderBy('members_count', 'desc')
            ->get()
            ->map(function ($dept) {
                return [
                    'department_id' => $dept->id,
                    'department_name' => $dept->name,
                    'member_count' => $dept->members_count,
                    'leader' => $dept->leader?->name ?? 'No Leader',
                ];
            });

        // Add members without department
        $withoutDept = User::whereNull('department_id')->count();
        if ($withoutDept > 0) {
            $data->push([
                'department_id' => null,
                'department_name' => 'No Department',
                'member_count' => $withoutDept,
                'leader' => 'N/A',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    /**
     * Get membership growth trends
     */
    public function growthTrends(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $groupBy = $request->get('group_by', 'monthly'); // daily, weekly, monthly

        $labels = [];
        $newMembersData = [];
        $totalMembersData = [];

        switch ($groupBy) {
            case 'daily':
                $current = $dateRange['start']->copy();
                while ($current <= $dateRange['end']) {
                    $labels[] = $current->format('M d');
                    $newMembersData[] = User::whereDate('created_at', $current)->count();
                    $totalMembersData[] = User::where('created_at', '<=', $current->endOfDay())->count();
                    $current->addDay();
                }
                break;

            case 'weekly':
                $current = $dateRange['start']->copy()->startOfWeek();
                $weekNum = 1;
                while ($current <= $dateRange['end']) {
                    $weekEnd = $current->copy()->endOfWeek();
                    $labels[] = 'Week ' . $weekNum;
                    $newMembersData[] = User::whereBetween('created_at', [$current, $weekEnd])->count();
                    $totalMembersData[] = User::where('created_at', '<=', $weekEnd)->count();
                    $current->addWeek();
                    $weekNum++;
                }
                break;

            default: // monthly
                $current = $dateRange['start']->copy()->startOfMonth();
                while ($current <= $dateRange['end']) {
                    $monthEnd = $current->copy()->endOfMonth();
                    $labels[] = $current->format('M Y');
                    $newMembersData[] = User::whereBetween('created_at', [$current, $monthEnd])->count();
                    $totalMembersData[] = User::where('created_at', '<=', $monthEnd)->count();
                    $current->addMonth();
                }
                break;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'new_members' => $newMembersData,
                'total_members' => $totalMembersData,
            ],
            'group_by' => $groupBy,
        ]);
    }

    /**
     * Get member demographics (age distribution)
     */
    public function demographics(Request $request)
    {
        // Assuming you have age/date_of_birth field
        // For now, return structure
        return response()->json([
            'success' => true,
            'message' => 'Demographics report structure. Add date_of_birth field for full functionality.',
            'data' => [
                'age_groups' => [
                    ['group' => '18-25', 'count' => 0],
                    ['group' => '26-35', 'count' => 0],
                    ['group' => '36-45', 'count' => 0],
                    ['group' => '46-55', 'count' => 0],
                    ['group' => '56+', 'count' => 0],
                ],
                'gender' => [
                    ['gender' => 'Male', 'count' => 0],
                    ['gender' => 'Female', 'count' => 0],
                ],
            ],
        ]);
    }

    /**
     * Get active vs inactive members report
     */
    public function activeVsInactive(Request $request)
    {
        $active = User::whereNull('deactivated_at')->count();
        $inactive = User::whereNotNull('deactivated_at')->count();
        $total = $active + $inactive;

        $dateRange = $this->getDateRange($request);
        $recentlyDeactivated = User::whereNotNull('deactivated_at')
            ->whereBetween('deactivated_at', [$dateRange['start'], $dateRange['end']])
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'active' => [
                    'count' => $active,
                    'percentage' => $total > 0 ? round(($active / $total) * 100, 2) : 0,
                ],
                'inactive' => [
                    'count' => $inactive,
                    'percentage' => $total > 0 ? round(($inactive / $total) * 100, 2) : 0,
                ],
                'recently_deactivated' => $recentlyDeactivated,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Get new members list
     */
    public function newMembers(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $limit = $request->get('limit', 50);

        $members = User::with('department')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'department' => $user->department?->name ?? 'No Department',
                    'joined_date' => $user->created_at->format('Y-m-d'),
                    'days_since_joined' => $user->created_at->diffInDays(now()),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $members,
            'total' => $members->count(),
        ]);
    }

    /**
     * Get member retention report
     */
    public function retention(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        // Members who joined in the period
        $joinedInPeriod = User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();

        // Members still active from those who joined
        $stillActive = User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNull('deactivated_at')
            ->count();

        $retentionRate = $joinedInPeriod > 0
            ? round(($stillActive / $joinedInPeriod) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'joined_in_period' => $joinedInPeriod,
                'still_active' => $stillActive,
                'deactivated' => $joinedInPeriod - $stillActive,
                'retention_rate' => $retentionRate . '%',
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
            ],
        ]);
    }

    /**
     * Get member status distribution
     */
    public function statusDistribution(Request $request)
    {
        $data = User::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status ?? 'unknown',
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->sum('count'),
        ]);
    }

    /**
     * Export membership report
     */
    public function export(Request $request)
    {
        $dateRange = $this->getDateRange($request);
        $status = $request->get('status', 'all'); // all, active, inactive

        $query = User::with('department');

        if ($status === 'active') {
            $query->whereNull('deactivated_at');
        } elseif ($status === 'inactive') {
            $query->whereNotNull('deactivated_at');
        }

        $members = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Export data prepared',
            'total_records' => $members->count(),
            'data' => $members->map(function ($user) {
                return [
                    'Name' => $user->name,
                    'Email' => $user->email ?? 'N/A',
                    'Phone' => $user->phone,
                    'Role' => $user->role,
                    'Department' => $user->department?->name ?? 'No Department',
                    'Status' => $user->status,
                    'Active' => is_null($user->deactivated_at) ? 'Yes' : 'No',
                    'Joined Date' => $user->created_at->format('Y-m-d'),
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
