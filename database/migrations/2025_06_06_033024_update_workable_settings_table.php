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
        Schema::rename('workable_settings', 'integration_settings');

        Schema::table('integration_settings', function (Blueprint $table) {
            $table->string('type')->after('name'); // e.g., 'workable', 'brighthire'
            $table->renameColumn('subdomain', 'api_endpoint');
            $table->renameColumn('api_token', 'api_key');
            $table->unique(['name', 'type']);
        });

        // Update existing records if necessary, for example, set a default 'type'
        // DB::table('integration_settings')->update(['type' => 'workable']);
        // Note: If you have existing data, ensure 'api_endpoint' can be nullable temporarily or set a default.
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->string('api_endpoint')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->dropUnique(['name', 'type']);
            $table->renameColumn('api_key', 'api_token');
            $table->renameColumn('api_endpoint', 'subdomain');
            $table->dropColumn('type');
        });

        Schema::rename('integration_settings', 'workable_settings');

        // If you changed api_endpoint to nullable, revert it if it wasn't nullable before.
        // This depends on your original schema for 'subdomain'.
        // For example, if 'subdomain' was not nullable:
        /*
        Schema::table('workable_settings', function (Blueprint $table) {
            $table->string('subdomain')->nullable(false)->change();
        });
        */
    }
};
