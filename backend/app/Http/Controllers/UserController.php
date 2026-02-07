<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateTierRequest;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/users",
     *     summary="Get all users",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="role", in="query", description="Filter by role", @OA\Schema(type="string")),
     *     @OA\Parameter(name="is_active", in="query", description="Filter by active status", @OA\Schema(type="boolean")),
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer", example=10)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = \App\Models\User::query()->with('department');

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            if ($isActive) {
                $query->whereNull('deactivated_at');
            } else {
                $query->whereNotNull('deactivated_at');
            }
        }

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Search by name, email, or phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('phone', 'ILIKE', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'total' => $users->total(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ]);
    }

    /**
     * Get all members (for autocomplete/search)
     */
    public function members(Request $request)
    {
        $query = \App\Models\User::query()
            ->select('id', 'name', 'email', 'phone', 'role', 'department_id')
            ->whereNull('deactivated_at');

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Limit for autocomplete
        $limit = $request->get('limit', 100);
        $members = $query->orderBy('name')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $members,
            'total' => $members->count(),
        ]);
    }

    /**
     * Search members by name or phone
     */
    public function searchMembers(Request $request)
    {
        $search = $request->get('q', '');

        $query = \App\Models\User::query()
            ->select('id', 'name', 'email', 'phone', 'role')
            ->whereNull('deactivated_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('phone', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        $members = $query->orderBy('name')->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    /**
     * Create a new user
     */
    public function store(StoreUserRequest $request)
    {
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password ?? 'password123'),
            'role' => $request->role,
            'department_id' => $request->department_id,
            'status' => 'active',
        ]);

        // Log audit
        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'users.create',
            'model_type' => 'User',
            'model_id' => $user->id,
            'changes' => json_encode($user->toArray()),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load('department'),
        ], 201);
    }

    /**
     * Get a single user
     */
    public function show($id)
    {
        $user = \App\Models\User::with('department')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update a user
     */
    public function update(UpdateUserRequest $request, $id)
    {

        // Mock response
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => [
                'id' => $id,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'role' => $request->role,
                'updated_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Deactivate a user
     */
    public function deactivate($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'User deactivated successfully',
        ]);
    }

    /**
     * Reactivate a user
     */
    public function reactivate($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'User reactivated successfully',
        ]);
    }

    /**
     * Get all roles
     */
    public function getRoles()
    {
        $roles = [
            ['id' => 'admin', 'name' => 'Administrator'],
            ['id' => 'pastor', 'name' => 'Pastor'],
            ['id' => 'usher', 'name' => 'Usher'],
            ['id' => 'finance', 'name' => 'Finance Officer'],
            ['id' => 'pr_follow_up', 'name' => 'PR/Follow-up'],
            ['id' => 'department_leader', 'name' => 'Department Leader'],
        ];

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    /**
     * Get permissions for a role
     */
    public function getPermissions($role)
    {
        $permissions = [
            'admin' => ['users.manage', 'attendance.approve', 'finance.manage', 'broadcasts.send', 'audit.view'],
            'pastor' => ['attendance.view', 'members.view', 'reports.view'],
            'usher' => ['attendance.record', 'members.view'],
            'finance' => ['contributions.manage', 'expenses.manage', 'reports.view'],
            'pr_follow_up' => ['visitors.manage', 'followups.manage', 'broadcasts.send'],
            'department_leader' => ['department.manage', 'members.view'],
        ];

        return response()->json([
            'success' => true,
            'role' => $role,
            'permissions' => $permissions[$role] ?? [],
        ]);
    }

    /**
     * Get member tier history
     */
    public function tierHistory($id)
    {
        $history = [
            [
                'id' => 'tier-001',
                'member_id' => $id,
                'old_tier' => 'visitor',
                'new_tier' => 'member',
                'reason' => 'Attended 52+ consecutive weeks',
                'changed_by' => 'System',
                'changed_at' => now()->subMonths(2)->toISOString(),
            ],
            [
                'id' => 'tier-002',
                'member_id' => $id,
                'old_tier' => 'member',
                'new_tier' => 'partnership',
                'reason' => 'Committed to monthly partnership',
                'changed_by' => 'Admin User',
                'changed_at' => now()->subMonth()->toISOString(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Update member tier
     */
    public function updateTier(UpdateTierRequest $request, $id)
    {

        return response()->json([
            'success' => true,
            'message' => 'Member tier updated successfully',
        ]);
    }

    // ========== Helper Methods ==========

    /**
     * Get role name
     */
    private function getRoleName($role)
    {
        $roles = [
            'admin' => 'Administrator',
            'pastor' => 'Pastor',
            'usher' => 'Usher',
            'finance' => 'Finance Officer',
            'pr_follow_up' => 'PR/Follow-up',
            'department_leader' => 'Department Leader',
        ];

        return $roles[$role] ?? $role;
    }

    /**
     * Mock users data
     */
    private function getMockUsers()
    {
        return [
            [
                'id' => 'user-001',
                'name' => 'Admin User',
                'phone' => '+233241234567',
                'email' => 'admin@ple.church',
                'role' => 'admin',
                'role_name' => 'Administrator',
                'is_active' => true,
                'avatar' => 'https://i.pravatar.cc/150?img=12',
                'department' => null,
                'created_at' => now()->subYears(2)->toISOString(),
            ],
            [
                'id' => 'user-002',
                'name' => 'Pastor John Mensah',
                'phone' => '+233241234568',
                'email' => 'pastor@ple.church',
                'role' => 'pastor',
                'role_name' => 'Pastor',
                'is_active' => true,
                'avatar' => 'https://i.pravatar.cc/150?img=13',
                'department' => null,
                'created_at' => now()->subYears(2)->toISOString(),
            ],
            [
                'id' => 'user-003',
                'name' => 'Mary Asante',
                'phone' => '+233241234569',
                'email' => 'mary@ple.church',
                'role' => 'usher',
                'role_name' => 'Usher',
                'is_active' => true,
                'avatar' => 'https://i.pravatar.cc/150?img=45',
                'department' => 'Ushering',
                'created_at' => now()->subYear()->toISOString(),
            ],
            [
                'id' => 'user-004',
                'name' => 'Kwame Osei',
                'phone' => '+233241234570',
                'email' => 'finance@ple.church',
                'role' => 'finance',
                'role_name' => 'Finance Officer',
                'is_active' => true,
                'avatar' => 'https://i.pravatar.cc/150?img=33',
                'department' => null,
                'created_at' => now()->subYear()->toISOString(),
            ],
            [
                'id' => 'user-005',
                'name' => 'Ama Boateng',
                'phone' => '+233241234571',
                'email' => 'pr@ple.church',
                'role' => 'pr_follow_up',
                'role_name' => 'PR/Follow-up',
                'is_active' => true,
                'avatar' => 'https://i.pravatar.cc/150?img=25',
                'department' => 'PR',
                'created_at' => now()->subMonths(8)->toISOString(),
            ],
            [
                'id' => 'user-006',
                'name' => 'Kofi Mensah',
                'phone' => '+233241234572',
                'email' => 'leader@ple.church',
                'role' => 'department_leader',
                'role_name' => 'Department Leader',
                'is_active' => true,
                'avatar' => 'https://i.pravatar.cc/150?img=50',
                'department' => 'Youth Ministry',
                'created_at' => now()->subMonths(6)->toISOString(),
            ],
        ];
    }

    /**
     * Mock members data (for attendance autocomplete)
     */
    private function getMockMembers()
    {
        return [
            ['id' => 'mem-001', 'name' => 'Emmanuel Agyei', 'phone' => '+233241111111', 'tier' => 'partnership'],
            ['id' => 'mem-002', 'name' => 'Grace Addo', 'phone' => '+233241111112', 'tier' => 'member'],
            ['id' => 'mem-003', 'name' => 'Joseph Owusu', 'phone' => '+233241111113', 'tier' => 'partnership'],
            ['id' => 'mem-004', 'name' => 'Comfort Agyeman', 'phone' => '+233241111114', 'tier' => 'member'],
            ['id' => 'mem-005', 'name' => 'Samuel Nkrumah', 'phone' => '+233241111115', 'tier' => 'visitor'],
            ['id' => 'mem-006', 'name' => 'Rebecca Mensah', 'phone' => '+233241111116', 'tier' => 'partnership'],
            ['id' => 'mem-007', 'name' => 'Peter Asare', 'phone' => '+233241111117', 'tier' => 'member'],
            ['id' => 'mem-008', 'name' => 'Esther Boateng', 'phone' => '+233241111118', 'tier' => 'member'],
            ['id' => 'mem-009', 'name' => 'Isaac Frimpong', 'phone' => '+233241111119', 'tier' => 'visitor'],
            ['id' => 'mem-010', 'name' => 'Joyce Mensah', 'phone' => '+233241111120', 'tier' => 'partnership'],
        ];
    }
}
