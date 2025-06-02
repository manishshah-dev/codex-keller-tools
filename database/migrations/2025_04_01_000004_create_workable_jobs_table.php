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
            $table->string('workable_id')->unique();
            $table->string('title');
            $table->string('full_title')->nullable();
            $table->string('shortcode');
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('url')->nullable();
            $table->string('state')->nullable();
            $table->timestamp('job_created_at')->nullable();
            $table->json('data')->nullable();
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
