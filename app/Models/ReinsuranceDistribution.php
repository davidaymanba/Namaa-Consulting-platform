<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReinsuranceDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'reinsurance_treaty_id',
        'policy_premium',
        'ri_share_amount',
        'claims_recovered',
    ];

    protected function casts(): array
    {
        return [
            'policy_premium' => 'decimal:2',
            'ri_share_amount' => 'decimal:2',
            'claims_recovered' => 'decimal:2',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function treaty(): BelongsTo
    {
        return $this->belongsTo(ReinsuranceTreaty::class, 'reinsurance_treaty_id');
    }
}
