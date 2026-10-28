<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('tax_identity', 32)->nullable()->after('phone');
            $table->string('tax_identity_type', 10)->nullable()->after('tax_identity');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('tax_base', 15, 2)->default(0)->after('discount');
            $table->decimal('final_tax_rate', 5, 2)->default(0.50)->after('tax_base');
            $table->decimal('final_tax_amount', 15, 2)->default(0)->after('final_tax_rate');
            $table->string('tax_invoice_number')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['tax_base', 'final_tax_rate', 'final_tax_amount', 'tax_invoice_number']);
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['tax_identity', 'tax_identity_type']);
        });
    }
};
