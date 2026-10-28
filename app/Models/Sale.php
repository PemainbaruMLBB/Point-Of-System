<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = ['invoice_number', 'user_id', 'customer_id', 'subtotal', 'discount', 'tax', 'tax_base', 'final_tax_rate', 'final_tax_amount', 'total', 'paid_amount', 'change_amount', 'payment_method', 'tax_invoice_number'];

    protected function casts(): array
    {
        return ['subtotal' => 'decimal:2', 'discount' => 'decimal:2', 'tax' => 'decimal:2', 'tax_base' => 'decimal:2', 'final_tax_rate' => 'decimal:2', 'final_tax_amount' => 'decimal:2', 'total' => 'decimal:2', 'paid_amount' => 'decimal:2', 'change_amount' => 'decimal:2'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
