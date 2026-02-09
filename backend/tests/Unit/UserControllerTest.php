<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index returns all users
     */
    public function test_index_returns_all_users()
    {
        $admin = $this->createAdmin();

        User::factory()->count(5)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'total',
                'current_page',
                'last_page',
            ]);

        $this->assertTrue($response->json('total') >= 5);
    }

    /**
     * Test index filters by role
     */
    public function test_index_filters_by_role()
    {
        $admin = $this->createAdmin();

        User::factory()->count(3)->create(['role' => 'usher']);
        User::factory()->count(2)->create(['role' => 'minister']);

        $response = $this->getJson('/api/users?role=usher');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test index filters by active status
     */
    public function test_index_filters_by_active_status()
    {
        $admin = $this->createAdmin();

        User::factory()->count(3)->create(['deactivated_at' => null]);
        User::factory()->count(2)->create(['deactivated_at' => now()]);

        $response = $this->getJson('/api/users?is_active=true');

        $response->assertStatus(200);
        // Total should be active users + admin
        $this->assertTrue($response->json('total') >= 3);
    }

    /**
     * Test index filters by department
     */
    public function test_index_filters_by_department()
    {
        $admin = $this->createAdmin();

        $department = Department::factory()->create();

        User::factory()->count(3)->create(['department_id' => $department->id]);
        User::factory()->count(2)->create(['department_id' => null]);

        $response = $this->getJson("/api/users?department_id={$department->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test index searches by name, email, or phone
     */
    public function test_index_searches_users()
    {
        $admin = $this->createAdmin();

        User::factory()->create(['name' => 'John Smith', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $response = $this->getJson('/api/users?search=John');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
    }

    /**
     * Test index pagination
     */
    public function test_index_pagination()
    {
        $admin = $this->createAdmin();

        User::factory()->count(25)->create();

        $response = $this->getJson('/api/users?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'last_page',
            ]);

        $this->assertTrue($response->json('last_page') > 1);
    }

    /**
     * Test store creates user successfully
     */
    public function test_store_creates_user_successfully()
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '1234567890',
            'password' => 'password123',
            'role' => 'member',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'role',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '1234567890',
            'role' => 'member',
            'status' => 'active',
        ]);
    }

    /**
     * Test store validation requires name
     */
    public function test_store_validation_requires_name()
    {
        $admin = $this->createAdmin();

        $payload = [
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'role' => 'member',
        ];

        $response = $this->postJson('/api/users', $payload);

        $this->assertValidationErrors($response, ['name']);
    }

    /**
     * Test store validation requires unique email
     */
    public function test_store_validation_requires_unique_email()
    {
        $admin = $this->createAdmin();

        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $payload = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'phone' => '1234567890',
            'role' => 'member',
        ];

        $response = $this->postJson('/api/users', $payload);

        $this->assertValidationErrors($response, ['email']);
    }

    /**
     * Test store validation requires unique phone
     */
    public function test_store_validation_requires_unique_phone()
    {
        $admin = $this->createAdmin();

        $existingUser = User::factory()->create([
            'phone' => '1234567890',
        ]);

        $payload = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'phone' => '1234567890',
            'role' => 'member',
        ];

        $response = $this->postJson('/api/users', $payload);

        $this->assertValidationErrors($response, ['phone']);
    }

    /**
     * Test store validation requires valid role
     */
    public function test_store_validation_requires_valid_role()
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'phone' => '1234567890',
            'role' => 'invalid_role',
        ];

        $response = $this->postJson('/api/users', $payload);

        $this->assertValidationErrors($response, ['role']);
    }

    /**
     * Test show returns single user
     */
    public function test_show_returns_single_user()
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ]);
    }

    /**
     * Test show returns 404 for non-existent user
     */
    public function test_show_returns_404_for_non_existent_user()
    {
        $admin = $this->createAdmin();

        $response = $this->getJson('/api/users/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found',
            ]);
    }

    /**
     * Test update modifies user successfully
     */
    public function test_update_modifies_user_successfully()
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/users/{$user->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test update returns 404 for non-existent user
     */
    public function test_update_returns_404_for_non_existent_user()
    {
        $admin = $this->createAdmin();

        $response = $this->putJson('/api/users/99999', [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found',
            ]);
    }

    /**
     * Test update validation prevents duplicate email
     */
    public function test_update_validation_prevents_duplicate_email()
    {
        $admin = $this->createAdmin();

        $user1 = User::factory()->create(['email' => 'existing@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $response = $this->putJson("/api/users/{$user2->id}", [
            'email' => 'existing@example.com',
        ]);

        $this->assertValidationErrors($response, ['email']);
    }

    /**
     * Test delete removes user successfully
     */
    public function test_delete_removes_user_successfully()
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent user
     */
    public function test_delete_returns_404_for_non_existent_user()
    {
        $admin = $this->createAdmin();

        $response = $this->deleteJson('/api/users/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found',
            ]);
    }

    /**
     * Test members endpoint returns active members only
     */
    public function test_members_endpoint_returns_active_members_only()
    {
        $admin = $this->createAdmin();

        User::factory()->count(5)->create(['deactivated_at' => null]);
        User::factory()->count(2)->create(['deactivated_at' => now()]);

        $response = $this->getJson('/api/users/members');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Should return only active members + admin
        $this->assertTrue($response->json('total') >= 5);
    }

    /**
     * Test members endpoint filters by role
     */
    public function test_members_endpoint_filters_by_role()
    {
        $admin = $this->createAdmin();

        User::factory()->count(3)->create(['role' => 'usher', 'deactivated_at' => null]);
        User::factory()->count(2)->create(['role' => 'member', 'deactivated_at' => null]);

        $response = $this->getJson('/api/users/members?role=usher');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test search members finds by name or phone
     */
    public function test_search_members_finds_by_name_or_phone()
    {
        $admin = $this->createAdmin();

        User::factory()->create([
            'name' => 'John Doe',
            'phone' => '1234567890',
            'deactivated_at' => null,
        ]);
        User::factory()->create([
            'name' => 'Jane Smith',
            'phone' => '9876543210',
            'deactivated_at' => null,
        ]);

        $response = $this->getJson('/api/users/search?q=John');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));
    }

    /**
     * Test deactivate user endpoint
     */
    public function test_deactivate_user()
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create(['deactivated_at' => null]);

        $response = $this->postJson("/api/users/{$user->id}/deactivate");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);

        $user->refresh();
        $this->assertNotNull($user->deactivated_at);
    }

    /**
     * Test activate user endpoint
     */
    public function test_activate_user()
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create(['deactivated_at' => now()]);

        $response = $this->postJson("/api/users/{$user->id}/activate");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertNull($user->deactivated_at);
    }

    /**
     * Test reset password endpoint
     */
    public function test_admin_can_reset_user_password()
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/reset-password", [
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /**
     * Test unauthenticated user cannot access user endpoints
     */
    public function test_unauthenticated_user_cannot_access_users()
    {
        $response = $this->getJson('/api/users');
        $this->assertUnauthorized($response);

        $response = $this->postJson('/api/users', []);
        $this->assertUnauthorized($response);
    }

    /**
     * Test non-admin cannot create users
     */
    public function test_non_admin_cannot_create_users()
    {
        $member = $this->createMember();

        $payload = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '1234567890',
            'role' => 'member',
        ];

        $response = $this->postJson('/api/users', $payload);

        $this->assertForbidden($response);
    }

    /**
     * Test update tier endpoint
     */
    public function test_update_user_tier()
    {
        $admin = $this->createAdmin();

        $user = User::factory()->create(['tier' => 'bronze']);

        $response = $this->putJson("/api/users/{$user->id}/tier", [
            'tier' => 'gold',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'tier' => 'gold',
        ]);
    }

    /**
     * Test bulk operations endpoint
     */
    public function test_bulk_delete_users()
    {
        $admin = $this->createAdmin();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $response = $this->deleteJson('/api/users/bulk', [
            'ids' => [$user1->id, $user2->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
        $this->assertDatabaseHas('users', ['id' => $user3->id]);
    }
}
