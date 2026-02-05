<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_index_users()
  {
    User::factory()->count(3)->create();
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/users');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }

  public function test_store_user()
  {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);
    $payload = [
      'name' => 'Jane Doe',
      'email' => 'jane@example.com',
      'password' => 'password',
      'role' => 'usher',
    ];
    $response = $this->postJson('/api/users', $payload);
    $response->assertStatus(201);
  }
}
