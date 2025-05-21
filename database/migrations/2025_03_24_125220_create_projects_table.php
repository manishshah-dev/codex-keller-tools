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
        Schema::create('projects', function (Blueprint $table) {
            // Basic project information
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            
            // Intake form fields
            $table->string('job_title')->nullable();
            $table->text('required_skills')->nullable();
            $table->text('preferred_skills')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('education_requirements')->nullable();
            $table->string('employment_type')->nullable();
            $table->string('salary_range')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('claap_recording_url')->nullable();
            $table->text('claap_transcript')->nullable();
            
            // Company research fields
            $table->string('company_name')->nullable();
            $table->date('founding_date')->nullable();
            $table->string('company_size')->nullable();
            $table->string('turnover')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('website_url')->nullable();
            $table->text('articles')->nullable();
            $table->text('reviews')->nullable();
            $table->text('competitors')->nullable();
            $table->text('industry_details')->nullable();
            $table->text('typical_clients')->nullable();
            
            // Job description fields
            $table->text('overview')->nullable();
            $table->text('responsibilities')->nullable();
            $table->text('requirements_non_negotiable')->nullable();
            $table->text('requirements_preferred')->nullable();
            $table->string('compensation_range')->nullable();
            $table->text('benefits')->nullable();
            $table->text('disclaimer')->nullable();
            $table->string('jd_file_path')->nullable();
            $table->string('jd_status')->default('draft');
            
            // Salary comparison fields
            $table->decimal('average_salary', 10, 2)->nullable();
            $table->decimal('min_salary', 10, 2)->nullable();
            $table->decimal('max_salary', 10, 2)->nullable();
            $table->text('similar_job_postings')->nullable();
            $table->text('salary_data_source')->nullable();
            
            // Search strings fields
            $table->text('linkedin_boolean_string')->nullable();
            $table->text('google_xray_linkedin_string')->nullable();
            $table->text('google_xray_cv_string')->nullable();
            $table->text('search_string_notes')->nullable();
            
            // Keywords fields
            $table->text('keywords')->nullable();
            $table->text('synonyms')->nullable();
            $table->text('translations')->nullable();
            
            // AI Questions fields
            $table->text('candidate_questions')->nullable();
            $table->text('recruiter_questions')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
