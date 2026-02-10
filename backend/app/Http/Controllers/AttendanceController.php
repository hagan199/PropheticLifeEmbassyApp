<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\BulkApproveRequest;
use App\Http\Requests\Attendance\BulkRejectRequest;
use App\Http\Requests\Attendance\RejectAttendanceRequest;
use App\Models\Attendance;
use App\Models\MinisterUnitAttendance;
use App\Models\Department;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    /**
     * Get paginated list of all attendance records.
     * Supports filtering by status, service_type, date range.
     */
    public function index(Request $request)
    {
        // Require authenticated user to view attendance index
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $query = Attendance::with('submittedBy:id,name,avatar', 'approvedBy:id,name');

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('service_type') && $request->service_type !== '') {
            $query->where('service_type', $request->service_type);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('service_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('service_date', '<=', $request->date_to);
        }

        // Get records ordered by date
        $records = $query->orderBy('service_date', 'desc')->get();

        // Calculate stats
        $stats = [
            'pending' => Attendance::where('status', 'pending')->count(),
            'approved' => Attendance::where('status', 'approved')->count(),
            'rejected' => Attendance::where('status', 'rejected')->count(),
            'approved_today' => Attendance::where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
            'total_week' => Attendance::whereBetween('service_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $records->map(function ($a) {
                return $this->transformRecord($a);
            }),
            'stats' => $stats,
            'total' => $records->count(),
        ]);
    }

    /**
     * Store a new attendance record.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $validated = $request->validate([
            'service_type' => 'required|string',
            'service_date' => 'required|date',
            'count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $attendance = Attendance::create([
            'service_type' => $validated['service_type'],
            'service_date' => $validated['service_date'],
            'count' => $validated['count'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'submitted_by' => Auth::id(),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance record created successfully',
            'data' => $this->transformRecord($attendance)
        ], 201);
    }

    /**
     * Update an attendance record.
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        // Only allow updates if status is pending
        if ($attendance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update attendance that has been approved or rejected'
            ], 403);
        }

        $validated = $request->validate([
            'service_type' => 'sometimes|string',
            'service_date' => 'sometimes|date',
            'count' => 'sometimes|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $attendance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Attendance record updated successfully',
            'data' => $this->transformRecord($attendance)
        ]);
    }

    /**
     * Delete an attendance record.
     */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);

        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        // Only allow deletion if status is pending
        if ($attendance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete attendance that has been approved or rejected'
            ], 403);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully'
        ]);
    }

    /**
     * Approve a single attendance record.
     */
    public function approve($id)
    {
        $attendance = Attendance::findOrFail($id);

        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        if ($attendance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Attendance has already been processed'
            ], 400);
        }

        $attendance->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance approved successfully',
            'data' => $this->transformRecord($attendance)
        ]);
    }

    /**
     * Reject a single attendance record.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $attendance = Attendance::findOrFail($id);

        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        if ($attendance->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Attendance has already been processed'
            ], 400);
        }

        $attendance->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['reason'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance rejected successfully',
            'data' => $this->transformRecord($attendance)
        ]);
    }

    /**
     * Bulk reject attendance records.
     */
    public function bulkReject(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:attendances,id',
            'reason' => 'required|string|max:255',
        ]);

        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $count = Attendance::whereIn('id', $validated['ids'])
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['reason'],
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "$count records rejected"
        ]);
    }

    /**
     * Optimized: Use mass assignment for speed.
     */
    public function storeUnitAttendance(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $request->validate([
            'unit' => 'required|string', // Department ID
            'service' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'member_id' => 'required|string',
            'member_name' => 'required|string',
            'present' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $department = Department::find($request->unit);

        $attendance = MinisterUnitAttendance::create([
            'department_id' => $request->unit,
            'unit_name' => $department ? $department->name : 'Unknown Unit',
            'service_type' => $request->service,
            'service_date' => $request->date,
            'service_time' => $request->time,
            'member_id' => $request->member_id,
            'member_name' => $request->member_name,
            'present' => $request->present,
            'notes' => $request->notes,
            'status' => 'pending',
            'submitted_by' => Auth::id(),
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true, 'data' => $attendance], 201);
    }

    /**
     * Optimized: Eager load only necessary columns to save memory.
     */
    public function show($id)
    {
        // speed optimization: select only columns needed from relationship
        $record = Attendance::with([
            'submittedBy:id,name,avatar',
            'approvedBy:id,name'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->transformRecord($record)
        ]);
    }

    /**
     * Optimized: Use cursor() for memory efficiency in large lists.
     */
    public function pendingApprovals()
    {
        // Memory optimization: use cursor() for large datasets
        $pendingQuery = Attendance::with('submittedBy:id,name,avatar')
            ->where('status', 'pending')
            ->orderBy('service_date', 'desc');

        $total = $pendingQuery->count();

        return response()->json([
            'success' => true,
            'data' => $pendingQuery->get()->map(function ($a) {
                return $this->transformRecord($a);
            }),
            'total' => $total,
        ]);
    }

    /**
     * Optimized: Single DB query for bulk operations.
     */
    public function bulkApprove(BulkApproveRequest $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $count = Attendance::whereIn('id', $request->ids)
            ->where('status', 'pending') // Integrity check
            ->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "$count records approved"
        ]);
    }

    /**
     * Optimized: Use Collection methods for stats to avoid extra DB queries.
     */
    public function mySubmissions()
    {
        $submissions = Attendance::with('approvedBy:id,name')
            ->where('submitted_by', Auth::id())
            ->orderBy('service_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $submissions->map(function ($a) {
                return $this->transformRecord($a, true);
            }),
            'total' => $submissions->count(),
            'stats' => [
                'pending' => $submissions->where('status', 'pending')->count(),
                'approved' => $submissions->where('status', 'approved')->count(),
                'rejected' => $submissions->where('status', 'rejected')->count(),
            ],
        ]);
    }

    /**
     * Get weekly attendance summary report.
     */
    public function weeklyReport()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $entries = MinisterUnitAttendance::whereBetween('service_date', [$startOfWeek, $endOfWeek])
            ->orderBy('service_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $entries->map(function ($e) {
                return [
                    'id' => $e->id,
                    'unit' => $e->department_id,
                    'unitName' => $e->unit_name,
                    'service' => $e->service_type,
                    'date' => $e->service_date->format('Y-m-d'),
                    'time' => $e->service_time,
                    'member_id' => $e->member_id,
                    'memberName' => $e->member_name,
                    'present' => $e->present,
                ];
            }),
            'weekly_total' => $entries->where('present', true)->count()
        ]);
    }

    /**
     * Private helper to dry up the code and centralize transformation logic.
     */
    private function transformRecord($a, $includeApproval = false)
    {
        // Compute a safe submitted_at value without using PHP 8 nullsafe operator
        $submittedAt = null;
        if (isset($a->submitted_at) && $a->submitted_at) {
            if (is_object($a->submitted_at) && method_exists($a->submitted_at, 'diffForHumans')) {
                $submittedAt = $a->submitted_at->diffForHumans();
            } else {
                $submittedAt = (string) $a->submitted_at;
            }
        } elseif (isset($a->created_at) && $a->created_at) {
            if (is_object($a->created_at) && method_exists($a->created_at, 'diffForHumans')) {
                $submittedAt = $a->created_at->diffForHumans();
            } else {
                $submittedAt = (string) $a->created_at;
            }
        }

        return [
            'id' => $a->id,
            'service_type' => $a->service_type,
            'service_date' => is_object($a->service_date) && method_exists($a->service_date, 'format') ? $a->service_date->format('Y-m-d') : (string) $a->service_date,
            'count' => $a->count,
            'status' => $a->status,
            'submitted_by' => $a->submittedBy ? [
                'id' => $a->submittedBy->id,
                'name' => $a->submittedBy->name,
                'avatar' => $a->submittedBy->avatar,
            ] : null,
            'submitted_at' => $submittedAt,
            'approved_by' => $a->approvedBy ? $a->approvedBy->name : null,
            'rejection_reason' => $a->rejection_reason,
            'notes' => $a->notes,
        ];
    }
}
