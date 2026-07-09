<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand' => fake()->randomElement(['Toyota', 'Daihatsu', 'Mitsubishi', 'Isuzu', 'Honda']),
            'model' => fake()->bothify('Model ###'),
            'vehicle_type' => fake()->randomElement(Vehicle::TYPES),
            'transmission' => fake()->randomElement(Vehicle::TRANSMISSIONS),
            'fuel_type' => fake()->randomElement(Vehicle::FUEL_TYPES),
            'seat_capacity' => fake()->numberBetween(2, 16),
            'base_monthly_price' => fake()->numberBetween(4500000, 18000000),
            'is_active' => true,
        ];
    }
}
