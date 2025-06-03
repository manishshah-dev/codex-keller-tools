<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workable_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('workable_id')->unique();
            $table->string('title');
            $table->string('shortcode')->nullable();
            $table->string('department')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('url')->nullable();
            $table->string('state')->default('published');
            $table->timestamp('job_created_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workable_jobs');
    }
};
