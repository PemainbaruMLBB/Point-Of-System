<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'sale_unit_id', 'quantity', 'unit_price', 'discount', 'total'];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(ProductSaleUnit::class, 'sale_unit_id');
    }
}
