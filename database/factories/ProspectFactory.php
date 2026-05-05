<?php

namespace Database\Factories;

use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prospect>
 */
class ProspectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'industry' => fake()->randomElement(['Manufacturing', 'Logistics', 'Retail', 'FMCG', 'Construction']),
            'company_size' => fake()->randomElement(Prospect::COMPANY_SIZES),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'website' => fake()->url(),
            'source' => fake()->randomElement(Prospect::SOURCES),
            'status' => fake()->randomElement(['new', 'contacted', 'meeting', 'quotation', 'negotiation']),
            'priority' => fake()->randomElement(Prospect::PRIORITIES),
            'estimated_vehicle_need' => fake()->numberBetween(1, 30),
            'assigned_sales_id' => User::factory(),
            'next_follow_up_at' => fake()->optional()->dateTimeBetween('now', '+30 days'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
