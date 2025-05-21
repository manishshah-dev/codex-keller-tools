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
        Schema::create('project_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Requirement details
            $table->string('type'); // skill, experience, education, certification, language, location, industry, tool
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 2)->default(1.00); // 0.00 to 1.00
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            
            // Metadata
            $table->string('source')->default('manual'); // manual, job_description, chat
            $table->boolean('created_by_chat')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_requirements');
    }
};