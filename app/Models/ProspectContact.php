<?php

namespace App\Models;

use Database\Factories\ProspectContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectContact extends Model
{
    /** @use HasFactory<ProspectContactFactory> */
    use HasFactory;

    protected $fillable = [
        'prospect_id',
        'name',
        'position',
        'email',
        'phone',
        'linkedin_url',
        'is_primary',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }
}
