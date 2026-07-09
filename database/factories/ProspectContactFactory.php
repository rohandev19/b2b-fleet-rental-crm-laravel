<?php

namespace Database\Factories;

use App\Models\Prospect;
use App\Models\ProspectContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProspectContact>
 */
class ProspectContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prospect_id' => Prospect::factory(),
            'name' => fake()->name(),
            'position' => fake()->randomElement(['Procurement', 'Purchasing', 'General Affair', 'HR', 'Operation', 'Director']),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'linkedin_url' => fake()->optional()->url(),
            'is_primary' => false,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
