<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('candidate_profiles', 'export_format')) {
                $table->string('export_format')->nullable();
            }
            if (!Schema::hasColumn('candidate_profiles', 'export_path')) {
                $table->string('export_path')->nullable();
            }
            if (!Schema::hasColumn('candidate_profiles', 'last_exported_at')) {
                $table->timestamp('last_exported_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('candidate_profiles', 'export_format')) {
                $table->dropColumn('export_format');
            }
            if (Schema::hasColumn('candidate_profiles', 'export_path')) {
                $table->dropColumn('export_path');
            }
            if (Schema::hasColumn('candidate_profiles', 'last_exported_at')) {
                $table->dropColumn('last_exported_at');
            }
        });
    }
};

