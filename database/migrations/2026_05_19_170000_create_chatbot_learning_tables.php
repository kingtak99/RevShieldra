<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_unhandled_queries', function (Blueprint $table) {
            $table->id();
            $table->string('language', 8)->default('en');
            $table->text('query');
            $table->string('normalized_query', 500);
            $table->unsignedInteger('occurrences')->default(1);
            $table->string('status')->default('pending');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['language', 'normalized_query'], 'chatbot_unhandled_language_query_unique');
            $table->index(['status', 'processed_at']);
        });

        Schema::create('chatbot_learned_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('language', 8)->default('en');
            $table->string('keyword', 500);
            $table->string('normalized_keyword', 500);
            $table->string('target_flow');
            $table->string('target_branch')->nullable();
            $table->string('source')->default('gemini');
            $table->decimal('confidence', 4, 2)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['language', 'normalized_keyword'], 'chatbot_learned_language_keyword_unique');
            $table->index(['target_flow', 'target_branch']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_learned_keywords');
        Schema::dropIfExists('chatbot_unhandled_queries');
    }
};
