<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\BulkApproveRequest;
use App\Http\Requests\Attendance\BulkRejectRequest;
use App\Http\Requests\Attendance\RejectAttendanceRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
    /**
     * Record ministry unit attendance (unit, service, date, time, member, present/absent)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeUnitAttendance(Request $request)
    {
        $data = $request->validate([
            'unit' => 'required|string',
            'service' => 'required|string',
            'date' => 'required|date',
            'time' => 'required',
            'member_id' => 'required|integer',
            'member_name' => 'required|string',
            'present' => 'required|boolean',
        ]);

        $attendance = Attendance::create([
            'service_type' => $data['service'],
            'service_date' => $data['date'],
            'service_time' => $data['time'],
            'unit' => $data['unit'],
            'member_id' => $data['member_id'],
            'member_name' => $data['member_name'],
            'present' => $data['present'],
            'status' => 'pending',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Unit attendance recorded',
            'data' => $attendance,
        ], 201);
    }
{
    /**
     * Get all attendance records
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['submittedBy', 'approvedBy']);

        // Filter by service type
        if ($request->has('service_type') && $request->service_type) {
            $query->where('service_type', $request->service_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('service_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('service_date', '<=', $request->date_to);
        }

        $attendance = $query->orderBy('service_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $attendance->map(function ($a) {
                return [
                    'id' => $a->id,
                    'service_type' => $a->service_type,
                    'service_date' => $a->service_date->format('Y-m-d'),
                    'count' => $a->count,
                    'status' => $a->status,
                    'notes' => $a->notes,
                    'submitted_by' => [
                        'id' => $a->submittedBy?->id,
                        'name' => $a->submittedBy?->name ?? 'Unknown',
                        'avatar' => $a->submittedBy?->avatar,
                    ],
                    'submitted_at' => $a->submitted_at ? $a->submitted_at->diffForHumans() : $a->created_at->diffForHumans(),
                    'approved_by' => $a->approvedBy?->name,
                    'approved_at' => $a->approved_at?->diffForHumans(),
                    'rejection_reason' => $a->rejection_reason,
                    'created_at' => $a->created_at->toISOString(),
                ];
            }),
            'total' => $attendance->count(),
            'stats' => [
                'pending' => Attendance::where('status', 'pending')->count(),
                'approved' => Attendance::where('status', 'approved')->count(),
                'rejected' => Attendance::where('status', 'rejected')->count(),
                'approved_today' => Attendance::where('status', 'approved')
                    ->whereDate('approved_at', Carbon::today())
                    ->count(),
                'total_week' => Attendance::whereBetween('service_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])->sum('count'),
            ],
        ]);
    }

    /**
     * Record attendance
     */
    public function store(StoreAttendanceRequest $request)
    {
        $attendance = Attendance::create([
            'service_type' => $request->service_type,
            'service_date' => $request->service_date,
            'count' => $request->count ?? 0,
            'status' => 'pending',
            'notes' => $request->notes,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'data' => $attendance,
        ], 201);
    }

    /**
     * Get single attendance record
     */
    public function show($id)
    {
        $record = Attendance::with(['submittedBy', 'approvedBy'])->find($id);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $record->id,
                'service_type' => $record->service_type,
                'service_date' => $record->service_date->format('Y-m-d'),
                'count' => $record->count,
                'status' => $record->status,
                'notes' => $record->notes,
                'submitted_by' => [
                    'id' => $record->submittedBy?->id,
                    'name' => $record->submittedBy?->name ?? 'Unknown',
                    'avatar' => $record->submittedBy?->avatar,
                ],
                'submitted_at' => $record->submitted_at?->diffForHumans(),
                'approved_by' => $record->approvedBy?->name,
                'rejection_reason' => $record->rejection_reason,
            ],
        ]);
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        $attendance->update($request->only(['service_type', 'service_date', 'count', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => $attendance,
        ]);
    }

    /**
     * Delete attendance record
     */
    public function destroy($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance deleted successfully',
        ]);
    }

    /**
     * Get pending approvals (Admin)
     */
    public function pendingApprovals()
    {
        $pending = Attendance::with(['submittedBy'])
            ->where('status', 'pending')
            ->orderBy('service_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pending->map(function ($a) {
                return [
                    'id' => $a->id,
                    'service_type' => $a->service_type,
                    'service_date' => $a->service_date->format('Y-m-d'),
                    'count' => $a->count,
                    'status' => $a->status,
                    'notes' => $a->notes,
                    'submitted_by' => [
                        'id' => $a->submittedBy?->id,
                        'name' => $a->submittedBy?->name ?? 'Unknown',
                        'avatar' => $a->submittedBy?->avatar,
                    ],
                    'submitted_at' => $a->submitted_at ? $a->submitted_at->diffForHumans() : $a->created_at->diffForHumans(),
                ];
            }),
            'total' => $pending->count(),
        ]);
    }

    /**
     * Bulk approve attendance
     */
    public function bulkApprove(BulkApproveRequest $request)
    {
        Attendance::whereIn('id', $request->ids)->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' records approved successfully',
        ]);
    }

    /**
     * Bulk reject attendance
     */
    public function bulkReject(BulkRejectRequest $request)
    {
        Attendance::whereIn('id', $request->ids)->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' records rejected',
        ]);
    }

    /**
     * Approve single attendance
     */
    public function approve($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        $attendance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance approved',
        ]);
    }

    /**
     * Reject single attendance
     */
    public function reject(RejectAttendanceRequest $request, $id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Record not found',
            ], 404);
        }

        $attendance->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance rejected',
        ]);
    }

    /**
     * Get my submissions (Usher)
     */
    public function mySubmissions()
    {
        $submissions = Attendance::with(['approvedBy'])
            ->where('submitted_by', auth()->id())
            ->orderBy('service_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $submissions->map(function ($a) {
                return [
                    'id' => $a->id,
                    'service_type' => $a->service_type,
                    'service_date' => $a->service_date->format('Y-m-d'),
                    'count' => $a->count,
                    'status' => $a->status,
                    'notes' => $a->notes,
                    'approved_by' => $a->approvedBy?->name,
                    'approved_at' => $a->approved_at?->diffForHumans(),
                    'rejection_reason' => $a->rejection_reason,
                    'created_at' => $a->created_at->toISOString(),
                ];
            }),
            'total' => $submissions->count(),
            'stats' => [
                'pending' => $submissions->where('status', 'pending')->count(),
                'approved' => $submissions->where('status', 'approved')->count(),
                'rejected' => $submissions->where('status', 'rejected')->count(),
            ],
        ]);
    }
}
