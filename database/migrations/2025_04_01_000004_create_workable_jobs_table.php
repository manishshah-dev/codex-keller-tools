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
            $table->string('full_title')->nullable();
            $table->string('shortcode')->unique();
            $table->string('state')->nullable();
            $table->string('department')->nullable();
            $table->json('department_hierarchy')->nullable();
            $table->string('url')->nullable();
            $table->string('application_url')->nullable();
            $table->string('shortlink')->nullable();
            $table->json('location')->nullable();
            $table->json('locations')->nullable();
            $table->json('salary')->nullable();
            $table->timestamp('workable_created_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workable_jobs');
    }
};
