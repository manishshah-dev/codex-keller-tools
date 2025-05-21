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
        Schema::create('job_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Basic job description fields
            $table->string('title');
            $table->text('overview')->nullable();
            $table->text('responsibilities')->nullable();
            $table->text('requirements_non_negotiable')->nullable();
            $table->text('requirements_preferred')->nullable();
            $table->string('compensation_range')->nullable();
            $table->text('benefits')->nullable();
            $table->string('location')->nullable();
            $table->text('disclaimer')->nullable();
            
            // Additional fields for AI generation
            $table->string('industry')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('education_requirements')->nullable();
            $table->text('skills_required')->nullable();
            $table->text('skills_preferred')->nullable();
            
            // Template and versioning
            $table->string('template_used')->nullable();
            $table->integer('version')->default(1);
            $table->string('status')->default('draft'); // draft, review, approved, published
            
            // Export information
            $table->string('export_format')->nullable();
            $table->string('export_path')->nullable();
            $table->timestamp('last_exported_at')->nullable();
            
            // AI generation metadata
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->json('generation_parameters')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_descriptions');
    }
};
