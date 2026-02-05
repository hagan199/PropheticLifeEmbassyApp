<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Visitor\StoreVisitorRequest;
use App\Http\Requests\Visitor\UpdateVisitorRequest;

class VisitorController extends Controller
{
    /**
     * Get all visitors
     */
    public function index(Request $request)
    {
        $visitors = $this->getMockVisitors();

        // Filter by status
        if ($request->has('status')) {
            $visitors = array_filter($visitors, fn($v) => $v['status'] === $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => array_values($visitors),
            'total' => count($visitors),
        ]);
    }

    /**
     * Register a new visitor
     */
    public function store(StoreVisitorRequest $request)
    {

        $newVisitor = [
            'id' => 'vis-' . rand(1000, 9999),
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'source' => $request->source,
            'ministry_interests' => $request->ministry_interests ?? [],
            'status' => 'not_contacted',
            'notes' => $request->notes,
            'first_visit_date' => now()->toDateString(),
            'created_at' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Visitor registered successfully',
            'data' => $newVisitor,
        ], 201);
    }

    /**
     * Get single visitor
     */
    public function show($id)
    {
        $visitors = $this->getMockVisitors();
        $visitor = collect($visitors)->firstWhere('id', $id);

        if (!$visitor) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $visitor,
        ]);
    }

    /**
     * Update visitor
     */
    public function update(UpdateVisitorRequest $request, $id)
    {

        return response()->json([
            'success' => true,
            'message' => 'Visitor updated successfully',
        ]);
    }

    /**
     * Delete visitor
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Visitor deleted successfully',
        ]);
    }

    /**
     * Get follow-ups for a visitor
     */
    public function followUps($id)
    {
        $followUps = [
            [
                'id' => 'fu-001',
                'visitor_id' => $id,
                'contact_method' => 'whatsapp',
                'notes' => 'Welcomed and shared church programs',
                'next_follow_up_date' => now()->addWeek()->toDateString(),
                'contacted_by' => 'Ama Boateng',
                'created_at' => now()->subDays(3)->toISOString(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $followUps,
        ]);
    }

    // ========== Helper Methods ==========

    /**
     * Mock visitors data
     */
    private function getMockVisitors()
    {
        return [
            [
                'id' => 'vis-001',
                'name' => 'Abena Mensah',
                'phone' => '+233241222001',
                'email' => 'abena@example.com',
                'source' => 'friend',
                'ministry_interests' => ['choir', 'youth'],
                'status' => 'contacted',
                'first_visit_date' => now()->subDays(5)->toDateString(),
                'notes' => 'Interested in joining choir',
                'created_at' => now()->subDays(5)->toISOString(),
            ],
            [
                'id' => 'vis-002',
                'name' => 'Kwabena Osei',
                'phone' => '+233241222002',
                'email' => null,
                'source' => 'social_media',
                'ministry_interests' => ['media'],
                'status' => 'not_contacted',
                'first_visit_date' => now()->subDays(2)->toDateString(),
                'notes' => null,
                'created_at' => now()->subDays(2)->toISOString(),
            ],
            [
                'id' => 'vis-003',
                'name' => 'Akua Asante',
                'phone' => '+233241222003',
                'email' => 'akua@example.com',
                'source' => 'walk_in',
                'ministry_interests' => [],
                'status' => 'engaged',
                'first_visit_date' => now()->subDays(15)->toDateString(),
                'notes' => 'Attending regularly',
                'created_at' => now()->subDays(15)->toISOString(),
            ],
            [
                'id' => 'vis-004',
                'name' => 'Yaw Frimpong',
                'phone' => '+233241222004',
                'email' => 'yaw@example.com',
                'source' => 'friend',
                'ministry_interests' => ['ushering'],
                'status' => 'converted',
                'first_visit_date' => now()->subMonths(2)->toDateString(),
                'notes' => 'Now a member',
                'created_at' => now()->subMonths(2)->toISOString(),
            ],
        ];
    }
}
