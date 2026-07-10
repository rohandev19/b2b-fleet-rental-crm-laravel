<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Prospect;
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

    public function test_sales_can_mark_approved_generated_quotation_as_sent(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $quotation = Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'approved',
            'pdf_path' => 'quotations/example.pdf',
            'pdf_generated_at' => now(),
        ]);

        $this->actingAs($sales)
            ->post("/quotations/{$quotation->id}/mark-sent")
            ->assertRedirect(route('quotations.show', $quotation));

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('quotation_approvals', [
            'quotation_id' => $quotation->id,
            'user_id' => $sales->id,
            'action' => 'mark_sent',
            'from_status' => 'approved',
            'to_status' => 'sent',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'quotation.sent',
            'auditable_type' => Quotation::class,
            'auditable_id' => $quotation->id,
        ]);
    }

    public function test_quotation_requires_pdf_before_marking_as_sent(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $quotation = Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'approved',
            'pdf_path' => null,
        ]);

        $this->actingAs($sales)
            ->post("/quotations/{$quotation->id}/mark-sent")
            ->assertSessionHasErrors('pdf_path');

        $this->assertSame('approved', $quotation->fresh()->status);
    }

    public function test_finance_cannot_mark_quotation_as_sent(): void
    {
        $finance = User::factory()->create(['role' => UserRole::Finance]);
        $quotation = Quotation::factory()->create([
            'status' => 'approved',
            'pdf_path' => 'quotations/example.pdf',
            'pdf_generated_at' => now(),
        ]);

        $this->actingAs($finance)
            ->post("/quotations/{$quotation->id}/mark-sent")
            ->assertForbidden();
    }

    public function test_sales_can_mark_sent_quotation_as_accepted(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $prospect = Prospect::factory()->create([
            'status' => 'quotation',
            'lost_reason' => 'Previous note.',
        ]);
        $quotation = Quotation::factory()->create([
            'prospect_id' => $prospect->id,
            'sales_id' => $sales->id,
            'status' => 'sent',
        ]);

        $this->actingAs($sales)
            ->post("/quotations/{$quotation->id}/accept")
            ->assertRedirect(route('quotations.show', $quotation));

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'accepted',
        ]);

        $this->assertDatabaseHas('quotation_approvals', [
            'quotation_id' => $quotation->id,
            'user_id' => $sales->id,
            'action' => 'accept',
            'from_status' => 'sent',
            'to_status' => 'accepted',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'quotation.accepted',
            'auditable_type' => Quotation::class,
            'auditable_id' => $quotation->id,
        ]);

        $this->assertDatabaseHas('prospects', [
            'id' => $prospect->id,
            'status' => 'won',
            'lost_reason' => null,
        ]);
    }

    public function test_manager_can_mark_sent_quotation_as_declined(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $prospect = Prospect::factory()->create(['status' => 'negotiation']);
        $quotation = Quotation::factory()->create([
            'quotation_number' => 'QTN/HAN/2026/07/9090',
            'prospect_id' => $prospect->id,
            'status' => 'sent',
        ]);

        $this->actingAs($manager)
            ->post("/quotations/{$quotation->id}/decline")
            ->assertRedirect(route('quotations.show', $quotation));

        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'status' => 'declined',
        ]);

        $this->assertDatabaseHas('quotation_approvals', [
            'quotation_id' => $quotation->id,
            'user_id' => $manager->id,
            'action' => 'decline',
            'from_status' => 'sent',
            'to_status' => 'declined',
        ]);

        $this->assertDatabaseHas('prospects', [
            'id' => $prospect->id,
            'status' => 'lost',
            'lost_reason' => 'Quotation QTN/HAN/2026/07/9090 was declined by the customer.',
        ]);
    }

    public function test_only_sent_quotations_can_be_marked_as_customer_outcome(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $quotation = Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'approved',
        ]);

        $this->actingAs($sales)
            ->post("/quotations/{$quotation->id}/accept")
            ->assertSessionHasErrors('status');

        $this->actingAs($sales)
            ->post("/quotations/{$quotation->id}/decline")
            ->assertSessionHasErrors('status');

        $this->assertSame('approved', $quotation->fresh()->status);
    }
}
