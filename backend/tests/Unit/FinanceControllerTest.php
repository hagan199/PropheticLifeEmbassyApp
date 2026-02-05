<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class FinanceControllerTest extends TestCase
{
  use RefreshDatabase;

  public function test_index_contributions()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/contributions');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }

  public function test_index_expenses()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/expenses');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data', 'total']);
  }

  public function test_expense_types()
  {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->getJson('/api/expenses/types/all');
    $response->assertStatus(200)->assertJsonStructure(['success', 'data']);
  }
}
