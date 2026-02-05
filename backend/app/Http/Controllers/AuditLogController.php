<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Get all audit logs
     */
    public function index(Request $request)
    {
        $logs = $this->getMockAuditLogs();

        // Filter by action
        if ($request->has('action')) {
            $logs = array_filter($logs, fn($l) => $l['action'] === $request->action);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $logs = array_filter($logs, fn($l) => $l['user_id'] === $request->user_id);
        }

        // Filter by entity type
        if ($request->has('entity_type')) {
            $logs = array_filter($logs, fn($l) => $l['entity_type'] === $request->entity_type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $logs = array_filter($logs, fn($l) => $l['created_at'] >= $request->from_date);
        }

        return response()->json([
            'success' => true,
            'data' => array_values($logs),
            'total' => count($logs),
        ]);
    }

    /**
     * Get single audit log
     */
    public function show($id)
    {
        $logs = $this->getMockAuditLogs();
        $log = collect($logs)->firstWhere('id', $id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Audit log not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        return response()->json([
            'success' => true,
            'message' => 'Audit log export initiated',
            'download_url' => '/exports/audit-logs-' . now()->format('Y-m-d') . '.' . $format,
        ]);
    }

    // ========== Helper Methods ==========

    /**
     * Mock audit logs data
     */
    private function getMockAuditLogs()
    {
        return [
            [
                'id' => 'audit-001',
                'user_id' => 'user-001',
                'user_name' => 'Admin User',
                'action' => 'create',
                'entity_type' => 'user',
                'entity_id' => 'user-007',
                'changes' => [
                    'before' => null,
                    'after' => [
                        'name' => 'New User',
                        'phone' => '+233241234573',
                        'role' => 'usher',
                    ],
                ],
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subHours(2)->toISOString(),
            ],
            [
                'id' => 'audit-002',
                'user_id' => 'user-001',
                'user_name' => 'Admin User',
                'action' => 'approve',
                'entity_type' => 'attendance',
                'entity_id' => 'att-001',
                'changes' => [
                    'before' => ['status' => 'pending'],
                    'after' => ['status' => 'approved'],
                ],
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subHours(5)->toISOString(),
            ],
            [
                'id' => 'audit-003',
                'user_id' => 'user-004',
                'user_name' => 'Kwame Osei',
                'action' => 'create',
                'entity_type' => 'contribution',
                'entity_id' => 'cont-001',
                'changes' => [
                    'before' => null,
                    'after' => [
                        'member_id' => 'mem-001',
                        'amount' => 500,
                        'payment_method' => 'mobile_money',
                    ],
                ],
                'ip_address' => '192.168.1.5',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subDays(1)->toISOString(),
            ],
            [
                'id' => 'audit-004',
                'user_id' => 'user-001',
                'user_name' => 'Admin User',
                'action' => 'update',
                'entity_type' => 'user',
                'entity_id' => 'user-003',
                'changes' => [
                    'before' => ['role' => 'usher'],
                    'after' => ['role' => 'department_leader'],
                ],
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subDays(2)->toISOString(),
            ],
            [
                'id' => 'audit-005',
                'user_id' => 'user-001',
                'user_name' => 'Admin User',
                'action' => 'delete',
                'entity_type' => 'expense',
                'entity_id' => 'exp-999',
                'changes' => [
                    'before' => [
                        'amount' => 200,
                        'description' => 'Test expense',
                    ],
                    'after' => null,
                ],
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subDays(3)->toISOString(),
            ],
            [
                'id' => 'audit-006',
                'user_id' => 'user-005',
                'user_name' => 'Ama Boateng',
                'action' => 'create',
                'entity_type' => 'visitor',
                'entity_id' => 'vis-001',
                'changes' => [
                    'before' => null,
                    'after' => [
                        'name' => 'Abena Mensah',
                        'phone' => '+233241222001',
                        'source' => 'friend',
                    ],
                ],
                'ip_address' => '192.168.1.8',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subDays(5)->toISOString(),
            ],
            [
                'id' => 'audit-007',
                'user_id' => 'user-001',
                'user_name' => 'Admin User',
                'action' => 'send',
                'entity_type' => 'broadcast',
                'entity_id' => 'bc-001',
                'changes' => [
                    'before' => null,
                    'after' => [
                        'message' => 'Join us for our special worship night...',
                        'recipient_type' => 'all',
                        'channel' => 'both',
                    ],
                ],
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0',
                'created_at' => now()->subDays(3)->toISOString(),
            ],
        ];
    }
}
