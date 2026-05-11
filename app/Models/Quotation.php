<?php

namespace App\Models;

use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use HasFactory;

    public const STATUSES = [
        'draft',
        'submitted',
        'approved',
        'rejected',
        'revised',
        'sent',
        'accepted',
        'declined',
        'expired',
    ];

    protected $fillable = [
        'quotation_number',
        'prospect_id',
        'contact_id',
        'sales_id',
        'approved_by',
        'quotation_date',
        'valid_until',
        'status',
        'subtotal',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'grand_total',
        'terms_and_conditions',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(ProspectContact::class, 'contact_id');
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
