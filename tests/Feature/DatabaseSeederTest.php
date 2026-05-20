<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\RentalPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_portfolio_demo_dataset(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'sales@example.com']);
        $this->assertSame(4, User::query()->count());

        $this->assertGreaterThanOrEqual(3, Prospect::query()->count());
        $this->assertGreaterThanOrEqual(4, Vehicle::query()->count());
        $this->assertGreaterThanOrEqual(3, RentalPackage::query()->count());

        $this->assertDatabaseHas('quotations', [
            'quotation_number' => 'QTN/HAN/'.now()->format('Y/m').'/9001',
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('quotations', [
            'quotation_number' => 'QTN/HAN/'.now()->format('Y/m').'/9002',
            'status' => 'submitted',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'demo.seeded',
            'summary' => 'Seeded portfolio demo dataset.',
        ]);
    }

    public function test_database_seeder_is_idempotent_for_demo_quotations(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, Quotation::query()
            ->where('quotation_number', 'QTN/HAN/'.now()->format('Y/m').'/9001')
            ->count());
        $this->assertSame(1, AuditLog::query()
            ->where('action', 'demo.seeded')
            ->count());
    }
}
