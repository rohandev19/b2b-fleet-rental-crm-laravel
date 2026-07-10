<?php

namespace Tests\Feature;

use App\Models\Prospect;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QuotationExpiryCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_expire_quotations_command_marks_past_valid_active_quotations_as_expired(): void
    {
        Carbon::setTestNow('2026-07-10 08:00:00');

        $prospect = Prospect::factory()->create([
            'company_name' => 'PT Expired Quote',
        ]);

        $expiredCandidate = Quotation::factory()->create([
            'quotation_number' => 'QTN/HAN/2026/07/8001',
            'prospect_id' => $prospect->id,
            'status' => 'sent',
            'valid_until' => '2026-07-09',
        ]);
        $futureQuotation = Quotation::factory()->create([
            'status' => 'sent',
            'valid_until' => '2026-07-11',
        ]);
        $acceptedQuotation = Quotation::factory()->create([
            'status' => 'accepted',
            'valid_until' => '2026-07-01',
        ]);

        $this->artisan('crm:expire-quotations')
            ->expectsOutput('1 quotation(s) need to be expired.')
            ->expectsTable(
                ['Quotation', 'Prospect', 'Current Status', 'Valid Until'],
                [
                    ['QTN/HAN/2026/07/8001', 'PT Expired Quote', 'Sent', '2026-07-09'],
                ],
            )
            ->expectsOutput('1 quotation(s) marked as expired.')
            ->assertExitCode(0);

        $this->assertSame('expired', $expiredCandidate->fresh()->status);
        $this->assertSame('sent', $futureQuotation->fresh()->status);
        $this->assertSame('accepted', $acceptedQuotation->fresh()->status);
    }

    public function test_expire_quotations_command_dry_run_does_not_update_records(): void
    {
        Carbon::setTestNow('2026-07-10 08:00:00');

        $quotation = Quotation::factory()->create([
            'quotation_number' => 'QTN/HAN/2026/07/8002',
            'status' => 'approved',
            'valid_until' => '2026-07-08',
        ]);

        $this->artisan('crm:expire-quotations --dry-run')
            ->expectsOutput('1 quotation(s) need to be expired.')
            ->expectsOutput('Dry run only. No quotations were updated.')
            ->assertExitCode(0);

        $this->assertSame('approved', $quotation->fresh()->status);
    }

    public function test_expire_quotations_command_reports_empty_state(): void
    {
        Carbon::setTestNow('2026-07-10 08:00:00');

        Quotation::factory()->create([
            'status' => 'sent',
            'valid_until' => '2026-07-10',
        ]);

        $this->artisan('crm:expire-quotations')
            ->expectsOutput('No quotations need to be expired.')
            ->assertExitCode(0);
    }
}
