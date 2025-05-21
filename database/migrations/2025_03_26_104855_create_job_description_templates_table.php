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
        Schema::create('job_description_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('industry');
            $table->string('job_level'); // entry, mid, senior, executive
            $table->text('description')->nullable();
            $table->text('overview_template')->nullable();
            $table->text('responsibilities_template')->nullable();
            $table->text('requirements_template')->nullable();
            $table->text('benefits_template')->nullable();
            $table->text('disclaimer_template')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot table for template categories
        Schema::create('template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('job_description_template_category', function (Blueprint $table) {
            $table->foreignId('job_description_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_category_id')->constrained('template_categories')->onDelete('cascade');
            $table->primary(['job_description_template_id', 'template_category_id'], 'template_category_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_description_template_category');
        Schema::dropIfExists('template_categories');
        Schema::dropIfExists('job_description_templates');
    }
};