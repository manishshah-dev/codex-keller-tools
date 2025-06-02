<?php

namespace App\Http\Controllers;

use App\Models\WorkableJob;
use App\Models\WorkableSetting;
use App\Services\WorkableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class WorkableJobController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', WorkableSetting::class); // admin only
        $jobs = WorkableJob::orderBy('workable_created_at', 'desc')->paginate(20);
        return view('workable_jobs.index', compact('jobs'));
    }

    public function fetch(Request $request, WorkableService $service): RedirectResponse
    {
        $this->authorize('create', WorkableSetting::class); // admin
        $setting = WorkableSetting::where('is_active', true)->first();
        if (!$setting) {
            return redirect()->route('workable-jobs.index')->with('error', 'No active Workable settings.');
        }

        try {
            $jobs = $service->listJobs($setting);
            foreach ($jobs as $job) {
                WorkableJob::updateOrCreate(
                    ['workable_id' => $job['id']],
                    [
                        'title' => $job['title'] ?? null,
                        'full_title' => $job['full_title'] ?? null,
                        'shortcode' => $job['shortcode'] ?? null,
                        'state' => $job['state'] ?? null,
                        'department' => $job['department'] ?? null,
                        'department_hierarchy' => $job['department_hierarchy'] ?? null,
                        'url' => $job['url'] ?? null,
                        'application_url' => $job['application_url'] ?? null,
                        'shortlink' => $job['shortlink'] ?? null,
                        'location' => $job['location'] ?? null,
                        'locations' => $job['locations'] ?? null,
                        'salary' => $job['salary'] ?? null,
                        'workable_created_at' => isset($job['created_at']) ? \Carbon\Carbon::parse($job['created_at']) : null,
                    ]
                );
            }
            return redirect()->route('workable-jobs.index')->with('success', 'Jobs imported successfully.');
        } catch (\Exception $e) {
            Log::error('Workable jobs fetch failed: ' . $e->getMessage());
            return redirect()->route('workable-jobs.index')->with('error', 'Failed to fetch jobs.');
        }
    }
}
