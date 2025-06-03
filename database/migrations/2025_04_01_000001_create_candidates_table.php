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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Basic candidate information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->string('current_company')->nullable();
            $table->string('current_position')->nullable();
            $table->string('linkedin_url')->nullable();
            
            // Resume data
            $table->string('resume_path')->nullable();
            $table->longText('resume_text')->nullable();
            $table->json('resume_parsed_data')->nullable();
            
            // Matching data
            $table->decimal('match_score', 5, 4)->default(0); // 0.0000 to 1.0000
            $table->json('match_details')->nullable();
            
            // Status and metadata
            $table->string('status')->default('new'); // new, contacted, interviewing, offered, hired, rejected, withdrawn
            $table->string('source')->default('manual'); // manual, workable, etc.
            $table->string('workable_id')->nullable();
            $table->string('workable_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_analyzed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};