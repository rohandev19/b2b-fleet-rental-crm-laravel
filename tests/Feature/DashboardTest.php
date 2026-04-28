<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_role_specific_workspace(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Manager,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response
            ->assertOk()
            ->assertSee('Sales Manager workspace')
            ->assertSee('Waiting approval');
    }
}
