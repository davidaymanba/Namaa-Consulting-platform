<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReinsuranceAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'reinsurance_treaty_id',
        'reinsurance_distribution_id',
        'type',
        'message',
        'status',
        'context',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function treaty(): BelongsTo
    {
        return $this->belongsTo(ReinsuranceTreaty::class, 'reinsurance_treaty_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
