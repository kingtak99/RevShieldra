<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('chatbot_logs', 'metadata')) {
            Schema::table('chatbot_logs', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('log_type');
            });
        }

        // Update log_type enum to include flow_navigation
        if (Schema::hasColumn('chatbot_logs', 'log_type')) {
            // SQLite doesn't support modifying enums, so we'll keep as is
            // For MySQL, you would need a different approach
        }
    }

    public function down(): void
    {
        Schema::table('chatbot_logs', function (Blueprint $table) {
            if (Schema::hasColumn('chatbot_logs', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
