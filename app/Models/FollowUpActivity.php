<?php

namespace App\Models;

use Database\Factories\FollowUpActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUpActivity extends Model
{
    /** @use HasFactory<FollowUpActivityFactory> */
    use HasFactory;

    public const TYPES = [
        'call',
        'email',
        'linkedin_message',
        'meeting',
        'site_visit',
        'proposal_sent',
        'negotiation',
        'internal_note',
    ];

    public const OUTCOMES = ['positive', 'neutral', 'negative', 'no_response'];

    protected $fillable = [
        'prospect_id',
        'contact_id',
        'user_id',
        'activity_type',
        'activity_date',
        'summary',
        'detail',
        'next_follow_up_at',
        'outcome',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'datetime',
            'next_follow_up_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
