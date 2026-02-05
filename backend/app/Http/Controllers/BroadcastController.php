<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Broadcast\StoreBroadcastRequest;

class BroadcastController extends Controller
{
    /**
     * Get all broadcasts
     */
    public function index(Request $request)
    {
        $broadcasts = $this->getMockBroadcasts();

        return response()->json([
            'success' => true,
            'data' => $broadcasts,
            'total' => count($broadcasts),
        ]);
    }

    /**
     * Create and send broadcast
     */
    public function store(StoreBroadcastRequest $request)
    {

        $newBroadcast = [
            'id' => 'bc-' . rand(1000, 9999),
            'message' => $request->message,
            'recipient_type' => $request->recipient_type,
            'department_id' => $request->department_id,
            'department_name' => $request->department_id ? 'Department Name' : null,
            'channel' => $request->channel,
            'status' => $request->schedule_at ? 'scheduled' : 'queued',
            'schedule_at' => $request->schedule_at,
            'sent_by' => 'Current User',
            'sent_by_id' => 'user-001',
            'recipient_count' => rand(20, 100),
            'delivered_count' => 0,
            'failed_count' => 0,
            'created_at' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => $request->schedule_at ? 'Broadcast scheduled successfully' : 'Broadcast queued for sending',
            'data' => $newBroadcast,
        ], 201);
    }

    /**
     * Get single broadcast
     */
    public function show($id)
    {
        $broadcasts = $this->getMockBroadcasts();
        $broadcast = collect($broadcasts)->firstWhere('id', $id);

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
        $deliveries = [
            [
                'id' => 'del-001',
                'broadcast_id' => $id,
                'recipient_name' => 'Emmanuel Agyei',
                'recipient_phone' => '+233241111111',
                'channel' => 'whatsapp',
                'status' => 'delivered',
                'delivered_at' => now()->subHours(2)->toISOString(),
            ],
            [
                'id' => 'del-002',
                'broadcast_id' => $id,
                'recipient_name' => 'Grace Addo',
                'recipient_phone' => '+233241111112',
                'channel' => 'sms',
                'status' => 'delivered',
                'delivered_at' => now()->subHours(2)->toISOString(),
            ],
            [
                'id' => 'del-003',
                'broadcast_id' => $id,
                'recipient_name' => 'Joseph Owusu',
                'recipient_phone' => '+233241111113',
                'channel' => 'whatsapp',
                'status' => 'failed',
                'error_message' => 'Invalid phone number',
                'delivered_at' => null,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $deliveries,
            'total' => count($deliveries),
        ]);
    }

    // ========== Helper Methods ==========

    /**
     * Mock broadcasts data
     */
    private function getMockBroadcasts()
    {
        return [
            [
                'id' => 'bc-001',
                'message' => 'Join us for our special worship night this Friday at 7pm! See you there! 🙏',
                'recipient_type' => 'all',
                'department_id' => null,
                'department_name' => null,
                'channel' => 'both',
                'status' => 'completed',
                'schedule_at' => null,
                'sent_by' => 'Admin User',
                'sent_by_id' => 'user-001',
                'recipient_count' => 120,
                'delivered_count' => 115,
                'failed_count' => 5,
                'created_at' => now()->subDays(3)->toISOString(),
                'completed_at' => now()->subDays(3)->addHours(1)->toISOString(),
            ],
            [
                'id' => 'bc-002',
                'message' => 'Thank you partners for your faithful giving! Your support means the world to us.',
                'recipient_type' => 'partners',
                'department_id' => null,
                'department_name' => null,
                'channel' => 'whatsapp',
                'status' => 'completed',
                'schedule_at' => null,
                'sent_by' => 'Admin User',
                'sent_by_id' => 'user-001',
                'recipient_count' => 35,
                'delivered_count' => 34,
                'failed_count' => 1,
                'created_at' => now()->subWeek()->toISOString(),
                'completed_at' => now()->subWeek()->addMinutes(30)->toISOString(),
            ],
            [
                'id' => 'bc-003',
                'message' => 'Youth Ministry meeting tomorrow at 5pm. Please confirm your attendance.',
                'recipient_type' => 'department',
                'department_id' => 'dept-001',
                'department_name' => 'Youth Ministry',
                'channel' => 'sms',
                'status' => 'scheduled',
                'schedule_at' => now()->addDay()->toISOString(),
                'sent_by' => 'Kofi Mensah',
                'sent_by_id' => 'user-006',
                'recipient_count' => 24,
                'delivered_count' => 0,
                'failed_count' => 0,
                'created_at' => now()->subHours(5)->toISOString(),
                'completed_at' => null,
            ],
        ];
    }
}
