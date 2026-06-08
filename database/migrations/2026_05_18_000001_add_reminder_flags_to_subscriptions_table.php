<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('reminder_7_days_sent')->default(false)->after('current_period_end');
            $table->boolean('reminder_1_day_sent')->default(false)->after('reminder_7_days_sent');
            $table->boolean('expiry_notice_sent')->default(false)->after('reminder_1_day_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['reminder_7_days_sent', 'reminder_1_day_sent', 'expiry_notice_sent']);
        });
    }
};
