<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test store unit attendance successfully
     */
    public function test_store_unit_attendance_successfully()
    {
        $user = $this->createUsher();

        $payload = [
            'unit' => 'Usher',
            'service' => 'Sunday Service',
            'date' => now()->toDateString(),
            'time' => '10:00 AM',
            'member_id' => 1,
            'member_name' => 'John Doe',
            'present' => true,
        ];

        $response = $this->postJson('/api/attendance/unit', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Unit attendance recorded',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'service_type',
                    'service_date',
                    'unit',
                    'member_name',
                    'present',
                    'status',
                ],
            ]);

        // Verify data in database
        $this->assertDatabaseHas('attendances', [
            'service_type' => 'Sunday Service',
            'unit' => 'Usher',
            'member_name' => 'John Doe',
            'present' => true,
            'status' => 'pending',
            'submitted_by' => $user->id,
        ]);
    }

    /**
     * Test store unit attendance validation
     */
    public function test_store_unit_attendance_validation()
    {
        $user = $this->createUsher();

        $response = $this->postJson('/api/attendance/unit', []);

        $this->assertValidationErrors($response, [
            'unit',
            'service',
            'date',
            'time',
            'member_id',
            'member_name',
            'present',
        ]);
    }

    /**
     * Test store general attendance successfully
     */
    public function test_store_general_attendance_successfully()
    {
        $user = $this->createUsher();

        $payload = [
            'service_type' => 'Sunday Service',
            'service_date' => now()->toDateString(),
            'count' => 150,
            'notes' => 'Great service today',
        ];

        $response = $this->postJson('/api/attendance', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance recorded successfully',
            ]);

        $this->assertDatabaseHas('attendances', [
            'service_type' => 'Sunday Service',
            'count' => 150,
            'status' => 'pending',
            'submitted_by' => $user->id,
        ]);
    }

    /**
     * Test index returns attendance list
     */
    public function test_index_returns_attendance_list()
    {
        $user = $this->createUsher();

        Attendance::factory()->count(5)->create([
            'submitted_by' => $user->id,
        ]);

        $response = $this->getJson('/api/attendance');

        $response->assertStatus(200);
        $this->assertPaginatedResponse($response);
        $this->assertEquals(5, $response->json('total'));
    }

    /**
     * Test show returns single attendance record
     */
    public function test_show_returns_single_attendance_record()
    {
        $user = $this->createUsher();

        $attendance = Attendance::factory()->create([
            'service_type' => 'Sunday Service',
            'submitted_by' => $user->id,
        ]);

        $response = $this->getJson("/api/attendance/{$attendance->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $attendance->id,
                    'service_type' => 'Sunday Service',
                ],
            ]);
    }

    /**
     * Test show returns 404 for non-existent record
     */
    public function test_show_returns_404_for_non_existent_record()
    {
        $user = $this->createUsher();

        $response = $this->getJson('/api/attendance/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Record not found',
            ]);
    }

    /**
     * Test update attendance successfully
     */
    public function test_update_attendance_successfully()
    {
        $user = $this->createUsher();

        $attendance = Attendance::factory()->create([
            'service_type' => 'Old Service',
            'count' => 100,
            'submitted_by' => $user->id,
        ]);

        $response = $this->putJson("/api/attendance/{$attendance->id}", [
            'service_type' => 'Updated Service',
            'count' => 200,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance updated successfully',
            ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'service_type' => 'Updated Service',
            'count' => 200,
        ]);
    }

    /**
     * Test update returns 404 for non-existent record
     */
    public function test_update_returns_404_for_non_existent_record()
    {
        $user = $this->createUsher();

        $response = $this->putJson('/api/attendance/99999', [
            'service_type' => 'Updated Service',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Record not found',
            ]);
    }

    /**
     * Test delete attendance successfully
     */
    public function test_delete_attendance_successfully()
    {
        $user = $this->createUsher();

        $attendance = Attendance::factory()->create([
            'submitted_by' => $user->id,
        ]);

        $response = $this->deleteJson("/api/attendance/{$attendance->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Attendance deleted successfully',
            ]);

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent record
     */
    public function test_delete_returns_404_for_non_existent_record()
    {
        $user = $this->createUsher();

        $response = $this->deleteJson('/api/attendance/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Record not found',
            ]);
    }

    /**
     * Test pending approvals returns only pending records
     */
    public function test_pending_approvals_returns_only_pending_records()
    {
        $admin = $this->createAdmin();

        Attendance::factory()->count(3)->create(['status' => 'pending']);
        Attendance::factory()->count(2)->create(['status' => 'approved']);

        $response = $this->getJson('/api/attendance/approvals/pending');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test bulk approve attendance
     */
    public function test_bulk_approve_attendance()
    {
        $admin = $this->createAdmin();

        $attendance1 = Attendance::factory()->create(['status' => 'pending']);
        $attendance2 = Attendance::factory()->create(['status' => 'pending']);
        $attendance3 = Attendance::factory()->create(['status' => 'pending']);

        $response = $this->postJson('/api/attendance/approvals/bulk-approve', [
            'ids' => [$attendance1->id, $attendance2->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance1->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance2->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        // Third attendance should still be pending
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance3->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test bulk approve validation
     */
    public function test_bulk_approve_validation()
    {
        $admin = $this->createAdmin();

        $response = $this->postJson('/api/attendance/approvals/bulk-approve', []);

        $this->assertValidationErrors($response, ['ids']);
    }

    /**
     * Test approve single attendance
     */
    public function test_approve_single_attendance()
    {
        $admin = $this->createAdmin();

        $attendance = Attendance::factory()->create(['status' => 'pending']);

        $response = $this->postJson("/api/attendance/approvals/{$attendance->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }

    /**
     * Test reject single attendance
     */
    public function test_reject_single_attendance()
    {
        $admin = $this->createAdmin();

        $attendance = Attendance::factory()->create(['status' => 'pending']);

        $response = $this->postJson("/api/attendance/approvals/{$attendance->id}/reject", [
            'rejection_reason' => 'Incorrect count',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'rejected',
            'rejection_reason' => 'Incorrect count',
            'approved_by' => $admin->id,
        ]);
    }

    /**
     * Test reject validation requires reason
     */
    public function test_reject_validation_requires_reason()
    {
        $admin = $this->createAdmin();

        $attendance = Attendance::factory()->create(['status' => 'pending']);

        $response = $this->postJson("/api/attendance/approvals/{$attendance->id}/reject", []);

        $this->assertValidationErrors($response, ['rejection_reason']);
    }

    /**
     * Test user submissions returns only user's records
     */
    public function test_user_submissions_returns_only_users_records()
    {
        $user1 = $this->createUsher();
        $user2 = User::factory()->create();

        Attendance::factory()->count(3)->create(['submitted_by' => $user1->id]);
        Attendance::factory()->count(2)->create(['submitted_by' => $user2->id]);

        $response = $this->getJson('/api/attendance/my-submissions');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test attendance statistics endpoint
     */
    public function test_attendance_statistics()
    {
        $admin = $this->createAdmin();

        Attendance::factory()->count(5)->create([
            'status' => 'approved',
            'service_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/attendance/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    /**
     * Test filtering by service type
     */
    public function test_filter_by_service_type()
    {
        $user = $this->createUsher();

        Attendance::factory()->count(3)->create(['service_type' => 'Sunday Service']);
        Attendance::factory()->count(2)->create(['service_type' => 'Wednesday Service']);

        $response = $this->getJson('/api/attendance?service_type=Sunday Service');

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test filtering by date range
     */
    public function test_filter_by_date_range()
    {
        $user = $this->createUsher();

        Attendance::factory()->count(2)->create([
            'service_date' => now()->subDays(10)->toDateString(),
        ]);
        Attendance::factory()->count(3)->create([
            'service_date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/attendance?from=' . now()->subDays(5)->toDateString());

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('total'));
    }

    /**
     * Test unauthenticated user cannot access attendance endpoints
     */
    public function test_unauthenticated_user_cannot_access_attendance()
    {
        $response = $this->getJson('/api/attendance');
        $this->assertUnauthorized($response);

        $response = $this->postJson('/api/attendance', []);
        $this->assertUnauthorized($response);
    }

    /**
     * Test attendance sorting by date
     */
    public function test_attendance_sorting_by_date()
    {
        $user = $this->createUsher();

        $older = Attendance::factory()->create([
            'service_date' => now()->subDays(5)->toDateString(),
            'submitted_by' => $user->id,
        ]);
        $newer = Attendance::factory()->create([
            'service_date' => now()->toDateString(),
            'submitted_by' => $user->id,
        ]);

        $response = $this->getJson('/api/attendance?sort=desc');

        $response->assertStatus(200);

        $data = $response->json('data');
        if (count($data) >= 2) {
            $this->assertTrue(strtotime($data[0]['service_date']) >= strtotime($data[1]['service_date']));
        }
    }
}
