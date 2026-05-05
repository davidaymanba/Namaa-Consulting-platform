<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_no',
        'client_id',
        'broker_id',
        'branch_id',
        'issued_by',
        'type',
        'status',
        'premium',
        'discount_pct',
        'loading_pct',
        'net_premium',
        'start_date',
        'end_date',
        'issued_at',
        'data',
        'archived_at',
        'renewed_from_policy_id',
        'renewal_notified_at',
        'renewal_quoted_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'issued_at' => 'datetime',
            'archived_at' => 'datetime',
            'renewal_notified_at' => 'datetime',
            'renewal_quoted_at' => 'datetime',
            'premium' => 'decimal:2',
            'net_premium' => 'decimal:2',
            'discount_pct' => 'decimal:2',
            'loading_pct' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PolicyInstallment::class);
    }

    public function reinsuranceDistributions(): HasMany
    {
        return $this->hasMany(ReinsuranceDistribution::class);
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_policy_id');
    }
}
