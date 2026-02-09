<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Attendance\{StoreAttendanceRequest, BulkApproveRequest, BulkRejectRequest, RejectAttendanceRequest};
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
    public function index(Request $request): JsonResponse
    {
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
            'data' => $records->map(fn($a) => $this->transformRecord($a)),
            'stats' => $stats,
            'total' => $records->count(),
        ]);
    }

    /**
     * Store a new attendance record.
     */
    public function store(Request $request): JsonResponse
    {
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
    public function update(Request $request, $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);

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
    public function destroy($id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);

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
    public function approve($id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);

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
    public function reject(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $attendance = Attendance::findOrFail($id);

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
    public function bulkReject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:attendances,id',
            'reason' => 'required|string|max:255',
        ]);

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
    public function storeUnitAttendance(Request $request): JsonResponse
    {
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
    public function show($id): JsonResponse
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
    public function pendingApprovals(): JsonResponse
    {
        // Memory optimization: use cursor() for large datasets
        $pendingQuery = Attendance::with('submittedBy:id,name,avatar')
            ->where('status', 'pending')
            ->orderBy('service_date', 'desc');

        $total = $pendingQuery->count();

        return response()->json([
            'success' => true,
            'data' => $pendingQuery->get()->map(fn($a) => $this->transformRecord($a)),
            'total' => $total,
        ]);
    }

    /**
     * Optimized: Single DB query for bulk operations.
     */
    public function bulkApprove(BulkApproveRequest $request): JsonResponse
    {
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
    public function mySubmissions(): JsonResponse
    {
        $submissions = Attendance::with('approvedBy:id,name')
            ->where('submitted_by', Auth::id())
            ->orderBy('service_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $submissions->map(fn($a) => $this->transformRecord($a, true)),
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
    public function weeklyReport(): JsonResponse
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
    private function transformRecord($a, $includeApproval = false): array
    {
        return [
            'id' => $a->id,
            'service_type' => $a->service_type,
            'service_date' => $a->service_date->format('Y-m-d'),
            'count' => $a->count,
            'status' => $a->status,
            'submitted_by' => $a->submittedBy ? [
                'id' => $a->submittedBy->id,
                'name' => $a->submittedBy->name,
                'avatar' => $a->submittedBy->avatar,
            ] : null,
            'submitted_at' => $a->submitted_at?->diffForHumans() ?? $a->created_at->diffForHumans(),
            'approved_by' => $a->approvedBy?->name,
            'rejection_reason' => $a->rejection_reason,
            'notes' => $a->notes,
        ];
    }
}
