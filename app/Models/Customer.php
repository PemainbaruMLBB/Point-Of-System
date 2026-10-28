<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'membership_level', 'phone', 'telegram_chat_id', 'telegram_opt_in', 'tax_identity', 'tax_identity_type', 'address'];

    protected function casts(): array
    {
        return ['telegram_opt_in' => 'boolean'];
    }

    public function discountPercent(): float
    {
        return match ($this->membership_level) {
            'member' => 3.0,
            'vip' => 5.0,
            default => 0.0,
        };
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
