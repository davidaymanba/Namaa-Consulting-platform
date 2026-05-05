<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'record_id',
        'request_type',
        'required_role',
        'current_stage',
        'total_stages',
        'sla_hours',
        'due_at',
        'escalated_at',
        'status',
        'requested_by',
        'approved_by',
        'reason',
        'context',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'due_at' => 'datetime',
            'escalated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
