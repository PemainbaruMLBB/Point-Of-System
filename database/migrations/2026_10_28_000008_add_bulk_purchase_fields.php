<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('purchase_unit')->default('pcs')->after('quantity');
            $table->unsignedInteger('units_per_purchase')->default(1)->after('purchase_unit');
            $table->string('discount_type')->default('amount')->after('unit_cost');
            $table->decimal('discount_value', 15, 2)->default(0)->after('discount_type');
            $table->unsignedInteger('stock_quantity')->default(0)->after('discount_value');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['purchase_unit', 'units_per_purchase', 'discount_type', 'discount_value', 'stock_quantity']);
        });
    }
};
