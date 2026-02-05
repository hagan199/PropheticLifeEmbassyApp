<?php

namespace App\Http\Controllers;

use App\Models\MinisterAttendance;
use App\Models\MinisterUnitAttendance;
use App\Helpers\AuditHelper;
use Illuminate\Http\Request;

class MinisterUnitAttendanceController extends Controller
{
    public function store(Request $request, MinisterAttendance $attendance)
    {
        $validated = $request->validate([
            'unit_id'        => ['required', 'integer'],
            'present_count'  => ['required', 'integer', 'min:0'],
            'absent_count'   => ['nullable', 'integer', 'min:0'],
            'notes'          => ['nullable', 'string'],
        ]);

        // prevent duplicate unit rows for same service
        $row = MinisterUnitAttendance::updateOrCreate(
            [
                'minister_attendance_id' => $attendance->id,
                'unit_id' => $validated['unit_id'],
            ],
            $validated + ['minister_attendance_id' => $attendance->id]
        );

        // Audit log
        $userId = $request->user()?->id ?? 'system';
        AuditHelper::logCreate(
            (string)$userId,
            'minister_unit_attendance',
            (string)$row->id,
            $row->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Unit attendance saved',
            'data'    => $row,
        ], 201);
    }

    public function index(MinisterAttendance $attendance)
    {
        $rows = MinisterUnitAttendance::where('minister_attendance_id', $attendance->id)
            ->orderBy('unit_id')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $rows,
            'total'   => $rows->count(),
        ]);
    }

    public function update(Request $request, MinisterUnitAttendance $unitAttendance)
    {
        $validated = $request->validate([
            'present_count' => ['sometimes', 'integer', 'min:0'],
            'absent_count'  => ['sometimes', 'nullable', 'integer', 'min:0'],
            'notes'         => ['sometimes', 'nullable', 'string'],
        ]);

        $before = $unitAttendance->toArray();
        $unitAttendance->update($validated);
        $after = $unitAttendance->toArray();

        // Audit log
        $userId = $request->user()?->id ?? 'system';
        AuditHelper::logUpdate(
            (string)$userId,
            'minister_unit_attendance',
            (string)$unitAttendance->id,
            $before,
            $after
        );

        return response()->json([
            'success' => true,
            'message' => 'Unit attendance updated',
            'data'    => $unitAttendance,
        ]);
    }

    public function destroy(MinisterUnitAttendance $unitAttendance)
    {
        $before = $unitAttendance->toArray();
        $unitAttendance->delete();

        // Audit log
        $userId = request()->user()?->id ?? 'system';
        AuditHelper::logDelete(
            (string)$userId,
            'minister_unit_attendance',
            (string)$unitAttendance->id,
            $before
        );

        return response()->json([
            'success' => true,
            'message' => 'Unit attendance deleted',
        ]);
    }
}
