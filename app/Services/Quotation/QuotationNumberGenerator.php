<?php

namespace App\Services\Quotation;

use App\Models\Quotation;
use Illuminate\Support\Carbon;

class QuotationNumberGenerator
{
    public function generate(?Carbon $date = null): string
    {
        $date ??= now();
        $prefix = sprintf('QTN/HAN/%s/%s', $date->format('Y'), $date->format('m'));

        $latestNumber = Quotation::query()
            ->where('quotation_number', 'like', $prefix.'/%')
            ->lockForUpdate()
            ->orderByDesc('quotation_number')
            ->value('quotation_number');

        $sequence = $latestNumber
            ? ((int) str($latestNumber)->afterLast('/')->toString()) + 1
            : 1;

        return sprintf('%s/%04d', $prefix, $sequence);
    }
}
