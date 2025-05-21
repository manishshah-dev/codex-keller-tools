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
        Schema::create('qualifying_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_description_id')->constrained()->onDelete('cascade');
            $table->string('question');
            $table->text('description')->nullable();
            $table->string('type')->default('multiple_choice'); // multiple_choice, yes_no, text, numeric
            $table->json('options')->nullable(); // For multiple choice questions
            $table->boolean('required')->default(true);
            $table->integer('order')->default(0);
            $table->string('category')->nullable(); // e.g., technical, experience, education, etc.
            $table->boolean('is_knockout')->default(false); // If true, wrong answer disqualifies candidate
            $table->string('correct_answer')->nullable(); // For knockout questions
            $table->boolean('is_ai_generated')->default(false);
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualifying_questions');
    }
};