<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE sales SET tax_base = GREATEST(subtotal - discount, 0), final_tax_rate = 0.50, final_tax_amount = ROUND(GREATEST(subtotal - discount, 0) * 0.005, 2)');
    }

    public function down(): void
    {
        DB::table('sales')->update(['tax_base' => 0, 'final_tax_amount' => 0]);
    }
};
