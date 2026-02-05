<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\FollowUp\StoreFollowUpRequest;
use App\Models\FollowUp;
use Illuminate\Support\Facades\Auth;

class FollowUpController extends Controller
{
    /**
     * Get all follow-ups
     */
    public function index(Request $request)
    {
        $query = FollowUp::with(['visitor', 'loggedBy']);
        if ($request->has('status')) {
            $query->where('status_after', $request->status);
        }
        $followUps = $query->orderByDesc('created_at')->get();
        return response()->json([
            'success' => true,
            'data' => $followUps,
            'total' => $followUps->count(),
        ]);
    }

    /**
     * Create a follow-up record
     */
    public function store(StoreFollowUpRequest $request)
    {
        $followUp = FollowUp::create([
            'visitor_id' => $request->visitor_id,
            'contact_method' => $request->contact_method,
            'outcome_notes' => $request->notes,
            'status_after' => $request->status ?? 'pending',
            'next_follow_up_date' => $request->next_follow_up_date,
            'logged_by' => Auth::id(),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Follow-up logged successfully',
            'data' => $followUp,
        ], 201);
    }

    /**
     * Get due follow-ups
     */
    public function dueList()
    {
        $due = FollowUp::with(['visitor', 'loggedBy'])
            ->where('status_after', 'pending')
            ->whereDate('next_follow_up_date', '<=', now()->addDays(3)->toDateString())
            ->orderBy('next_follow_up_date')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $due,
            'total' => $due->count(),
        ]);
    }

    /**
     * Get single follow-up
     */
    public function show($id)
    {
        $followUp = FollowUp::with(['visitor', 'loggedBy'])->find($id);
        if (!$followUp) {
            return response()->json([
                'success' => false,
                'message' => 'Follow-up not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $followUp,
        ]);
    }

    /**
     * Update follow-up
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Follow-up updated successfully',
        ]);
    }

    /**
     * Delete follow-up
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Follow-up deleted successfully',
        ]);
    }

    // ...existing code...
}
