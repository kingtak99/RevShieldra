<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('user_email')->nullable();
            $table->string('session_id');
            $table->enum('sender', ['user', 'bot', 'system']);
            $table->text('message');
            $table->string('language', 2)->default('ar');
            $table->enum('log_type', ['chat', 'ticket_request'])->default('chat');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_logs');
    }
};
