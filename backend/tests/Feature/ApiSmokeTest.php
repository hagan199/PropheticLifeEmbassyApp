<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class ApiSmokeTest extends TestCase
{
  use RefreshDatabase;

  public function test_public_auth_endpoints()
  {
    $this->postJson('/api/auth/login', [])->assertStatus(422);
    $this->postJson('/api/auth/forgot-password', [])->assertStatus(422);
    $this->postJson('/api/auth/reset-password', [])->assertStatus(422);
  }

  public function test_protected_endpoints_require_auth()
  {
    $protected = [
      '/api/auth/logout',
      '/api/auth/user',
      '/api/dashboard/stats',
      '/api/users',
      '/api/attendance',
      '/api/visitors',
      '/api/follow-ups',
      '/api/contributions',
      '/api/expenses',
      '/api/departments',
      '/api/broadcasts',
      '/api/audit-logs',
      '/api/members',
    ];
    foreach ($protected as $url) {
      $this->getJson($url)->assertStatus(401);
    }
  }

  public function test_authenticated_user_can_access_dashboard()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->getJson('/api/dashboard/stats')->assertStatus(200);
  }
}
