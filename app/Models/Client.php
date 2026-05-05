<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'national_id',
        'contact',
        'email',
        'address',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class);
    }

    public function claims(): HasManyThrough
    {
        return $this->hasManyThrough(Claim::class, Policy::class);
    }
}
