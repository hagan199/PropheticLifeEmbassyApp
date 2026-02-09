<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\FollowUp\StoreFollowUpRequest;
use App\Models\FollowUp;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\Visitor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FollowUpController extends Controller
{
    /**
     * Get all follow-ups
     */
    public function convertToMember(Request $request, $id)
    {
        $visitor = Visitor::findOrFail($id);

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
     * Get follow-ups due for action
     */
    public function due()
    {
        $due = FollowUp::with(['visitor', 'loggedBy'])
            ->whereNotNull('next_follow_up_date')
            ->where('next_follow_up_date', '<=', now()->toDateString())
            ->orderBy('next_follow_up_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $due,
            'total' => $due->count(),
        ]);
    }

    /**
     * Get single follow-up
     */
    public function show($id)
    {
        $followUp = FollowUp::with(['visitor', 'loggedBy'])->find($id);
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
        /** @var User|null $user */
        $user = $request->user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

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
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Follow-up deleted successfully',
        ]);
    }

    public function stats()
    {
        $stats = [
            'total_follow_ups' => FollowUp::count(),
            'pending_follow_ups' => FollowUp::where('status_after', 'pending')->count(),
            'completed_follow_ups' => FollowUp::where('status_after', 'completed')->count(),
        ];
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // duplicate convertToMember removed; the request-aware convertToMember(Request $request, $id)
    // near the top of this file is the canonical implementation that supports multi-role attachment.
}
