<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login with valid credentials
     */
    public function test_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'role',
                        'status',
                    ],
                    'token',
                ],
            ]);

        $this->assertEquals(true, $response->json('success'));
        $this->assertEquals('Login successful', $response->json('message'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test login fails with invalid phone number
     */
    public function test_login_fails_with_invalid_phone()
    {
        $response = $this->postJson('/api/auth/login', [
            'phone' => 'nonexistent',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test login fails with incorrect password
     */
    public function test_login_fails_with_incorrect_password()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'password' => Hash::make('correctpassword'),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '1234567890',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    /**
     * Test login fails for inactive user
     */
    public function test_login_fails_for_inactive_user()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '1234567890',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact administrator.',
            ]);
    }

    /**
     * Test login validation requires phone and password
     */
    public function test_login_validation_requires_fields()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone', 'password']);
    }

    /**
     * Test logout successfully revokes token
     */
    public function test_logout_revokes_current_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);

        // Verify token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * Test getting authenticated user profile
     */
    public function test_get_authenticated_user_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'role',
                        'status',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ]);
    }

    /**
     * Test forgot password generates reset code
     */
    public function test_forgot_password_generates_reset_code()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'phone' => '1234567890',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'reset_token',
                    'message',
                ],
            ]);

        $resetToken = $response->json('data.reset_token');
        $this->assertNotEmpty($resetToken);

        // Verify cache contains reset data
        $this->assertNotNull(Cache::get("password_reset_token_{$resetToken}"));
        $this->assertNotNull(Cache::get("password_reset_code_{$user->id}"));
    }

    /**
     * Test forgot password doesn't reveal if user exists
     */
    public function test_forgot_password_doesnt_reveal_user_existence()
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'phone' => 'nonexistent',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'If this phone number is registered, you will receive a reset code.',
                ],
            ]);
    }

    /**
     * Test password reset with valid code
     */
    public function test_reset_password_with_valid_code()
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
            'password' => Hash::make('oldpassword'),
        ]);

        $resetToken = 'test_reset_token';
        $resetCode = '123456';

        Cache::put("password_reset_token_{$resetToken}", $user->id, now()->addMinutes(15));
        Cache::put("password_reset_code_{$user->id}", $resetCode, now()->addMinutes(15));

        $response = $this->postJson('/api/auth/reset-password', [
            'reset_token' => $resetToken,
            'code' => $resetCode,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset successfully. Please login with your new password.',
            ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        // Verify cache was cleared
        $this->assertNull(Cache::get("password_reset_token_{$resetToken}"));
        $this->assertNull(Cache::get("password_reset_code_{$user->id}"));

        // Verify all tokens were revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * Test password reset fails with invalid code
     */
    public function test_reset_password_fails_with_invalid_code()
    {
        $user = User::factory()->create();
        $resetToken = 'test_reset_token';

        Cache::put("password_reset_token_{$resetToken}", $user->id, now()->addMinutes(15));
        Cache::put("password_reset_code_{$user->id}", '123456', now()->addMinutes(15));

        $response = $this->postJson('/api/auth/reset-password', [
            'reset_token' => $resetToken,
            'code' => '654321',  // Wrong code
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid reset code',
            ]);
    }

    /**
     * Test password reset fails with expired token
     */
    public function test_reset_password_fails_with_expired_token()
    {
        $response = $this->postJson('/api/auth/reset-password', [
            'reset_token' => 'expired_token',
            'code' => '123456',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Reset session expired. Please try again.',
            ]);
    }

    /**
     * Test password reset validation
     */
    public function test_reset_password_validation()
    {
        $response = $this->postJson('/api/auth/reset-password', [
            'reset_token' => 'token',
            'code' => '123',  // Too short
            'password' => 'short',  // Too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'password']);
    }

    /**
     * Test change password with correct current password
     */
    public function test_change_password_with_correct_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('currentpassword'),
        ]);
        $this->actingAs($user);

        $response = $this->postJson('/api/auth/change-password', [
            'current_password' => 'currentpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /**
     * Test change password fails with incorrect current password
     */
    public function test_change_password_fails_with_incorrect_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('currentpassword'),
        ]);
        $this->actingAs($user);

        $response = $this->postJson('/api/auth/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect',
            ]);

        // Verify password was NOT changed
        $user->refresh();
        $this->assertTrue(Hash::check('currentpassword', $user->password));
    }

    /**
     * Test change password validation
     */
    public function test_change_password_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/auth/change-password', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password', 'password']);
    }

    /**
     * Test update profile successfully
     */
    public function test_update_profile_successfully()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $this->actingAs($user);

        $response = $this->putJson('/api/auth/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => [
                        'name' => 'New Name',
                        'email' => 'new@example.com',
                    ],
                ],
            ]);

        // Verify database was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    /**
     * Test update profile with partial data
     */
    public function test_update_profile_with_partial_data()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);
        $this->actingAs($user);

        $response = $this->putJson('/api/auth/profile', [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'old@example.com',  // Unchanged
        ]);
    }

    /**
     * Test update profile validation
     */
    public function test_update_profile_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->putJson('/api/auth/profile', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test unauthenticated user cannot access protected routes
     */
    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/auth/user');
        $response->assertStatus(401);

        $response = $this->postJson('/api/auth/logout');
        $response->assertStatus(401);

        $response = $this->postJson('/api/auth/change-password', []);
        $response->assertStatus(401);

        $response = $this->putJson('/api/auth/profile', []);
        $response->assertStatus(401);
    }
}
