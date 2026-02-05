<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\MinisterAttendance;
use App\Models\MinisterUnitAttendance;
use App\Models\User;

class MinisterUnitAttendanceTest extends TestCase
{
  use RefreshDatabase;

  public function test_store_creates_or_updates_unit_attendance()
  {
    $user = User::factory()->create();
    $attendance = MinisterAttendance::factory()->create();
    $this->actingAs($user);

    $payload = [
      'unit_id' => 1,
      'present_count' => 10,
      'absent_count' => 2,
      'notes' => 'First entry',
    ];

    $response = $this->postJson(route('minister-unit-attendance.store', $attendance->id), $payload);
    $response->assertStatus(201)
      ->assertJsonFragment(['unit_id' => 1, 'present_count' => 10, 'absent_count' => 2, 'notes' => 'First entry']);

    // Duplicate entry should update
    $payload['present_count'] = 12;
    $response2 = $this->postJson(route('minister-unit-attendance.store', $attendance->id), $payload);
    $response2->assertStatus(201)
      ->assertJsonFragment(['present_count' => 12]);
  }

  public function test_index_returns_unit_attendance_list()
  {
    $attendance = MinisterAttendance::factory()->create();
    MinisterUnitAttendance::factory()->count(3)->create(['minister_attendance_id' => $attendance->id]);

    $response = $this->getJson(route('minister-unit-attendance.index', $attendance->id));
    $response->assertStatus(200)
      ->assertJsonStructure(['success', 'data', 'total']);
  }

  public function test_update_modifies_unit_attendance()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $unitAttendance = MinisterUnitAttendance::factory()->create();

    $payload = ['present_count' => 20, 'notes' => 'Updated'];
    $response = $this->putJson(route('minister-unit-attendance.update', $unitAttendance->id), $payload);
    $response->assertStatus(200)
      ->assertJsonFragment(['present_count' => 20, 'notes' => 'Updated']);
  }

  public function test_destroy_deletes_unit_attendance()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $unitAttendance = MinisterUnitAttendance::factory()->create();

    $response = $this->deleteJson(route('minister-unit-attendance.destroy', $unitAttendance->id));
    $response->assertStatus(200)
      ->assertJsonFragment(['success' => true, 'message' => 'Unit attendance deleted']);
    $this->assertDatabaseMissing('minister_unit_attendances', ['id' => $unitAttendance->id]);
  }
}
