<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to modify the enum
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chatbot_logs MODIFY log_type ENUM('chat', 'ticket_request', 'flow_navigation', 'flow_detection') DEFAULT 'chat'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE chatbot_logs MODIFY log_type ENUM('chat', 'ticket_request') DEFAULT 'chat'");
        }
    }
};
