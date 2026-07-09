<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RentalPackage;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationItem>
 */
class QuotationItemFactory extends Factory
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
            'vehicle_id' => Vehicle::factory(),
            'package_id' => RentalPackage::factory(),
            'quantity' => 1,
            'duration_months' => 12,
            'monthly_price' => 1000000,
            'discount_percent' => 0,
            'line_total' => 12000000,
        ];
    }
}
