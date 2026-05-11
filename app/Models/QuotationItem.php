<?php

namespace App\Models;

use Database\Factories\QuotationItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    /** @use HasFactory<QuotationItemFactory> */
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'vehicle_id',
        'package_id',
        'quantity',
        'duration_months',
        'monthly_price',
        'discount_percent',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'duration_months' => 'integer',
            'monthly_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(RentalPackage::class, 'package_id');
    }
}
