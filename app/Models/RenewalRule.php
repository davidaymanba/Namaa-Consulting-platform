<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenewalRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_type',
        'notify_days_before',
        'auto_quote',
        'bulk_eligible',
        'risk_loading_pct',
        'claims_discount_pct',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'auto_quote' => 'boolean',
            'bulk_eligible' => 'boolean',
            'risk_loading_pct' => 'decimal:2',
            'claims_discount_pct' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
