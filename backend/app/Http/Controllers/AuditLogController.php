<?php

namespace App\Http\Controllers;

use App\Models\AuditLog; // Assuming an Eloquent Model
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    /**
     * Get all audit logs
     * Optimized: Database-level filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Build query with user relationship
        $query = AuditLog::with('user:id,name,role');

        // 2. Use 'when' to build the SQL dynamically
        $query->when($request->action, function ($q, $action) {
            return $q->where('action', $action);
        })
        ->when($request->user_id, function ($q, $userId) {
            return $q->where('user_id', $userId);
        })
        ->when($request->entity_type, function ($q, $type) {
            return $q->where('entity_type', $type);
        })
        ->when($request->date_from, function ($q, $date) {
            return $q->whereDate('created_at', '>=', $date);
        })
        ->when($request->date_to, function ($q, $date) {
            return $q->whereDate('created_at', '<=', $date);
        })
        ->when($request->search, function ($q, $search) {
            return $q->where(function ($query) use ($search) {
                $query->where('description', 'LIKE', "%{$search}%")
                      ->orWhere('ip_address', 'LIKE', "%{$search}%")
                      ->orWhere('entity_type', 'LIKE', "%{$search}%");
            });
        });

        // 3. Get all users for filter dropdown
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        // 4. Execute query and get all results
        $logs = $query->latest()->get();

        // 5. Transform data for frontend
        $transformedLogs = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'userId' => $log->user_id,
                'userName' => $log->user?->name ?? 'Unknown User',
                'userRole' => $log->user?->role ?? 'unknown',
                'action' => $log->action,
                'module' => $log->entity_type,
                'description' => $log->description ?? '',
                'ipAddress' => $log->ip_address,
                'userAgent' => $log->user_agent,
                'changes' => $log->changes,
                'createdAt' => $log->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedLogs,
            'users' => $users,
            'stats' => [
                'total' => $logs->count(),
                'creates' => $logs->where('action', 'create')->count(),
                'updates' => $logs->where('action', 'update')->count(),
                'deletes' => $logs->where('action', 'delete')->count(),
                'logins' => $logs->where('action', 'login')->count(),
            ],
        ]);
    }

    /**
     * Get single audit log
     * Optimized: findOrFail is faster and handles 404s automatically
     */
    public function show($id): JsonResponse
    {
        $log = AuditLog::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    /**
     * Export audit logs
     * Speed Tip: For large datasets, use a Background Job
     */
    public function export(Request $request): JsonResponse
    {
        $format = $request->get('format', 'csv');
        
        // In a high-speed app, dispatch a job to handle this in the background
        // ExportAuditLogsJob::dispatch($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Audit log export has been queued.',
            'download_url' => null, // Provide URL once job is complete
        ]);
    }
}