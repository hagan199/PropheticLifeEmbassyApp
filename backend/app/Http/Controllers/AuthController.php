<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Login with phone and password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
            'remember' => 'sometimes|boolean',
        ]);

        $phone = $request->input('phone');
        $password = $request->input('password');
        $remember = $request->input('remember', false);

        // Find user by phone number
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return ResponseHelper::error('Invalid credentials', null, 401);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return ResponseHelper::error('Your account has been deactivated. Please contact administrator.', null, 403);
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            return ResponseHelper::error('Invalid credentials', null, 401);
        }

        // Create token for user with expiration
        // If remember is true, token expires in 30 days, otherwise 2 hours
        $tokenName = $remember ? 'auth_token_remember' : 'auth_token';
        $expiresAt = $remember ? now()->addDays(30) : now()->addHours(2);

        $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        return ResponseHelper::success([
            'user' => $this->formatUserData($user),
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
        ], 'Login successful');
    }

    /**
     * Logout current user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return ResponseHelper::success(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return ResponseHelper::success([
            'user' => $this->formatUserData($user),
        ]);
    }

    /**
     * Request password reset
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            // Don't reveal if user exists
            return ResponseHelper::success([
                'message' => 'If this phone number is registered, you will receive a reset code.',
            ]);
        }

        // Generate reset code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $resetToken = Str::random(64);

        Cache::put("password_reset_code_{$user->id}", $code, now()->addMinutes(15));
        Cache::put("password_reset_token_{$resetToken}", $user->id, now()->addMinutes(15));

        // In production, send SMS here
        Log::info("Password reset code for {$user->phone}: {$code}");

        return ResponseHelper::success([
            'reset_token' => $resetToken,
            'message' => 'If this phone number is registered, you will receive a reset code.',
        ]);
    }

    /**
     * Reset password with code
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'reset_token' => 'required|string',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $userId = Cache::get("password_reset_token_{$request->reset_token}");

        if (!$userId) {
            return ResponseHelper::error('Reset session expired. Please try again.', null, 401);
        }

        $storedCode = Cache::get("password_reset_code_{$userId}");

        if (!$storedCode || $storedCode !== $request->code) {
            return ResponseHelper::error('Invalid reset code', null, 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return ResponseHelper::error('User not found', null, 404);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Clear reset cache
        Cache::forget("password_reset_code_{$userId}");
        Cache::forget("password_reset_token_{$request->reset_token}");

        // Revoke all existing tokens
        $user->tokens()->delete();

        return ResponseHelper::success(null, 'Password reset successfully. Please login with your new password.');
    }

    /**
     * Change password for authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return ResponseHelper::error('Current password is incorrect', null, 401);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Revoke all other tokens except current
        $currentTokenId = $request->user()->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return ResponseHelper::success(null, 'Password changed successfully');
    }

    /**
     * Update authenticated user profile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
        ]);

        $user = $request->user();

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        $user->save();

        return ResponseHelper::success([
            'user' => $this->formatUserData($user),
        ], 'Profile updated successfully');
    }

    /**
     * Format user data for response
     *
     * @param User $user
     * @return array
     */
    private function formatUserData(User $user): array
    {
        $user->load(['department', 'roles']);

        // Get roles array (supports both old single role and new multiple roles)
        $userRoles = [];
        if ($user->roles && $user->roles->count() > 0) {
            $userRoles = $user->roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                ];
            })->toArray();
        } elseif ($user->role) {
            // Fallback to old single role field
            $userRoles = [[
                'id' => $user->role,
                'name' => $user->role,
                'display_name' => $user->role_name,
            ]];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role, // Keep for backward compatibility
            'role_name' => $user->role_name, // Keep for backward compatibility
            'roles' => $userRoles, // New: array of roles
            'department' => $user->department ? [
                'id' => $user->department->id,
                'name' => $user->department->name,
            ] : null,
            'avatar' => $user->avatar,
            'status' => $user->status,
            'has_2fa' => $user->has_2fa,
        ];
    }
}
