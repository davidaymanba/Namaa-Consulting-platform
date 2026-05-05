<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyInstallment extends Model
{
    use HasFactory;

    protected $fillable = ['policy_id', 'sequence', 'due_date', 'amount', 'status', 'paid_at', 'reminder_count', 'last_reminded_at', 'overdue_days'];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_at' => 'date',
            'last_reminded_at' => 'datetime',
            'amount' => 'decimal:2',
            'reminder_count' => 'integer',
            'overdue_days' => 'integer',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
