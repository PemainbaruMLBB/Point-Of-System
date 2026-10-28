<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('role')->constrained('users')->nullOnDelete();
            $table->timestamp('activated_at')->nullable()->after('created_by_user_id');
            $table->index(['role', 'activated_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('type')->default('product')->after('name');
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['type', 'is_active']);
            $table->dropColumn('type');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropIndex(['role', 'activated_at']);
            $table->dropColumn(['created_by_user_id', 'activated_at']);
        });
    }
};
