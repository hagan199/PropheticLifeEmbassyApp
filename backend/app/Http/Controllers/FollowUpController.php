<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\FollowUp\StoreFollowUpRequest;

class FollowUpController extends Controller
{
    /**
     * Get all follow-ups
     */
    public function index(Request $request)
    {
        $followUps = $this->getMockFollowUps();

        // Filter by status
        if ($request->has('status')) {
            $followUps = array_filter($followUps, fn($f) => $f['status'] === $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => array_values($followUps),
            'total' => count($followUps),
        ]);
    }

    /**
     * Create a follow-up record
     */
    public function store(StoreFollowUpRequest $request)
    {

        $newFollowUp = [
            'id' => 'fu-' . rand(1000, 9999),
            'visitor_id' => $request->visitor_id,
            'visitor_name' => 'Visitor Name', // Would fetch from DB
            'contact_method' => $request->contact_method,
            'notes' => $request->notes,
            'next_follow_up_date' => $request->next_follow_up_date,
            'contacted_by' => 'Current User',
            'contacted_by_id' => 'user-005',
            'status' => 'completed',
            'created_at' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Follow-up logged successfully',
            'data' => $newFollowUp,
        ], 201);
    }

    /**
     * Get due follow-ups
     */
    public function dueList()
    {
        $followUps = $this->getMockFollowUps();
        $due = array_filter($followUps, function ($f) {
            return $f['next_follow_up_date'] &&
                   $f['next_follow_up_date'] <= now()->addDays(3)->toDateString() &&
                   $f['status'] === 'pending';
        });

        return response()->json([
            'success' => true,
            'data' => array_values($due),
            'total' => count($due),
        ]);
    }

    /**
     * Get single follow-up
     */
    public function show($id)
    {
        $followUps = $this->getMockFollowUps();
        $followUp = collect($followUps)->firstWhere('id', $id);

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

    // ========== Helper Methods ==========

    /**
     * Mock follow-ups data
     */
    private function getMockFollowUps()
    {
        return [
            [
                'id' => 'fu-001',
                'visitor_id' => 'vis-001',
                'visitor_name' => 'Abena Mensah',
                'visitor_phone' => '+233241222001',
                'contact_method' => 'whatsapp',
                'notes' => 'Welcomed visitor, shared church programs',
                'next_follow_up_date' => now()->addDays(2)->toDateString(),
                'contacted_by' => 'Ama Boateng',
                'contacted_by_id' => 'user-005',
                'status' => 'pending',
                'created_at' => now()->subDays(3)->toISOString(),
            ],
            [
                'id' => 'fu-002',
                'visitor_id' => 'vis-002',
                'visitor_name' => 'Kwabena Osei',
                'visitor_phone' => '+233241222002',
                'contact_method' => 'call',
                'notes' => 'No answer, will try again tomorrow',
                'next_follow_up_date' => now()->addDay()->toDateString(),
                'contacted_by' => 'Ama Boateng',
                'contacted_by_id' => 'user-005',
                'status' => 'pending',
                'created_at' => now()->subDays(1)->toISOString(),
            ],
            [
                'id' => 'fu-003',
                'visitor_id' => 'vis-003',
                'visitor_name' => 'Akua Asante',
                'visitor_phone' => '+233241222003',
                'contact_method' => 'in_person',
                'notes' => 'Met after service, interested in joining youth ministry',
                'next_follow_up_date' => now()->addWeek()->toDateString(),
                'contacted_by' => 'Ama Boateng',
                'contacted_by_id' => 'user-005',
                'status' => 'completed',
                'created_at' => now()->subDays(7)->toISOString(),
            ],
            [
                'id' => 'fu-004',
                'visitor_id' => 'vis-001',
                'visitor_name' => 'Abena Mensah',
                'visitor_phone' => '+233241222001',
                'contact_method' => 'sms',
                'notes' => 'Sent welcome message with service times',
                'next_follow_up_date' => now()->toDateString(),
                'contacted_by' => 'Ama Boateng',
                'contacted_by_id' => 'user-005',
                'status' => 'pending',
                'created_at' => now()->subDays(10)->toISOString(),
            ],
        ];
    }
}
