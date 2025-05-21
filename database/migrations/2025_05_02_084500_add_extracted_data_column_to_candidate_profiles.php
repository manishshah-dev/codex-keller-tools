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
        // Check if the column already exists to avoid errors
        if (!Schema::hasColumn('candidate_profiles', 'extracted_data')) {
            Schema::table('candidate_profiles', function (Blueprint $table) {
                $table->json('extracted_data')->nullable()->after('custom_sections');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop the column if it exists
        if (Schema::hasColumn('candidate_profiles', 'extracted_data')) {
            Schema::table('candidate_profiles', function (Blueprint $table) {
                $table->dropColumn('extracted_data');
            });
        }
    }
};