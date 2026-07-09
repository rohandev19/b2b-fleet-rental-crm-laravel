<?php

namespace Database\Factories;

use App\Models\Prospect;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quotation_number' => 'QTN/HAN/'.now()->format('Y/m').'/'.fake()->unique()->numberBetween(1000, 9999),
            'prospect_id' => Prospect::factory(),
            'contact_id' => null,
            'sales_id' => User::factory(),
            'quotation_date' => now()->toDateString(),
            'valid_until' => now()->addDays(14)->toDateString(),
            'status' => 'draft',
            'subtotal' => 12000000,
            'discount_amount' => 0,
            'tax_percent' => 11,
            'tax_amount' => 1320000,
            'grand_total' => 13320000,
            'terms_and_conditions' => 'Standard quotation terms.',
            'internal_notes' => null,
        ];
    }
}
