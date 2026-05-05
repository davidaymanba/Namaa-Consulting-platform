<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReinsuranceTreaty extends Model
{
    use HasFactory;

    protected $fillable = [
        'reinsurer',
        'type',
        'share_pct',
        'retention',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
            'share_pct' => 'decimal:2',
            'retention' => 'decimal:2',
        ];
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(ReinsuranceDistribution::class);
    }
}
