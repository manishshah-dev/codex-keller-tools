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
        Schema::table('job_description_templates', function (Blueprint $table) {
            // Make originally non-nullable fields nullable
            $table->string('industry')->nullable()->change();
            $table->string('job_level')->nullable()->change();
            // Other text fields were already nullable or don't exist
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_description_templates', function (Blueprint $table) {
            // Revert only the fields changed in up()
            $table->string('industry')->nullable(false)->change();
            $table->string('job_level')->nullable(false)->change();
        });
    }
};