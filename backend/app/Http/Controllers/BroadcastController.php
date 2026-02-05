<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Broadcast\StoreBroadcastRequest;
use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BroadcastController extends Controller
{
    public function __construct()
    {
        // Require authentication for all broadcast actions
        $this->middleware('auth:api');
    }
    /**
     * Get all broadcasts
     */
    public function index(Request $request)
    {
        // Only allow users with 'view-broadcasts' permission
        if (Gate::denies('view-broadcasts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $broadcasts = Broadcast::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $broadcasts,
            'total' => $broadcasts->count(),
        ]);
    }

    /**
     * Create and send broadcast
     */
    public function store(StoreBroadcastRequest $request)
    {
        if (Gate::denies('create-broadcasts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $user = Auth::user();
        $broadcast = Broadcast::create([
            'message' => strip_tags($request->message),
            'recipient_type' => $request->recipient_type,
            'department_id' => $request->department_id,
            'channel' => $request->channel,
            'status' => $request->schedule_at ? 'scheduled' : 'queued',
            'schedule_at' => $request->schedule_at,
            'sent_by' => $user ? $user->name : 'Unknown',
            'sent_by_id' => $user ? $user->id : null,
        ]);
        return response()->json([
            'success' => true,
            'message' => $request->schedule_at ? 'Broadcast scheduled successfully' : 'Broadcast queued for sending',
            'data' => $broadcast,
        ], 201);
    }

    /**
     * Get single broadcast
     */
    public function show($id)
    {
        if (Gate::denies('view-broadcasts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $broadcast = Broadcast::find($id);
        if (!$broadcast) {
            return response()->json([
                'success' => false,
                'message' => 'Broadcast not found',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $broadcast,
        ]);
    }

    /**
     * Delete broadcast
     */
    public function destroy($id)
    {
        if (Gate::denies('delete-broadcasts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $broadcast = Broadcast::find($id);
        if ($broadcast) {
            $broadcast->delete();
        }
        return response()->json([
            'success' => true,
            'message' => 'Broadcast deleted successfully',
        ]);
    }

    /**
     * Get broadcast deliveries
     */
    public function deliveries($id)
    {
        if (Gate::denies('view-broadcasts')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $deliveries = BroadcastDelivery::where('broadcast_id', $id)->get();
        return response()->json([
            'success' => true,
            'data' => $deliveries,
            'total' => $deliveries->count(),
        ]);
    }

    // ...existing code...
}
