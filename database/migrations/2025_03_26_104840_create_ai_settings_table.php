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
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // openai, anthropic, google, etc.
            $table->string('name'); // Display name for the provider
            $table->string('api_key')->nullable();
            $table->string('organization_id')->nullable(); // For OpenAI
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('models')->nullable(); // Available models for this provider
            $table->json('capabilities')->nullable(); // What features this provider can be used for
            $table->timestamps();
        });

        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('feature'); // job_description, qualifying_questions, etc.
            $table->string('name');
            $table->text('prompt_template');
            $table->json('parameters')->nullable(); // Parameters that can be injected into the prompt
            $table->string('provider')->nullable(); // If null, works with any provider
            $table->string('model')->nullable(); // If null, works with any model
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('ai_settings');
    }
};