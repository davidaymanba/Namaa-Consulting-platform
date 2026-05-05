<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalFlowRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'request_type',
        'required_role',
        'min_amount',
        'max_amount',
        'sla_hours',
        'total_stages',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
