<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Visitor;

class FollowUpControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_index_followups()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/follow-ups');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }

  public function test_store_followup()
  {
    $user = User::factory()->create();
    $visitor = Visitor::factory()->create();
    $this->actingAs($user);
    $payload = [
      'visitor_id' => $visitor->id,
      'contact_method' => 'phone',
      'notes' => 'Followed up',
      'status' => 'completed',
      'next_follow_up_date' => now()->addWeek()->toDateString(),
    ];
    $response = $this->postJson('/api/follow-ups', $payload);
    $response->assertStatus(201);
  }
}
