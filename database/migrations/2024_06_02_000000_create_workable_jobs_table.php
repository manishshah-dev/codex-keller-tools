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
        Schema::create('workable_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workable_setting_id')->nullable()->constrained('workable_settings')->onDelete('set null');
            $table->string('workable_job_id')->unique();
            $table->string('title');
            $table->string('full_title')->nullable();
            $table->string('shortcode')->nullable()->index();
            $table->string('state')->nullable();
            $table->string('department')->nullable();
            $table->text('url')->nullable();
            $table->text('application_url')->nullable();
            $table->string('shortlink')->nullable();
            $table->string('location_str')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->boolean('telecommuting')->default(false);
            $table->string('workplace_type')->nullable();
            $table->string('salary_currency', 10)->nullable();
            $table->json('raw_location_data')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('workable_created_at')->nullable();
            $table->timestamp('workable_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workable_jobs');
    }
};
