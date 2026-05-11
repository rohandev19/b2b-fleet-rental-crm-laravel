<?php

namespace Tests\Unit;

use App\Services\Quotation\QuotationCalculator;
use PHPUnit\Framework\TestCase;

class QuotationCalculatorTest extends TestCase
{
    public function test_it_calculates_item_and_grand_total(): void
    {
        $calculator = new QuotationCalculator;

        $result = $calculator->calculate([
            [
                'vehicle_id' => 1,
                'package_id' => 1,
                'quantity' => 2,
                'duration_months' => 12,
                'monthly_price' => 5000000,
                'discount_percent' => 10,
            ],
        ], 8000000, 11);

        $this->assertSame(108000000.0, $result['subtotal']);
        $this->assertSame(8000000.0, $result['discount_amount']);
        $this->assertSame(11000000.0, $result['tax_amount']);
        $this->assertSame(111000000.0, $result['grand_total']);
        $this->assertSame(108000000.0, $result['items'][0]['line_total']);
    }
}
