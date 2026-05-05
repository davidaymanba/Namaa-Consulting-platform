<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimDocument extends Model
{
    use HasFactory;

    protected $fillable = ['claim_id', 'file_name', 'file_path', 'mime_type', 'size'];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}
