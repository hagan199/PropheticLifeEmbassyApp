<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Broadcast\StoreBroadcastRequest;
use App\Models\Broadcast;
use App\Models\BroadcastDelivery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class BroadcastController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        
        // Speed Tip: Use middleware for authorization to stop unauthorized 
        // requests before they hit the controller logic.
        $this->middleware('can:view-broadcasts')->only(['index', 'show', 'deliveries']);
        $this->middleware('can:create-broadcasts')->only('store');
        $this->middleware('can:delete-broadcasts')->only('destroy');
    }

    /**
     * Get all broadcasts (Optimized with Pagination)
     */
    public function index(Request $request): JsonResponse
    {
        // Only select columns you need to save memory
        $broadcasts = Broadcast::select(['id', 'message', 'recipient_type', 'status', 'sent_by', 'created_at'])
            ->latest()
            ->paginate($request->query('per_page', 15)); // Use pagination instead of get()

        return response()->json([
            'success' => true,
            'data' => $broadcasts->items(),
            'meta' => [
                'total' => $broadcasts->total(),
                'current_page' => $broadcasts->currentPage(),
                'last_page' => $broadcasts->lastPage(),
            ],
        ]);
    }

    /**
     * Create and send broadcast (Optimized with Mass Assignment)
     */
    public function store(StoreBroadcastRequest $request): JsonResponse
    {
        $user = Auth::user();

        // Speed Tip: Use mass assignment with validated data
        $broadcast = Broadcast::create(array_merge($request->validated(), [
            'message' => strip_tags($request->message),
            'status' => $request->schedule_at ? 'scheduled' : 'queued',
            'sent_by' => $user->name ?? 'Unknown',
            'sent_by_id' => $user->id ?? null,
        ]));

        return response()->json([
            'success' => true,
            'message' => $request->schedule_at ? 'Scheduled' : 'Queued',
            'data' => $broadcast,
        ], 201);
    }

    /**
     * Get single broadcast (Optimized with findOrFail)
     */
    public function show($id): JsonResponse
    {
        // findOrFail is faster for error handling than manual null checks
        $broadcast = Broadcast::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $broadcast,
        ]);
    }

    /**
     * Get broadcast deliveries (Optimized with Cursor Pagination)
     */
    public function deliveries($id, Request $request): JsonResponse
    {
        // For large logs, simplePaginate or cursorPaginate is significantly faster
        $deliveries = BroadcastDelivery::where('broadcast_id', $id)
            ->simplePaginate($request->query('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $deliveries->items(),
            'meta' => [
                'total' => $deliveries->total(),
                'current_page' => $deliveries->currentPage(),
                'last_page' => $deliveries->lastPage(),
            ],
        ]);
    }
}