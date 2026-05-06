<?php

namespace Database\Factories;

use App\Models\FollowUpActivity;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FollowUpActivity>
 */
class FollowUpActivityFactory extends Factory
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
            'contact_id' => null,
            'user_id' => User::factory(),
            'activity_type' => fake()->randomElement(FollowUpActivity::TYPES),
            'activity_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'summary' => fake()->sentence(6),
            'detail' => fake()->optional()->paragraph(),
            'next_follow_up_at' => fake()->optional()->dateTimeBetween('now', '+14 days'),
            'outcome' => fake()->optional()->randomElement(FollowUpActivity::OUTCOMES),
        ];
    }
}
