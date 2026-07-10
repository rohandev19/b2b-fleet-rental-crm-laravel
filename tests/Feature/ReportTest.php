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

    public function test_manager_can_export_prospects_csv_for_selected_period(): void
    {
        $sales = User::factory()->create([
            'name' => 'Nadia Sales',
            'role' => UserRole::Sales,
        ]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        Prospect::factory()->create([
            'company_name' => 'PT Armada Prima',
            'industry' => 'Logistics',
            'city' => 'Jakarta',
            'status' => 'meeting',
            'priority' => 'high',
            'estimated_vehicle_need' => 12,
            'assigned_sales_id' => $sales->id,
            'created_at' => '2026-07-12 08:30:00',
        ]);

        Prospect::factory()->create([
            'company_name' => 'PT Outside Period',
            'created_at' => '2026-06-12 08:30:00',
        ]);

        $response = $this->actingAs($manager)
            ->get('/reports/exports/prospects?start_date=2026-07-01&end_date=2026-07-31');

        $response
            ->assertOk()
            ->assertDownload('prospects-2026-07-01-to-2026-07-31.csv');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('"Company Name",Industry,City,Status,Priority', $csv);
        $this->assertStringContainsString('"PT Armada Prima",Logistics,Jakarta,Meeting,High,12,"Nadia Sales"', $csv);
        $this->assertStringNotContainsString('PT Outside Period', $csv);
    }

    public function test_finance_can_export_quotations_csv_for_selected_period(): void
    {
        $sales = User::factory()->create([
            'name' => 'Rohan Sales',
            'role' => UserRole::Sales,
        ]);
        $finance = User::factory()->create(['role' => UserRole::Finance]);
        $prospect = Prospect::factory()->create([
            'company_name' => 'PT Sewa Nasional',
            'assigned_sales_id' => $sales->id,
        ]);

        Quotation::factory()->create([
            'quotation_number' => 'QTN/HAN/2026/07/0007',
            'prospect_id' => $prospect->id,
            'sales_id' => $sales->id,
            'status' => 'approved',
            'quotation_date' => '2026-07-15',
            'valid_until' => '2026-07-30',
            'subtotal' => 20000000,
            'discount_amount' => 1000000,
            'tax_amount' => 2090000,
            'grand_total' => 21090000,
        ]);

        Quotation::factory()->create([
            'quotation_number' => 'QTN/HAN/2026/06/0001',
            'quotation_date' => '2026-06-15',
        ]);

        $response = $this->actingAs($finance)
            ->get('/reports/exports/quotations?start_date=2026-07-01&end_date=2026-07-31');

        $response
            ->assertOk()
            ->assertDownload('quotations-2026-07-01-to-2026-07-31.csv');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('"Quotation Number",Prospect,Sales,"Quotation Date","Valid Until",Status', $csv);
        $this->assertStringContainsString('QTN/HAN/2026/07/0007,"PT Sewa Nasional","Rohan Sales",2026-07-15,2026-07-30,Approved,20000000.00,1000000.00,2090000.00,21090000.00', $csv);
        $this->assertStringNotContainsString('QTN/HAN/2026/06/0001', $csv);
    }

    public function test_sales_cannot_export_report_csv_files(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);

        $this->actingAs($sales)
            ->get('/reports/exports/prospects')
            ->assertForbidden();

        $this->actingAs($sales)
            ->get('/reports/exports/quotations')
            ->assertForbidden();
    }
}
