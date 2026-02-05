<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class DepartmentControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_index_departments()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/departments');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }

  public function test_store_department()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $payload = [
      'name' => 'Music',
      'description' => 'Music department',
      'leader_id' => $user->id,
    ];
    $response = $this->postJson('/api/departments', $payload);
    $response->assertStatus(201);
  }
}
