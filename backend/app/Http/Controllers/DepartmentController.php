<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Requests\Department\AddMemberRequest;

class DepartmentController extends Controller
{
    /**
     * Get all departments
     */
    public function index()
    {
        $departments = $this->getMockDepartments();

        return response()->json([
            'success' => true,
            'data' => $departments,
            'total' => count($departments),
        ]);
    }

    /**
     * Create department
     */
    public function store(StoreDepartmentRequest $request)
    {

        $newDepartment = [
            'id' => 'dept-' . rand(100, 999),
            'name' => $request->name,
            'description' => $request->description,
            'leader_id' => $request->leader_id,
            'leader_name' => $request->leader_id ? 'Leader Name' : null,
            'member_count' => 0,
            'created_at' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $newDepartment,
        ], 201);
    }

    /**
     * Get single department
     */
    public function show($id)
    {
        $departments = $this->getMockDepartments();
        $department = collect($departments)->firstWhere('id', $id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }

    /**
     * Update department
     */
    public function update(UpdateDepartmentRequest $request, $id)
    {

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
        ]);
    }

    /**
     * Delete department
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully',
        ]);
    }

    /**
     * Get department members
     */
    public function members($id)
    {
        $members = [
            ['id' => 'mem-001', 'name' => 'Emmanuel Agyei', 'phone' => '+233241111111', 'role' => 'Member'],
            ['id' => 'mem-002', 'name' => 'Grace Addo', 'phone' => '+233241111112', 'role' => 'Assistant'],
            ['id' => 'mem-007', 'name' => 'Peter Asare', 'phone' => '+233241111117', 'role' => 'Member'],
        ];

        return response()->json([
            'success' => true,
            'data' => $members,
            'total' => count($members),
        ]);
    }

    /**
     * Add member to department
     */
    public function addMember(AddMemberRequest $request, $id)
    {

        return response()->json([
            'success' => true,
            'message' => 'Member added to department',
        ]);
    }

    /**
     * Remove member from department
     */
    public function removeMember($id, $memberId)
    {
        return response()->json([
            'success' => true,
            'message' => 'Member removed from department',
        ]);
    }

    /**
     * Get my department (Department Leader)
     */
    public function myDepartment()
    {
        $department = [
            'id' => 'dept-001',
            'name' => 'Youth Ministry',
            'description' => 'Ministry for young people aged 13-25',
            'leader_id' => 'user-006',
            'leader_name' => 'Kofi Mensah',
            'member_count' => 24,
            'members' => [
                ['id' => 'mem-001', 'name' => 'Emmanuel Agyei', 'phone' => '+233241111111'],
                ['id' => 'mem-002', 'name' => 'Grace Addo', 'phone' => '+233241111112'],
            ],
            'recent_activities' => [
                ['date' => now()->subDays(2)->toDateString(), 'activity' => 'Youth worship night', 'attendance' => 18],
                ['date' => now()->subWeek()->toDateString(), 'activity' => 'Bible study', 'attendance' => 15],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }

    // ========== Helper Methods ==========

    /**
     * Mock departments data
     */
    private function getMockDepartments()
    {
        return [
            [
                'id' => 'dept-001',
                'name' => 'Youth Ministry',
                'description' => 'Ministry for young people aged 13-25',
                'leader_id' => 'user-006',
                'leader_name' => 'Kofi Mensah',
                'member_count' => 24,
                'created_at' => now()->subYear()->toISOString(),
            ],
            [
                'id' => 'dept-002',
                'name' => 'Ushering',
                'description' => 'Welcome and seating coordination',
                'leader_id' => 'user-003',
                'leader_name' => 'Mary Asante',
                'member_count' => 12,
                'created_at' => now()->subYears(2)->toISOString(),
            ],
            [
                'id' => 'dept-003',
                'name' => 'Choir',
                'description' => 'Worship and praise team',
                'leader_id' => null,
                'leader_name' => null,
                'member_count' => 18,
                'created_at' => now()->subYears(2)->toISOString(),
            ],
            [
                'id' => 'dept-004',
                'name' => 'Media',
                'description' => 'Sound, video, and social media',
                'leader_id' => null,
                'leader_name' => null,
                'member_count' => 8,
                'created_at' => now()->subMonths(8)->toISOString(),
            ],
            [
                'id' => 'dept-005',
                'name' => 'PR',
                'description' => 'Public relations and follow-up',
                'leader_id' => 'user-005',
                'leader_name' => 'Ama Boateng',
                'member_count' => 6,
                'created_at' => now()->subMonths(6)->toISOString(),
            ],
        ];
    }
}
