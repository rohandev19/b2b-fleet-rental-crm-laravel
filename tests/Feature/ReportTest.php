<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_reports_with_period_aggregates(): void
    {
        $sales = User::factory()->create([
            'name' => 'Rohan Sales',
            'role' => UserRole::Sales,
        ]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $prospect = Prospect::factory()->create([
            'assigned_sales_id' => $sales->id,
            'status' => 'won',
            'created_at' => '2026-07-10 10:00:00',
        ]);

        Quotation::factory()->create([
            'prospect_id' => $prospect->id,
            'sales_id' => $sales->id,
            'status' => 'approved',
            'quotation_date' => '2026-07-10',
            'grand_total' => 22000000,
        ]);

        Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'draft',
            'quotation_date' => '2026-06-10',
            'grand_total' => 9000000,
        ]);

        FollowUpActivity::factory()->create([
            'prospect_id' => $prospect->id,
            'user_id' => $sales->id,
            'activity_date' => '2026-07-11 09:00:00',
            'outcome' => 'positive',
        ]);

        $this->actingAs($manager)
            ->get('/reports?start_date=2026-07-01&end_date=2026-07-31')
            ->assertOk()
            ->assertSee('Reports')
            ->assertSee('Rp22.000.000')
            ->assertSee('Rohan Sales')
            ->assertSee('Positive')
            ->assertSee('Won');
    }

    public function test_sales_cannot_access_reports(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);

        $this->actingAs($sales)
            ->get('/reports')
            ->assertForbidden();
    }
}
