<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Prospect;
use App\Models\ProspectContact;
use App\Models\Quotation;
use App\Models\RentalPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_can_create_quotation_draft_with_server_side_calculation(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create(['assigned_sales_id' => $sales->id, 'status' => 'meeting']);
        $contact = ProspectContact::factory()->create(['prospect_id' => $prospect->id]);
        $vehicle = Vehicle::factory()->create(['is_active' => true]);
        $package = RentalPackage::factory()->create(['is_active' => true]);

        $response = $this->actingAs($sales)->post('/quotations', [
            'prospect_id' => $prospect->id,
            'contact_id' => $contact->id,
            'quotation_date' => '2026-07-10',
            'valid_until' => '2026-07-24',
            'discount_amount' => 8000000,
            'tax_percent' => 11,
            'terms_and_conditions' => 'Standard terms.',
            'items' => [
                [
                    'vehicle_id' => $vehicle->id,
                    'package_id' => $package->id,
                    'quantity' => 2,
                    'duration_months' => 12,
                    'monthly_price' => 5000000,
                    'discount_percent' => 10,
                ],
            ],
        ]);

        $quotation = Quotation::query()->firstOrFail();

        $response->assertRedirect(route('quotations.show', $quotation));

        $this->assertSame('QTN/HAN/2026/07/0001', $quotation->quotation_number);
        $this->assertSame('draft', $quotation->status);
        $this->assertSame('108000000.00', $quotation->subtotal);
        $this->assertSame('11000000.00', $quotation->tax_amount);
        $this->assertSame('111000000.00', $quotation->grand_total);
        $this->assertDatabaseHas('quotation_items', [
            'quotation_id' => $quotation->id,
            'vehicle_id' => $vehicle->id,
            'line_total' => 108000000,
        ]);
        $this->assertDatabaseHas('prospects', [
            'id' => $prospect->id,
            'status' => 'quotation',
        ]);
    }

    public function test_next_quotation_number_increments_within_month(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create(['assigned_sales_id' => $sales->id]);
        $vehicle = Vehicle::factory()->create(['is_active' => true]);

        Quotation::factory()->create([
            'quotation_number' => 'QTN/HAN/2026/07/0007',
            'quotation_date' => '2026-07-01',
        ]);

        $this->actingAs($sales)->post('/quotations', $this->validPayload($prospect, $vehicle, [
            'quotation_date' => '2026-07-10',
            'valid_until' => '2026-07-18',
        ]));

        $this->assertDatabaseHas('quotations', [
            'quotation_number' => 'QTN/HAN/2026/07/0008',
        ]);
    }

    public function test_valid_until_must_be_at_least_seven_days_after_quotation_date(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create(['assigned_sales_id' => $sales->id]);
        $vehicle = Vehicle::factory()->create(['is_active' => true]);

        $this->actingAs($sales)
            ->post('/quotations', $this->validPayload($prospect, $vehicle, [
                'quotation_date' => '2026-07-10',
                'valid_until' => '2026-07-15',
            ]))
            ->assertSessionHasErrors('valid_until');
    }

    public function test_contact_must_belong_to_selected_prospect(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create(['assigned_sales_id' => $sales->id]);
        $otherContact = ProspectContact::factory()->create();
        $vehicle = Vehicle::factory()->create(['is_active' => true]);

        $this->actingAs($sales)
            ->post('/quotations', $this->validPayload($prospect, $vehicle, [
                'contact_id' => $otherContact->id,
            ]))
            ->assertSessionHasErrors('contact_id');
    }

    public function test_inactive_vehicle_cannot_be_used_in_new_quotation(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create(['assigned_sales_id' => $sales->id]);
        $vehicle = Vehicle::factory()->create(['is_active' => false]);

        $this->actingAs($sales)
            ->post('/quotations', $this->validPayload($prospect, $vehicle))
            ->assertSessionHasErrors('items.0.vehicle_id');
    }

    public function test_manager_can_view_quotation_but_cannot_create(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $quotation = Quotation::factory()->hasItems(1)->create();

        $this->actingAs($manager)
            ->get('/quotations')
            ->assertOk();

        $this->actingAs($manager)
            ->get(route('quotations.show', $quotation))
            ->assertOk()
            ->assertSee($quotation->quotation_number);

        $this->actingAs($manager)
            ->post('/quotations', [])
            ->assertForbidden();
    }

    private function validPayload(Prospect $prospect, Vehicle $vehicle, array $overrides = []): array
    {
        return array_merge([
            'prospect_id' => $prospect->id,
            'contact_id' => null,
            'quotation_date' => '2026-07-10',
            'valid_until' => '2026-07-24',
            'discount_amount' => 0,
            'tax_percent' => 11,
            'terms_and_conditions' => 'Standard terms.',
            'items' => [
                [
                    'vehicle_id' => $vehicle->id,
                    'package_id' => null,
                    'quantity' => 1,
                    'duration_months' => 12,
                    'monthly_price' => 1000000,
                    'discount_percent' => 0,
                ],
            ],
        ], $overrides);
    }
}
