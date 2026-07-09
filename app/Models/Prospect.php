<?php

namespace App\Models;

use Database\Factories\ProspectFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    /** @use HasFactory<ProspectFactory> */
    use HasFactory, SoftDeletes;

    public const COMPANY_SIZES = ['small', 'medium', 'enterprise'];

    public const SOURCES = ['linkedin', 'referral', 'cold_email', 'website', 'event', 'manual'];

    public const STATUSES = ['new', 'contacted', 'meeting', 'quotation', 'negotiation', 'won', 'lost'];

    public const PRIORITIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'company_name',
        'industry',
        'company_size',
        'address',
        'city',
        'province',
        'website',
        'source',
        'status',
        'priority',
        'estimated_vehicle_need',
        'assigned_sales_id',
        'next_follow_up_at',
        'lost_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_vehicle_need' => 'integer',
            'next_follow_up_at' => 'datetime',
        ];
    }

    public function assignedSales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_sales_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ProspectContact::class);
    }

    public function primaryContact(): HasMany
    {
        return $this->contacts()->where('is_primary', true);
    }

    protected function companyName(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? trim($value) : $value,
        );
    }
}
