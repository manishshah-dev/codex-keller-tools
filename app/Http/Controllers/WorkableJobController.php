<?php

namespace App\Http\Controllers;

use App\Models\WorkableJob;
use App\Models\WorkableSetting;
use App\Services\WorkableService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View; // Changed from Illuminate\Support\Facades\View for direct type hinting

class WorkableJobController extends Controller
{
    protected WorkableService $workableService;

    public function __construct(WorkableService $workableService)
    {
        $this->workableService = $workableService;
        // Optional: Add middleware for authorization if needed, e.g.
        // $this->middleware('auth');
        // $this->middleware('can:manage_workable_settings'); // Example permission
    }

    /**
     * Display a listing of the locally stored Workable jobs.
     */
    public function index(): View
    {
        $jobs = WorkableJob::with('workableSetting')->orderBy('created_at', 'desc')->paginate(20);
        return view('workable_jobs.index', compact('jobs'));
    }

    /**
     * Synchronize jobs from Workable API to the local database.
     */
    public function syncJobs(Request $request): RedirectResponse
    {
        $activeSetting = WorkableSetting::where('is_active', true)->first();

        if (!$activeSetting) {
            session()->flash('error', 'No active Workable setting found. Please configure and activate a Workable setting first.');
            return redirect()->route('workable_jobs.index'); // Or to settings page: redirect()->route('workable_settings.index');
        }

        Log::info("Starting job synchronization with Workable for setting ID: {$activeSetting->id}");

        try {
            $workableJobsData = $this->workableService->listJobs($activeSetting);
            $syncedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($workableJobsData as $jobData) {
                if (empty($jobData['id'])) {
                    Log::warning('Skipping job due to missing ID from Workable.', ['job_data_title' => $jobData['title'] ?? 'N/A']);
                    $skippedCount++;
                    continue;
                }

                $location = $jobData['location'] ?? [];
                $locationCountry = $location['country'] ?? null;
                $locationCountryCode = $location['country_code'] ?? null;
                if (is_array($locationCountryCode)) { // country_code sometimes is an array
                    $locationCountryCode = $locationCountryCode[0] ?? null;
                }


                $mappedData = [
                    'workable_setting_id' => $activeSetting->id,
                    'title' => $jobData['title'] ?? 'Untitled Job',
                    'full_title' => $jobData['full_title'] ?? null,
                    'shortcode' => $jobData['shortcode'] ?? null,
                    'state' => $jobData['state'] ?? null,
                    'department' => $jobData['department'] ?? null,
                    'url' => $jobData['url'] ?? null,
                    'application_url' => $jobData['application_url'] ?? null,
                    'shortlink' => $jobData['shortlink'] ?? null,
                    'location_str' => is_array($location) && isset($location['text']) ? $location['text'] : (is_string($location) ? $location : null),
                    'country' => is_string($locationCountry) ? $locationCountry : null,
                    'country_code' => is_string($locationCountryCode) ? substr($locationCountryCode, 0, 10) : null,
                    'region' => $location['region'] ?? null,
                    'city' => $location['city'] ?? null,
                    'zip_code' => isset($location['zip_code']) ? substr((string)$location['zip_code'], 0, 20) : null,
                    'telecommuting' => $jobData['telecommuting'] ?? false,
                    'workplace_type' => $jobData['workplace_type'] ?? ($jobData['employment_type'] ?? null), // employment_type as fallback
                    'salary_currency' => $jobData['salary_currency'] ?? null, // Assuming direct field or would need parsing
                    'raw_location_data' => is_array($location) ? $location : null,
                    'raw_data' => $jobData, // Store the whole job data
                    'workable_created_at' => isset($jobData['created_at']) ? date('Y-m-d H:i:s', strtotime($jobData['created_at'])) : null,
                    'workable_updated_at' => isset($jobData['updated_at']) ? date('Y-m-d H:i:s', strtotime($jobData['updated_at'])) : null,
                ];

                // Remove null values explicitly if your DB doesn't like them for certain fields handled by updateOrCreate logic
                // For example, if shortcode must be truly unique or null but not empty string.
                // $mappedData = array_filter($mappedData, fn($value) => !is_null($value));


                $job = WorkableJob::updateOrCreate(
                    ['workable_job_id' => $jobData['id']],
                    $mappedData
                );

                if ($job->wasRecentlyCreated) {
                    $syncedCount++;
                } elseif ($job->wasChanged()) {
                    $updatedCount++;
                } else {
                    // No changes, not recently created.
                }
            }

            Log::info("Job synchronization complete. Created: {$syncedCount}, Updated: {$updatedCount}, Skipped: {$skippedCount}.");
            session()->flash('success', "Jobs synchronized successfully. Created: {$syncedCount}, Updated: {$updatedCount}, Skipped: {$skippedCount}.");

        } catch (\Exception $e) {
            Log::error('Failed to synchronize jobs from Workable: ' . $e->getMessage(), [
                'setting_id' => $activeSetting->id,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Could not retrieve jobs from Workable at this time. Error: ' . $e->getMessage());
        }

        return redirect()->route('workable_jobs.index'); // Assuming this route will be defined
    }
}
