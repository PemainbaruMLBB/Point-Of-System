<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('membership_level')->default('general')->after('name');
            $table->string('telegram_chat_id')->nullable()->after('phone');
            $table->boolean('telegram_opt_in')->default(false)->after('telegram_chat_id');
            $table->index(['membership_level', 'telegram_opt_in']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['membership_level', 'telegram_opt_in']);
            $table->dropColumn(['membership_level', 'telegram_chat_id', 'telegram_opt_in']);
        });
    }
};
