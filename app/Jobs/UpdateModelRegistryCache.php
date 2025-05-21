<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\ModelRegistryService; // Import the service
class UpdateModelRegistryCache implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    // Inject the service via the handle method
    public function handle(ModelRegistryService $modelRegistryService): void
    {
        // Call the refresh cache method
        $modelRegistryService->refreshCache();
    }
}
