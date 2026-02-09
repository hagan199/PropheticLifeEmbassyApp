<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Visitor\StoreVisitorRequest;
use App\Http\Requests\Visitor\UpdateVisitorRequest;
use App\Models\Visitor;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VisitorController extends Controller
{
    /**
     * Get all visitors
     */
    public function index(Request $request)
    {
        // Require authenticated user to view visitors
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $query = Visitor::query()->with('creator:id,name');

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('occupation', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $paginated = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'visitor_count' => Visitor::where('category', 'Visitor')->count(),
            'partner_count' => Visitor::where('category', 'Partner')->count(),
            'member_count' => Visitor::where('category', 'Wants to be a Member')->count(),
        ]);
    }

    /**
     * Register a new visitor
     */
    public function store(Request $request)
    {
        try {
            /** @var User|null $user */
            $user = $request->user();
            if (!$user || !$user->hasAnyRole(['admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'category' => 'required|in:Visitor,Partner,Wants to be a Member',
                'service_type' => 'nullable|string|max:255',
                'occupation' => 'nullable|string|max:255',
                'date' => 'required|date',
            ]);

            $visitor = Visitor::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'category' => $request->category,
                'service_type' => $request->service_type,
                'occupation' => $request->occupation,
                'first_visit_date' => $request->date,
                'created_by' => Auth::id() ?? User::where('email', 'admin@church.com')->first()?->id, // Fallback for debugging dev
                'status' => 'not_contacted',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visitor registered successfully',
                'data' => $visitor,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get single visitor
     */
    public function show($id)
    {
        $visitor = Visitor::with(['creator:id,name', 'followUps'])->find($id);

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
    public function update(Request $request, $id)
    {
        try {
            /** @var User|null $user */
            $user = $request->user();
            if (!$user || !$user->hasAnyRole(['admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden'
                ], 403);
            }

            $visitor = Visitor::find($id);

            if (!$visitor) {
                return response()->json(['success' => false, 'message' => 'Visitor not found'], 404);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|required|string|max:20',
                'category' => 'sometimes|required|in:Visitor,Partner,Wants to be a Member',
                'service_type' => 'sometimes|nullable|string|max:255',
                'occupation' => 'sometimes|nullable|string|max:255',
                'date' => 'sometimes|required|date',
            ]);

            $data = $request->only(['name', 'phone', 'category', 'service_type', 'occupation']);

            if ($request->has('date')) {
                $data['first_visit_date'] = $request->date;
            }

            $visitor->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Visitor updated successfully',
                'data' => $visitor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete visitor
     */
    public function destroy($id)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $visitor = Visitor::find($id);
        if (!$visitor) {
            return response()->json(['success' => false, 'message' => 'Visitor not found'], 404);
        }

        $visitor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Visitor deleted successfully',
        ]);
    }


    public function convertToMember(Request $request, $id)
    {
        /** @var User|null $authUser */
        $authUser = Auth::user();
        if (!$authUser || !$authUser->hasAnyRole(['admin'])) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $visitor = Visitor::find($id);
        if (!$visitor) {
            return response()->json(['success' => false, 'message' => 'Visitor not found'], 404);
        }

        // Check for existing user by phone or email
        $existingUser = null;
        if ($visitor->phone) {
            $existingUser = User::where('phone', $visitor->phone)->first();
        }
        if (!$existingUser && $visitor->email) {
            $existingUser = User::where('email', $visitor->email)->first();
        }
        // Determine roles to attach (array or comma-separated string). Default to ['member']
        $rolesInput = $request->input('roles', ['member']);
        if (is_string($rolesInput)) {
            $rolesInput = array_filter(array_map('trim', explode(',', $rolesInput)));
        }
        if (!is_array($rolesInput) || empty($rolesInput)) {
            $rolesInput = ['member'];
        }

        // Resolve or create Role records and collect IDs
        $roleIds = [];
        foreach ($rolesInput as $rName) {
            $rName = trim((string) $rName);
            if ($rName === '') continue;
            $role = Role::firstOrCreate([
                'name' => $rName
            ], [
                'display_name' => ucfirst($rName),
                'description' => ucfirst($rName) . ' role',
                'is_system' => false,
            ]);
            $roleIds[] = $role->id;
        }

        if ($existingUser) {
            // Attach requested roles to existing user
            if (!empty($roleIds)) {
                $existingUser->roles()->syncWithoutDetaching($roleIds);
            }

            $visitor->category = 'Member';
            $visitor->converted_user_id = $existingUser->id;
            $visitor->converted_at = now();
            $visitor->save();

            AuditLog::logAction(Auth::id(), 'visitor.convert', 'Visitor', $visitor->id, ['converted_to' => $existingUser->id, 'roles' => $rolesInput], 'Marked visitor as member (existing user)');

            return response()->json([
                'success' => true,
                'message' => 'Visitor marked as member; existing user found',
                'user' => $existingUser,
                'visitor' => $visitor,
            ]);
        }

        // Create new user
        $newUser = User::create([
            'name' => $visitor->name,
            'email' => $visitor->email,
            'phone' => $visitor->phone,
            'password' => bcrypt(Str::random(12)),
            'status' => 'active',
        ]);

        if (!empty($roleIds)) {
            $newUser->roles()->syncWithoutDetaching($roleIds);
        }

        $visitor->category = 'Member';
        $visitor->converted_user_id = $newUser->id;
        $visitor->converted_at = now();
        $visitor->save();

        AuditLog::logAction(Auth::id(), 'visitor.convert', 'Visitor', $visitor->id, ['converted_to' => $newUser->id, 'roles' => $rolesInput], 'Converted visitor to new user');

        return response()->json([
            'success' => true,
            'message' => 'Visitor converted to member successfully',
            'user' => $newUser,
            'visitor' => $visitor,
        ]);
    }
    /**
     * Get follow-ups for a visitor
     */
    public function followUps($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return response()->json(['success' => false, 'message' => 'Visitor not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $visitor->followUps,
        ]);
    }
}
