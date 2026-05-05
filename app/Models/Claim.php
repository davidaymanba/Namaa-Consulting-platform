<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_no',
        'policy_id',
        'assigned_to',
        'status',
        'incident_date',
        'report_date',
        'description',
        'claimed_amount',
        'estimated_loss',
        'approved_amount',
        'paid_amount',
        'is_large_claim',
        'escalated_at',
        'last_status_update_at',
        'workflow_notes',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'report_date' => 'date',
            'claimed_amount' => 'decimal:2',
            'estimated_loss' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'is_large_claim' => 'boolean',
            'escalated_at' => 'datetime',
            'last_status_update_at' => 'datetime',
            'workflow_notes' => 'array',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClaimDocument::class);
    }
}
