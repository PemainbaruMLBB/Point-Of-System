<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSaleUnit extends Model
{
    protected $fillable = ['product_id', 'name', 'conversion', 'selling_price', 'is_default', 'is_active'];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
