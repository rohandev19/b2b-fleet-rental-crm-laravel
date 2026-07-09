<?php

namespace Database\Factories;

use App\Models\RentalPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalPackage>
 */
class RentalPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Rental Only',
                'Rental + Driver',
                'Rental + Driver + Maintenance',
                'Corporate Monthly Rental',
                'Long Term Fleet Contract',
            ]).' '.fake()->unique()->numberBetween(1, 999),
            'description' => fake()->sentence(),
            'duration_months' => fake()->randomElement([1, 3, 6, 12, 24, 36]),
            'includes_driver' => fake()->boolean(),
            'includes_maintenance' => fake()->boolean(),
            'includes_insurance' => fake()->boolean(),
            'is_active' => true,
        ];
    }
}
