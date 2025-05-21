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
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->json('headings')->nullable(); // Stores custom headings and their content
            $table->json('metadata')->nullable(); // Stores additional metadata like generation parameters
            $table->json('extracted_data')->nullable(); // Stores structured data extracted from resume and other sources
            $table->json('interview_insights')->nullable(); // Stores insights from interview data
            $table->json('web_presence_data')->nullable(); // Stores data from web presence analysis
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->string('status')->default('draft'); // draft, in_progress, completed
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('candidate_id');
            $table->index('project_id');
            $table->index('user_id');
            $table->index('status');
        });
        
        // Create profile_custom_headings table for storing heading templates
        Schema::create('profile_custom_headings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('project_id');
            $table->index('user_id');
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_custom_headings');
        Schema::dropIfExists('candidate_profiles');
    }
};