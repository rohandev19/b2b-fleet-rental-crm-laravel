<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\RentalPackage;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_vehicle_index(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Vehicle::factory()->create(['brand' => 'Toyota', 'model' => 'Avanza']);

        $this->actingAs($admin)
            ->get('/vehicles')
            ->assertOk()
            ->assertSee('Toyota Avanza');
    }

    public function test_manager_can_view_vehicle_index_but_cannot_create_vehicle(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $this->actingAs($manager)
            ->get('/vehicles')
            ->assertOk();

        $this->actingAs($manager)
            ->post('/vehicles', $this->validVehiclePayload())
            ->assertForbidden();
    }

    public function test_sales_cannot_view_vehicle_index(): void
    {
        $sales = User::factory()->create(['role' => UserRole::Sales]);

        $this->actingAs($sales)
            ->get('/vehicles')
            ->assertForbidden();
    }

    public function test_admin_can_create_vehicle(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post('/vehicles', $this->validVehiclePayload([
            'brand' => 'Toyota',
            'model' => 'Innova',
        ]));

        $response->assertRedirect('/vehicles');

        $this->assertDatabaseHas('vehicles', [
            'brand' => 'Toyota',
            'model' => 'Innova',
            'vehicle_type' => 'mpv',
            'is_active' => true,
        ]);
    }

    public function test_vehicle_base_price_must_be_greater_than_zero(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post('/vehicles', $this->validVehiclePayload(['base_monthly_price' => 0]))
            ->assertSessionHasErrors('base_monthly_price');
    }

    public function test_vehicle_brand_model_type_combination_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Vehicle::factory()->create([
            'brand' => 'Toyota',
            'model' => 'Hiace',
            'vehicle_type' => 'commercial',
        ]);

        $this->actingAs($admin)
            ->post('/vehicles', $this->validVehiclePayload([
                'brand' => 'Toyota',
                'model' => 'Hiace',
                'vehicle_type' => 'commercial',
            ]))
            ->assertSessionHasErrors('vehicle_type');
    }

    public function test_admin_can_toggle_vehicle_active_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $vehicle = Vehicle::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch("/vehicles/{$vehicle->id}/toggle-active")
            ->assertRedirect();

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_create_rental_package(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->post('/rental-packages', $this->validPackagePayload([
            'name' => 'Rental + Driver',
            'includes_driver' => '1',
        ]));

        $response->assertRedirect('/rental-packages');

        $this->assertDatabaseHas('rental_packages', [
            'name' => 'Rental + Driver',
            'duration_months' => 12,
            'includes_driver' => true,
            'is_active' => true,
        ]);
    }

    public function test_rental_package_duration_must_be_positive(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post('/rental-packages', $this->validPackagePayload(['duration_months' => 0]))
            ->assertSessionHasErrors('duration_months');
    }

    public function test_manager_can_view_rental_packages_but_cannot_create(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        RentalPackage::factory()->create(['name' => 'Corporate Monthly']);

        $this->actingAs($manager)
            ->get('/rental-packages')
            ->assertOk()
            ->assertSee('Corporate Monthly');

        $this->actingAs($manager)
            ->post('/rental-packages', $this->validPackagePayload())
            ->assertForbidden();
    }

    public function test_admin_can_toggle_rental_package_active_status(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $rentalPackage = RentalPackage::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch("/rental-packages/{$rentalPackage->id}/toggle-active")
            ->assertRedirect();

        $this->assertDatabaseHas('rental_packages', [
            'id' => $rentalPackage->id,
            'is_active' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validVehiclePayload(array $overrides = []): array
    {
        return array_merge([
            'brand' => 'Toyota',
            'model' => 'Avanza',
            'vehicle_type' => 'mpv',
            'transmission' => 'automatic',
            'fuel_type' => 'gasoline',
            'seat_capacity' => 7,
            'base_monthly_price' => 6500000,
            'is_active' => '1',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPackagePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Corporate Monthly Rental',
            'description' => 'Standard corporate monthly package.',
            'duration_months' => 12,
            'includes_driver' => '0',
            'includes_maintenance' => '1',
            'includes_insurance' => '1',
            'is_active' => '1',
        ], $overrides);
    }
}
