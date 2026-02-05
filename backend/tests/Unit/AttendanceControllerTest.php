<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Attendance;
use App\Models\User;

class AttendanceControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_store_attendance()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $payload = [
      'service_type' => 'Sunday',
      'service_date' => now()->toDateString(),
      'service_time' => '10:00',
      'unit' => 'Usher',
      'member_id' => 1,
      'member_name' => 'John Doe',
      'present' => true,
    ];
    $response = $this->postJson('/api/attendance', $payload);
    $response->assertStatus(201);
  }

  public function test_index_attendance()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    Attendance::factory()->count(2)->create();
    $response = $this->getJson('/api/attendance');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }
}
