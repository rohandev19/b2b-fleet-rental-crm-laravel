<?php

namespace App\Models;

use Database\Factories\QuotationApprovalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationApproval extends Model
{
    /** @use HasFactory<QuotationApprovalFactory> */
    use HasFactory;

    public const ACTIONS = ['submit', 'approve', 'reject'];

    protected $fillable = [
        'quotation_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'reason',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
