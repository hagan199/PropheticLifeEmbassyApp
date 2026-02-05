<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class VisitorControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_index_visitors()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/visitors');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }

  public function test_store_visitor()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $payload = [
      'name' => 'Visitor Name',
      'phone' => '1234567890',
      'email' => 'visitor@example.com',
    ];
    $response = $this->postJson('/api/visitors', $payload);
    $response->assertStatus(201);
  }
}
