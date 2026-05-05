<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_installment_id',
        'channel',
        'days_offset',
        'status',
        'scheduled_for',
        'sent_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PolicyInstallment::class, 'policy_installment_id');
    }
}
