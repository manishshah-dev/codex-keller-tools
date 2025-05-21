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
        if (!Schema::hasTable('profile_custom_headings')) {
            Schema::create('profile_custom_headings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_default')->default(false);
                $table->integer('display_order')->default(0);
                $table->timestamps();
                
                // Indexes
                $table->index('project_id');
                $table->index('user_id');
                $table->index('is_default');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_custom_headings');
    }
};
