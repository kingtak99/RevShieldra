<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('chatbot_logs')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE chatbot_logs MODIFY log_type VARCHAR(50) NOT NULL DEFAULT 'chat'");
            } else {
                Schema::table('chatbot_logs', function (Blueprint $table) {
                    $table->string('log_type', 50)->default('chat')->change();
                });
            }

            if (!Schema::hasColumn('chatbot_logs', 'metadata')) {
                Schema::table('chatbot_logs', function (Blueprint $table) {
                    $table->json('metadata')->nullable()->after('log_type');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('chatbot_logs')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE chatbot_logs MODIFY log_type ENUM('chat', 'ticket_request', 'flow_navigation', 'flow_detection') DEFAULT 'chat'");
            }

            if (Schema::hasColumn('chatbot_logs', 'metadata')) {
                Schema::table('chatbot_logs', function (Blueprint $table) {
                    $table->dropColumn('metadata');
                });
            }
        }
    }
};
