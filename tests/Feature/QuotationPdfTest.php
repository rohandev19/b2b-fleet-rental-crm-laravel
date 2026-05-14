<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Quotation;
use App\Models\User;
use App\Services\Quotation\QuotationPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuotationPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_generate_pdf_for_approved_quotation(): void
    {
        Storage::fake('local');

        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $quotation = Quotation::factory()
            ->hasItems(1)
            ->create([
                'status' => 'approved',
                'approved_by' => $manager->id,
            ]);

        $this->actingAs($manager)
            ->post(route('quotations.generate-pdf', $quotation))
            ->assertRedirect(route('quotations.show', $quotation));

        $quotation->refresh();

        $this->assertNotNull($quotation->pdf_path);
        $this->assertNotNull($quotation->pdf_generated_at);
        Storage::disk('local')->assertExists($quotation->pdf_path);
    }

    public function test_pdf_cannot_be_generated_for_non_approved_quotation(): void
    {
        Storage::fake('local');

        $sales = User::factory()->create(['role' => UserRole::Sales]);
        $quotation = Quotation::factory()
            ->hasItems(1)
            ->create([
                'sales_id' => $sales->id,
                'status' => 'draft',
            ]);

        $this->actingAs($sales)
            ->post(route('quotations.generate-pdf', $quotation))
            ->assertSessionHasErrors('status');

        $this->assertNull($quotation->refresh()->pdf_path);
    }

    public function test_finance_can_download_generated_approved_quotation_pdf(): void
    {
        Storage::fake('local');

        $finance = User::factory()->create(['role' => UserRole::Finance]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $quotation = Quotation::factory()
            ->hasItems(1)
            ->create([
                'status' => 'approved',
                'approved_by' => $manager->id,
            ]);

        app(QuotationPdfService::class)->generate($quotation);

        $this->actingAs($finance)
            ->get(route('quotations.download-pdf', $quotation))
            ->assertOk()
            ->assertDownload(app(QuotationPdfService::class)->downloadFilename($quotation));
    }

    public function test_download_requires_generated_pdf_file(): void
    {
        Storage::fake('local');

        $finance = User::factory()->create(['role' => UserRole::Finance]);
        $quotation = Quotation::factory()
            ->hasItems(1)
            ->create(['status' => 'approved']);

        $this->actingAs($finance)
            ->get(route('quotations.download-pdf', $quotation))
            ->assertRedirect(route('quotations.show', $quotation))
            ->assertSessionHasErrors('pdf_path');
    }

    public function test_finance_cannot_generate_quotation_pdf(): void
    {
        $finance = User::factory()->create(['role' => UserRole::Finance]);
        $quotation = Quotation::factory()
            ->hasItems(1)
            ->create(['status' => 'approved']);

        $this->actingAs($finance)
            ->post(route('quotations.generate-pdf', $quotation))
            ->assertForbidden();
    }
}
