<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->string('integration');
            $table->string('name');
            $table->string('subdomain')->nullable();
            $table->string('api_token');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique('integration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};
