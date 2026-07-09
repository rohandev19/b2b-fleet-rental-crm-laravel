<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_creation_is_audited_without_password_values(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Audited Finance',
            'email' => 'audited.finance@example.com',
            'role' => UserRole::Finance->value,
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_active' => '1',
        ])->assertRedirect('/users');

        $log = AuditLog::query()->where('action', 'user.created')->firstOrFail();

        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('audited.finance@example.com', $log->new_values['email']);
        $this->assertArrayNotHasKey('password', $log->new_values);
    }

    public function test_quotation_approval_is_audited(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $quotation = Quotation::factory()->create([
            'sales_id' => $sales->id,
            'status' => 'submitted',
        ]);

        $this->actingAs($manager)
            ->post(route('quotations.approve', $quotation))
            ->assertRedirect(route('quotations.show', $quotation));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $manager->id,
            'auditable_type' => Quotation::class,
            'auditable_id' => $quotation->id,
            'action' => 'quotation.approved',
        ]);
    }

    public function test_manager_can_view_audit_logs_and_finance_cannot(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $finance = User::factory()->create(['role' => UserRole::Finance]);
        $prospect = Prospect::factory()->create(['company_name' => 'Audit Target Co']);

        AuditLog::query()->create([
            'user_id' => $manager->id,
            'auditable_type' => Prospect::class,
            'auditable_id' => $prospect->id,
            'action' => 'prospect.updated',
            'summary' => 'Updated prospect Audit Target Co.',
        ]);

        $this->actingAs($manager)
            ->get('/audit-logs')
            ->assertOk()
            ->assertSee('Audit Target Co')
            ->assertSee('Prospect Updated');

        $this->actingAs($finance)
            ->get('/audit-logs')
            ->assertForbidden();
    }
}
