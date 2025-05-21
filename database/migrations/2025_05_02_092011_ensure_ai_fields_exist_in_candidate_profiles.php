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
        Schema::table('candidate_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('candidate_profiles', 'ai_provider')) {
                $table->string('ai_provider')->nullable();
            }
            
            if (!Schema::hasColumn('candidate_profiles', 'ai_model')) {
                $table->string('ai_model')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed as we're just ensuring the columns exist
    }
};
