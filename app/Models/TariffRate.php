<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffRate extends Model
{
    use HasFactory;

    protected $fillable = ['insurance_type', 'base_rate', 'min_premium', 'rules', 'is_active'];

    protected function casts(): array
    {
        return [
            'rules' => 'array',
            'is_active' => 'boolean',
            'base_rate' => 'decimal:4',
            'min_premium' => 'decimal:2',
        ];
    }
}
