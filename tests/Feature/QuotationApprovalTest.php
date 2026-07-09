<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_can_submit_draft_quotation(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $quotation = Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'draft',
        ]);

        $this->actingAs($sales)
            ->post("/quotations/{$quotation->id}/submit")
            ->assertRedirect(route('quotations.show', $quotation));

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('quotation_approvals', [
            'quotation_id' => $quotation->id,
            'user_id' => $sales->id,
            'action' => 'submit',
            'from_status' => 'draft',
            'to_status' => 'submitted',
        ]);
    }

    public function test_manager_can_approve_submitted_quotation(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $quotation = Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'submitted',
        ]);

        $this->actingAs($manager)
            ->post("/quotations/{$quotation->id}/approve")
            ->assertRedirect(route('quotations.show', $quotation));

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'approved',
            'approved_by' => $manager->id,
        ]);

        $this->assertDatabaseHas('quotation_approvals', [
            'quotation_id' => $quotation->id,
            'user_id' => $manager->id,
            'action' => 'approve',
            'from_status' => 'submitted',
            'to_status' => 'approved',
        ]);
    }

    public function test_manager_can_reject_submitted_quotation_with_reason(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $quotation = Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'submitted',
        ]);

        $this->actingAs($manager)
            ->post("/quotations/{$quotation->id}/reject", [
                'rejection_reason' => 'Price needs manager revision.',
            ])
            ->assertRedirect(route('quotations.show', $quotation));

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('quotation_approvals', [
            'quotation_id' => $quotation->id,
            'user_id' => $manager->id,
            'action' => 'reject',
            'reason' => 'Price needs manager revision.',
        ]);
    }

    public function test_rejection_reason_is_required(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $quotation = Quotation::factory()->create(['status' => 'submitted']);

        $this->actingAs($manager)
            ->post("/quotations/{$quotation->id}/reject", [
                'rejection_reason' => '',
            ])
            ->assertSessionHasErrors('rejection_reason');
    }

    public function test_sales_cannot_approve_quotation(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $quotation = Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'submitted',
        ]);

        $this->actingAs($sales)
            ->post("/quotations/{$quotation->id}/approve")
            ->assertForbidden();
    }

    public function test_manager_cannot_approve_non_submitted_quotation(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $quotation = Quotation::factory()->create(['status' => 'draft']);

        $this->actingAs($manager)
            ->post("/quotations/{$quotation->id}/approve")
            ->assertSessionHasErrors('status');
    }
}
