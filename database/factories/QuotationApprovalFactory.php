<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\QuotationApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationApproval>
 */
class QuotationApprovalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quotation_id' => Quotation::factory(),
            'user_id' => User::factory(),
            'action' => fake()->randomElement(QuotationApproval::ACTIONS),
            'from_status' => 'draft',
            'to_status' => 'submitted',
            'reason' => null,
        ];
    }
}
