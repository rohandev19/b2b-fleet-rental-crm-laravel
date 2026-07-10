<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Prospect;
use App\Models\ProspectContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProspectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_roles_can_view_prospect_index(): void
    {
        foreach ([UserRole::Admin, UserRole::Sales, UserRole::Manager, UserRole::Finance] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get('/prospects')
                ->assertOk()
                ->assertSee('Track company leads');
        }
    }

    public function test_sales_can_create_prospect(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);

        $response = $this->actingAs($sales)->post('/prospects', $this->validProspectPayload([
            'company_name' => 'PT Maju Logistik',
        ]));

        $prospect = Prospect::query()->where('company_name', 'PT Maju Logistik')->firstOrFail();

        $response->assertRedirect(route('prospects.show', $prospect));

        $this->assertDatabaseHas('prospects', [
            'company_name' => 'PT Maju Logistik',
            'assigned_sales_id' => $sales->id,
            'status' => 'new',
        ]);
    }

    public function test_manager_cannot_create_prospect(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $this->actingAs($manager)
            ->post('/prospects', $this->validProspectPayload())
            ->assertForbidden();
    }

    public function test_company_name_must_be_unique_case_insensitive(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Prospect::factory()->create(['company_name' => 'PT Sinar Fleet']);

        $this->actingAs($admin)
            ->post('/prospects', $this->validProspectPayload([
                'company_name' => 'pt sinar fleet',
            ]))
            ->assertSessionHasErrors('company_name');
    }

    public function test_high_priority_requires_next_follow_up(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);

        $this->actingAs($sales)
            ->post('/prospects', $this->validProspectPayload([
                'priority' => 'high',
                'next_follow_up_at' => null,
            ]))
            ->assertSessionHasErrors('next_follow_up_at');
    }

    public function test_lost_status_requires_reason(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);

        $this->actingAs($sales)
            ->post('/prospects', $this->validProspectPayload([
                'status' => 'lost',
                'lost_reason' => null,
            ]))
            ->assertSessionHasErrors('lost_reason');
    }

    public function test_won_status_is_reserved_for_quotation_acceptance_workflow(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);

        $this->actingAs($sales)
            ->post('/prospects', $this->validProspectPayload([
                'status' => 'won',
            ]))
            ->assertSessionHasErrors([
                'status' => 'Won status is set automatically when a sent quotation is accepted.',
            ]);
    }

    public function test_sales_can_add_primary_contact_and_replace_previous_primary(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create(['assigned_sales_id' => $sales->id]);
        $oldPrimary = ProspectContact::factory()->create([
            'prospect_id' => $prospect->id,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($sales)->post("/prospects/{$prospect->id}/contacts", [
            'name' => 'Budi Procurement',
            'position' => 'Procurement',
            'email' => 'budi@example.com',
            'phone' => '+62 812 1111 2222',
            'is_primary' => '1',
        ]);

        $response->assertRedirect(route('prospects.show', $prospect));

        $this->assertFalse($oldPrimary->fresh()->is_primary);
        $this->assertDatabaseHas('prospect_contacts', [
            'prospect_id' => $prospect->id,
            'email' => 'budi@example.com',
            'is_primary' => true,
        ]);
    }

    public function test_contact_phone_may_not_contain_letters(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create(['assigned_sales_id' => $sales->id]);

        $this->actingAs($sales)->post("/prospects/{$prospect->id}/contacts", [
            'name' => 'Bad Phone',
            'phone' => 'CALL-ME',
        ])->assertSessionHasErrors('phone');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validProspectPayload(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'PT Contoh Armada',
            'industry' => 'Logistics',
            'company_size' => 'medium',
            'address' => 'Jl. Sudirman No. 1',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'website' => 'https://example.com',
            'source' => 'manual',
            'status' => 'new',
            'priority' => 'medium',
            'estimated_vehicle_need' => 12,
            'next_follow_up_at' => null,
            'lost_reason' => null,
            'notes' => 'Initial prospect.',
        ], $overrides);
    }
}
