<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\RentalPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = $this->seedUsers();
        $vehicles = $this->seedVehicles();
        $packages = $this->seedRentalPackages();
        $prospects = $this->seedProspects($users);

        $this->seedFollowUps($users, $prospects);
        $this->seedQuotations($users, $prospects, $vehicles, $packages);
        $this->seedAuditLogs($users, $prospects);
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(): array
    {
        $users = [
            'admin' => ['name' => 'Hana Admin', 'email' => 'admin@example.com', 'role' => UserRole::Admin],
            'sales' => ['name' => 'Satria Sales', 'email' => 'sales@example.com', 'role' => UserRole::Sales],
            'manager' => ['name' => 'Maya Manager', 'email' => 'manager@example.com', 'role' => UserRole::Manager],
            'finance' => ['name' => 'Raka Finance', 'email' => 'finance@example.com', 'role' => UserRole::Finance],
        ];

        return collect($users)
            ->map(fn (array $user): User => User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            ))
            ->all();
    }

    /**
     * @return array<string, Vehicle>
     */
    private function seedVehicles(): array
    {
        $vehicles = [
            'avanza' => ['brand' => 'Toyota', 'model' => 'Avanza 1.5 G', 'vehicle_type' => 'mpv', 'transmission' => 'automatic', 'fuel_type' => 'gasoline', 'seat_capacity' => 7, 'base_monthly_price' => 6200000],
            'innova' => ['brand' => 'Toyota', 'model' => 'Innova Zenix Hybrid', 'vehicle_type' => 'mpv', 'transmission' => 'automatic', 'fuel_type' => 'hybrid', 'seat_capacity' => 7, 'base_monthly_price' => 11200000],
            'triton' => ['brand' => 'Mitsubishi', 'model' => 'Triton DC 4x4', 'vehicle_type' => 'commercial', 'transmission' => 'manual', 'fuel_type' => 'diesel', 'seat_capacity' => 5, 'base_monthly_price' => 13800000],
            'elf' => ['brand' => 'Isuzu', 'model' => 'Elf NLR Box', 'vehicle_type' => 'box', 'transmission' => 'manual', 'fuel_type' => 'diesel', 'seat_capacity' => 3, 'base_monthly_price' => 15500000],
        ];

        return collect($vehicles)
            ->map(fn (array $vehicle): Vehicle => Vehicle::query()->updateOrCreate(
                [
                    'brand' => $vehicle['brand'],
                    'model' => $vehicle['model'],
                    'vehicle_type' => $vehicle['vehicle_type'],
                ],
                $vehicle + ['is_active' => true],
            ))
            ->all();
    }

    /**
     * @return array<string, RentalPackage>
     */
    private function seedRentalPackages(): array
    {
        $packages = [
            'rental_only' => ['name' => 'Corporate Rental Only 12M', 'description' => 'Unit rental for corporate operational fleets.', 'duration_months' => 12, 'includes_driver' => false, 'includes_maintenance' => true, 'includes_insurance' => true],
            'driver' => ['name' => 'Fleet + Driver 24M', 'description' => 'Long-term rental including professional driver and maintenance.', 'duration_months' => 24, 'includes_driver' => true, 'includes_maintenance' => true, 'includes_insurance' => true],
            'logistics' => ['name' => 'Logistics Box Fleet 36M', 'description' => 'Commercial box fleet package for route operations.', 'duration_months' => 36, 'includes_driver' => false, 'includes_maintenance' => true, 'includes_insurance' => true],
        ];

        return collect($packages)
            ->map(fn (array $package): RentalPackage => RentalPackage::query()->updateOrCreate(
                ['name' => $package['name']],
                $package + ['is_active' => true],
            ))
            ->all();
    }

    /**
     * @param  array<string, User>  $users
     * @return array<string, Prospect>
     */
    private function seedProspects(array $users): array
    {
        $prospects = [
            'nusantara' => [
                'company_name' => 'PT Nusantara Retailindo',
                'industry' => 'Retail',
                'company_size' => 'enterprise',
                'address' => 'Jl. Gatot Subroto Kav. 18',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'website' => 'https://nusantara-retail.example',
                'source' => 'referral',
                'status' => 'quotation',
                'priority' => 'high',
                'estimated_vehicle_need' => 18,
                'assigned_sales_id' => $users['sales']->id,
                'next_follow_up_at' => now()->addDays(2),
                'notes' => 'Needs MPV fleet replacement for branch operations.',
                'contacts' => [
                    ['name' => 'Dewi Paramita', 'position' => 'Procurement Manager', 'email' => 'dewi.paramita@example.com', 'phone' => '+62 812 1000 2000', 'is_primary' => true],
                    ['name' => 'Agus Pranata', 'position' => 'Operations Lead', 'email' => 'agus.pranata@example.com', 'phone' => '+62 812 1000 3000', 'is_primary' => false],
                ],
            ],
            'samudra' => [
                'company_name' => 'PT Samudra Logistik Prima',
                'industry' => 'Logistics',
                'company_size' => 'enterprise',
                'address' => 'Kawasan Industri MM2100',
                'city' => 'Bekasi',
                'province' => 'Jawa Barat',
                'website' => 'https://samudra-logistik.example',
                'source' => 'event',
                'status' => 'negotiation',
                'priority' => 'high',
                'estimated_vehicle_need' => 12,
                'assigned_sales_id' => $users['sales']->id,
                'next_follow_up_at' => now()->subDay(),
                'notes' => 'Evaluating box fleet for distribution route expansion.',
                'contacts' => [
                    ['name' => 'Bimo Santoso', 'position' => 'Fleet Procurement', 'email' => 'bimo.santoso@example.com', 'phone' => '+62 813 4000 5000', 'is_primary' => true],
                ],
            ],
            'cakrawala' => [
                'company_name' => 'PT Cakrawala Mining Services',
                'industry' => 'Mining',
                'company_size' => 'medium',
                'address' => 'Jl. Mulawarman No. 77',
                'city' => 'Balikpapan',
                'province' => 'Kalimantan Timur',
                'website' => 'https://cakrawala-mining.example',
                'source' => 'cold_email',
                'status' => 'meeting',
                'priority' => 'medium',
                'estimated_vehicle_need' => 8,
                'assigned_sales_id' => $users['sales']->id,
                'next_follow_up_at' => now()->addWeek(),
                'notes' => 'Interested in 4x4 units for site mobility.',
                'contacts' => [
                    ['name' => 'Rizky Wibowo', 'position' => 'General Affairs', 'email' => 'rizky.wibowo@example.com', 'phone' => '+62 811 7000 8000', 'is_primary' => true],
                ],
            ],
        ];

        return collect($prospects)
            ->map(function (array $prospect): Prospect {
                $contacts = $prospect['contacts'];
                unset($prospect['contacts']);

                $model = Prospect::query()->updateOrCreate(
                    ['company_name' => $prospect['company_name']],
                    $prospect,
                );

                foreach ($contacts as $contact) {
                    $model->contacts()->updateOrCreate(
                        ['email' => $contact['email']],
                        $contact,
                    );
                }

                return $model;
            })
            ->all();
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Prospect>  $prospects
     */
    private function seedFollowUps(array $users, array $prospects): void
    {
        $activities = [
            ['prospect' => 'nusantara', 'activity_type' => 'meeting', 'activity_date' => now()->subDays(4), 'summary' => 'Procurement meeting for MPV replacement plan.', 'detail' => 'Customer requested quotation with maintenance and insurance included.', 'next_follow_up_at' => now()->addDays(2), 'outcome' => 'positive'],
            ['prospect' => 'samudra', 'activity_type' => 'proposal_sent', 'activity_date' => now()->subDays(8), 'summary' => 'Sent revised box fleet proposal.', 'detail' => 'Waiting for internal finance comparison against purchase option.', 'next_follow_up_at' => now()->subDay(), 'outcome' => 'neutral'],
            ['prospect' => 'cakrawala', 'activity_type' => 'call', 'activity_date' => now()->subDays(2), 'summary' => 'Confirmed site visit schedule.', 'detail' => 'Technical team wants to inspect unit specification.', 'next_follow_up_at' => now()->addWeek(), 'outcome' => 'positive'],
        ];

        foreach ($activities as $activity) {
            $prospect = $prospects[$activity['prospect']];
            $contact = $prospect->contacts()->where('is_primary', true)->first();

            FollowUpActivity::query()->updateOrCreate(
                [
                    'prospect_id' => $prospect->id,
                    'summary' => $activity['summary'],
                ],
                [
                    'contact_id' => $contact?->id,
                    'user_id' => $users['sales']->id,
                    'activity_type' => $activity['activity_type'],
                    'activity_date' => $activity['activity_date'],
                    'detail' => $activity['detail'],
                    'next_follow_up_at' => $activity['next_follow_up_at'],
                    'outcome' => $activity['outcome'],
                ],
            );
        }
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Prospect>  $prospects
     * @param  array<string, Vehicle>  $vehicles
     * @param  array<string, RentalPackage>  $packages
     */
    private function seedQuotations(array $users, array $prospects, array $vehicles, array $packages): void
    {
        $this->seedQuotation(
            number: 'QTN/HAN/'.now()->format('Y/m').'/9001',
            prospect: $prospects['nusantara'],
            sales: $users['sales'],
            manager: $users['manager'],
            status: 'approved',
            items: [
                ['vehicle' => $vehicles['innova'], 'package' => $packages['driver'], 'quantity' => 6, 'duration_months' => 24, 'monthly_price' => 11200000, 'discount_percent' => 5],
                ['vehicle' => $vehicles['avanza'], 'package' => $packages['rental_only'], 'quantity' => 8, 'duration_months' => 12, 'monthly_price' => 6200000, 'discount_percent' => 3],
            ],
        );

        $this->seedQuotation(
            number: 'QTN/HAN/'.now()->format('Y/m').'/9002',
            prospect: $prospects['samudra'],
            sales: $users['sales'],
            manager: null,
            status: 'submitted',
            items: [
                ['vehicle' => $vehicles['elf'], 'package' => $packages['logistics'], 'quantity' => 5, 'duration_months' => 36, 'monthly_price' => 15500000, 'discount_percent' => 4],
            ],
        );

        $this->seedQuotation(
            number: 'QTN/HAN/'.now()->format('Y/m').'/9003',
            prospect: $prospects['cakrawala'],
            sales: $users['sales'],
            manager: null,
            status: 'draft',
            items: [
                ['vehicle' => $vehicles['triton'], 'package' => $packages['rental_only'], 'quantity' => 4, 'duration_months' => 12, 'monthly_price' => 13800000, 'discount_percent' => 0],
            ],
        );
    }

    /**
     * @param  list<array{vehicle: Vehicle, package: RentalPackage, quantity: int, duration_months: int, monthly_price: int, discount_percent: int}>  $items
     */
    private function seedQuotation(string $number, Prospect $prospect, User $sales, ?User $manager, string $status, array $items): void
    {
        $subtotal = collect($items)->sum(fn (array $item): float => $this->lineTotal($item));
        $discountAmount = $status === 'approved' ? 5000000 : 0;
        $taxPercent = 11;
        $taxAmount = ($subtotal - $discountAmount) * ($taxPercent / 100);
        $grandTotal = $subtotal - $discountAmount + $taxAmount;
        $contact = $prospect->contacts()->where('is_primary', true)->first();

        $quotation = Quotation::query()->updateOrCreate(
            ['quotation_number' => $number],
            [
                'prospect_id' => $prospect->id,
                'contact_id' => $contact?->id,
                'sales_id' => $sales->id,
                'approved_by' => $status === 'approved' ? $manager?->id : null,
                'quotation_date' => now()->toDateString(),
                'valid_until' => now()->addDays(14)->toDateString(),
                'status' => $status,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'terms_and_conditions' => "Prices include standard maintenance and replacement unit support.\nQuotation is valid for 14 calendar days.",
                'internal_notes' => 'Seeded portfolio quotation.',
                'pdf_path' => null,
                'pdf_generated_at' => null,
            ],
        );

        $quotation->items()->delete();

        foreach ($items as $item) {
            $quotation->items()->create([
                'vehicle_id' => $item['vehicle']->id,
                'package_id' => $item['package']->id,
                'quantity' => $item['quantity'],
                'duration_months' => $item['duration_months'],
                'monthly_price' => $item['monthly_price'],
                'discount_percent' => $item['discount_percent'],
                'line_total' => $this->lineTotal($item),
            ]);
        }

        $quotation->approvals()->delete();

        if ($status === 'submitted' || $status === 'approved') {
            $quotation->approvals()->create([
                'user_id' => $sales->id,
                'action' => 'submit',
                'from_status' => 'draft',
                'to_status' => 'submitted',
            ]);
        }

        if ($status === 'approved' && $manager) {
            $quotation->approvals()->create([
                'user_id' => $manager->id,
                'action' => 'approve',
                'from_status' => 'submitted',
                'to_status' => 'approved',
            ]);
        }
    }

    /**
     * @param  array{quantity: int, duration_months: int, monthly_price: int, discount_percent: int}  $item
     */
    private function lineTotal(array $item): float
    {
        $gross = $item['quantity'] * $item['duration_months'] * $item['monthly_price'];

        return $gross - ($gross * ($item['discount_percent'] / 100));
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Prospect>  $prospects
     */
    private function seedAuditLogs(array $users, array $prospects): void
    {
        AuditLog::query()->updateOrCreate(
            [
                'action' => 'demo.seeded',
                'summary' => 'Seeded portfolio demo dataset.',
            ],
            [
                'user_id' => $users['admin']->id,
                'auditable_type' => Prospect::class,
                'auditable_id' => $prospects['nusantara']->id,
                'new_values' => [
                    'prospects' => Prospect::query()->count(),
                    'quotations' => Quotation::query()->count(),
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'DatabaseSeeder',
            ],
        );
    }
}
