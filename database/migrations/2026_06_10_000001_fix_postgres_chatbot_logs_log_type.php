<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chatbot_logs')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE chatbot_logs DROP CONSTRAINT IF EXISTS chatbot_logs_log_type_check');
        DB::statement("ALTER TABLE chatbot_logs ALTER COLUMN log_type TYPE VARCHAR(50) USING log_type::text");
        DB::statement("ALTER TABLE chatbot_logs ALTER COLUMN log_type SET DEFAULT 'chat'");
        DB::statement("ALTER TABLE chatbot_logs ALTER COLUMN log_type SET NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('chatbot_logs')) {
            return;
        }

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE chatbot_logs ALTER COLUMN log_type TYPE VARCHAR(50) USING log_type::text");
        DB::statement("ALTER TABLE chatbot_logs ALTER COLUMN log_type SET DEFAULT 'chat'");
    }
};
