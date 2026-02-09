<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create and authenticate a user with specific role
     *
     * @param string $role
     * @param array $attributes
     * @return User
     */
    protected function createAuthenticatedUser(string $role = 'member', array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => $role,
            'status' => 'active',
        ], $attributes));

        $this->actingAs($user);

        return $user;
    }

    /**
     * Create an admin user and authenticate
     *
     * @param array $attributes
     * @return User
     */
    protected function createAdmin(array $attributes = []): User
    {
        return $this->createAuthenticatedUser('admin', $attributes);
    }

    /**
     * Create a member user and authenticate
     *
     * @param array $attributes
     * @return User
     */
    protected function createMember(array $attributes = []): User
    {
        return $this->createAuthenticatedUser('member', $attributes);
    }

    /**
     * Create an usher user and authenticate
     *
     * @param array $attributes
     * @return User
     */
    protected function createUsher(array $attributes = []): User
    {
        return $this->createAuthenticatedUser('usher', $attributes);
    }

    /**
     * Create a minister user and authenticate
     *
     * @param array $attributes
     * @return User
     */
    protected function createMinister(array $attributes = []): User
    {
        return $this->createAuthenticatedUser('minister', $attributes);
    }

    /**
     * Assert JSON response has success structure
     *
     * @param \Illuminate\Testing\TestResponse $response
     * @param bool $expectedSuccess
     * @return void
     */
    protected function assertSuccessResponse($response, bool $expectedSuccess = true): void
    {
        $response->assertJsonStructure(['success', 'message']);
        $response->assertJson(['success' => $expectedSuccess]);
    }

    /**
     * Assert JSON response has paginated data structure
     *
     * @param \Illuminate\Testing\TestResponse $response
     * @return void
     */
    protected function assertPaginatedResponse($response): void
    {
        $response->assertJsonStructure([
            'success',
            'data',
            'total',
        ]);
    }

    /**
     * Assert validation errors for specific fields
     *
     * @param \Illuminate\Testing\TestResponse $response
     * @param array $fields
     * @return void
     */
    protected function assertValidationErrors($response, array $fields): void
    {
        $response->assertStatus(422);
        $response->assertJsonValidationErrors($fields);
    }

    /**
     * Assert unauthorized response
     *
     * @param \Illuminate\Testing\TestResponse $response
     * @return void
     */
    protected function assertUnauthorized($response): void
    {
        $response->assertStatus(401);
    }

    /**
     * Assert forbidden response
     *
     * @param \Illuminate\Testing\TestResponse $response
     * @return void
     */
    protected function assertForbidden($response): void
    {
        $response->assertStatus(403);
    }

    /**
     * Create a user with token
     *
     * @param array $attributes
     * @return array ['user' => User, 'token' => string]
     */
    protected function createUserWithToken(array $attributes = []): array
    {
        $user = User::factory()->create(array_merge([
            'status' => 'active',
        ], $attributes));

        $token = $user->createToken('test_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Make authenticated JSON request with token
     *
     * @param string $method
     * @param string $uri
     * @param string $token
     * @param array $data
     * @return \Illuminate\Testing\TestResponse
     */
    protected function jsonWithToken(string $method, string $uri, string $token, array $data = [])
    {
        return $this->withToken($token)->json($method, $uri, $data);
    }
}
