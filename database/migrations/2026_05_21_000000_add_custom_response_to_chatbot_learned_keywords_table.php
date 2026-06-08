<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_learned_keywords', function (Blueprint $table) {
            $table->text('custom_response')->nullable()->after('target_branch');
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_learned_keywords', function (Blueprint $table) {
            $table->dropColumn('custom_response');
        });
    }
};
