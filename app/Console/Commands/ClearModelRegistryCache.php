<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ModelRegistryService; // Import the service
use Illuminate\Support\Facades\Log; // Import Log facade
class ClearModelRegistryCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-models'; // Changed signature

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the cached AI model list from ModelRegistryService'; // Added description

    /**
     * Execute the console command.
     */
    // Inject the service
    public function handle(ModelRegistryService $modelRegistryService): void
    {
        try {
            $modelRegistryService->clearCache();
            $this->info('AI Model Registry cache cleared successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to clear AI Model Registry cache: ' . $e->getMessage());
        }
    }
}
