<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BordereauxBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_start',
        'period_end',
        'file_name',
        'file_path',
        'status',
        'total_records',
        'total_ri_share',
        'sent_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'sent_at' => 'datetime',
            'total_ri_share' => 'decimal:2',
        ];
    }
}
