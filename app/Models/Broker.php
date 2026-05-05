<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broker extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'contact', 'email', 'commission_rates'];

    protected function casts(): array
    {
        return [
            'commission_rates' => 'array',
        ];
    }

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class);
    }
}
