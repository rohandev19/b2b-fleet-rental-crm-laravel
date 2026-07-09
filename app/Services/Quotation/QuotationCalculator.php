<?php

namespace App\Services\Quotation;

class QuotationCalculator
{
    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{subtotal: float, discount_amount: float, tax_percent: float, tax_amount: float, grand_total: float, items: list<array<string, mixed>>}
     */
    public function calculate(array $items, float $discountAmount = 0, float $taxPercent = 11): array
    {
        $calculatedItems = array_map(fn (array $item): array => $this->calculateItem($item), $items);
        $subtotal = array_sum(array_column($calculatedItems, 'line_total'));
        $taxableAmount = max($subtotal - $discountAmount, 0);
        $taxAmount = round($taxableAmount * $taxPercent / 100, 2);
        $grandTotal = round($taxableAmount + $taxAmount, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_percent' => round($taxPercent, 2),
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'items' => $calculatedItems,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    public function calculateItem(array $item): array
    {
        $quantity = (int) $item['quantity'];
        $durationMonths = (int) $item['duration_months'];
        $monthlyPrice = (float) $item['monthly_price'];
        $discountPercent = (float) ($item['discount_percent'] ?? 0);

        $baseTotal = $quantity * $durationMonths * $monthlyPrice;
        $itemDiscount = $baseTotal * $discountPercent / 100;

        return array_merge($item, [
            'discount_percent' => round($discountPercent, 2),
            'line_total' => round($baseTotal - $itemDiscount, 2),
        ]);
    }
}
