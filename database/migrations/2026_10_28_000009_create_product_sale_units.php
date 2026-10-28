<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sale_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 20);
            $table->unsignedInteger('conversion')->default(1);
            $table->decimal('selling_price', 15, 2);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['product_id', 'name']);
            $table->index(['product_id', 'is_active']);
        });

        foreach (DB::table('products')->select(['id', 'unit', 'selling_price'])->get() as $product) {
            DB::table('product_sale_units')->insert([
                'product_id' => $product->id,
                'name' => $product->unit,
                'conversion' => 1,
                'selling_price' => $product->selling_price,
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sale_units');
    }
};
