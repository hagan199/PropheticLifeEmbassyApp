<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class BroadcastControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_index_broadcasts()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/broadcasts');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }
}
