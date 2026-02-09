<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Requests\Department\AddMemberRequest;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    /**
     * Get all departments
     */
    public function index()
    {
        $departments = Department::withCount('members as member_count')
            ->with('leader:id,name,avatar')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $departments,
            'total' => $departments->count(),
        ]);
    }

    /**
     * Create department
     */
    public function store(StoreDepartmentRequest $request)
    {
        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'leader_id' => $request->leader_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department,
        ], 201);
    }

    /**
     * Get single department
     */
    public function show($id)
    {
        $department = Department::withCount('members as member_count')
            ->with(['leader:id,name,avatar,phone,email', 'members:id,name,phone,avatar,role,department_id'])
            ->find($id);

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
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['success' => false, 'message' => 'Department not found'], 404);
        }

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'leader_id' => $request->leader_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department
        ]);
    }

    /**
     * Delete department
     */
    public function destroy($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['success' => false, 'message' => 'Department not found'], 404);
        }

        // Reset members department_id
        User::where('department_id', $id)->update(['department_id' => null]);
        
        $department->delete();

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
        $department = Department::find($id);
        
        if (!$department) {
            return response()->json(['success' => false, 'message' => 'Department not found'], 404);
        }

        $members = $department->members()->select('id', 'name', 'phone', 'role', 'avatar', 'email')->get();

        return response()->json([
            'success' => true,
            'data' => $members,
            'total' => $members->count(),
        ]);
    }

    /**
     * Add member to department
     */
    public function addMember(AddMemberRequest $request, $id)
    {
        $department = Department::find($id);
        if (!$department) {
            return response()->json(['success' => false, 'message' => 'Department not found'], 404);
        }

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $user->department_id = $department->id;
        if ($request->has('role')) {
            // Check if role is valid enum logic or just string? 
            // User model role enum: ['admin', 'pastor', 'usher', 'finance', 'pr_follow_up', 'department_leader']
            // But departments usually have internal roles like 'Member', 'Assistant'. 
            // The User model doesn't seem to have 'department_role'. 
            // Assuming 'role' in request means system role, or maybe we need a pivot table for department roles?
            // The User migration only shows 'department_id'.
            // For now, let's just update department_id. 
            // If request->role matches system role, update it too.
            // But usually department member role (Leader vs Member) isn't the same as system Role.
            // The 'Department' model leader_id defined the leader.
            // I'll stick to updating department_id.
        }
        $user->save();

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
        $user = User::where('id', $memberId)->where('department_id', $id)->first();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Member not found in this department'], 404);
        }

        $user->department_id = null;
        $user->save();

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
        $user = Auth::user();
        if (!$user->department_id) {
             return response()->json(['success' => false, 'message' => 'You do not belong to any department'], 404);
        }

        $department = Department::withCount('members as member_count')
            ->with(['leader', 'members' => function($q) {
                $q->limit(5); // Recent members preview
            }])
            ->find($user->department_id);

        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }
}
