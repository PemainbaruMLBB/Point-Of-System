<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'product_id', 'quantity', 'purchase_unit', 'units_per_purchase', 'unit_cost', 'discount_type', 'discount_value', 'stock_quantity', 'total'];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
