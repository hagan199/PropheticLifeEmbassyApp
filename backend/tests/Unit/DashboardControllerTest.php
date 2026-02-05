<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class DashboardControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_stats_endpoint()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/dashboard/stats');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data']);
  }
}
