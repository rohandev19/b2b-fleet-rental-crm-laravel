<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\Quotation;
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

    public function test_dashboard_uses_live_pipeline_and_quotation_data(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $prospect = Prospect::factory()->create([
            'assigned_sales_id' => $sales->id,
            'status' => 'quotation',
            'priority' => 'high',
        ]);

        Quotation::factory()->create([
            'prospect_id' => $prospect->id,
            'sales_id' => $sales->id,
            'status' => 'submitted',
            'grand_total' => 15000000,
        ]);

        FollowUpActivity::factory()->create([
            'prospect_id' => $prospect->id,
            'user_id' => $sales->id,
            'summary' => 'Call procurement team',
            'next_follow_up_at' => now()->subDay(),
        ]);

        $this->actingAs($manager)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Rp15.000.000')
            ->assertSee('1 quotations waiting approval')
            ->assertSee('Call procurement team')
            ->assertSee($prospect->company_name);
    }
}
