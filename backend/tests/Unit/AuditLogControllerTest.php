<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuditLogControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_index_audit_logs()
  {
    $user = User::factory()->create(['role' => 'admin']);
    $this->actingAs($user);
    $response = $this->getJson('/api/audit-logs');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }
}
