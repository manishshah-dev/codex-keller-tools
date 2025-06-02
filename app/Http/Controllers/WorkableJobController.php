<?php

namespace App\Http\Controllers;

use App\Models\WorkableJob;
use App\Models\WorkableSetting;
use App\Services\WorkableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkableJobController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', WorkableJob::class);
        $jobs = WorkableJob::orderByDesc('job_created_at')->get();
        return view('workable_jobs.index', compact('jobs'));
    }

    public function fetch(WorkableService $workableService): RedirectResponse
    {
        $this->authorize('create', WorkableJob::class);
        $setting = WorkableSetting::where('is_active', true)->first();
        if (!$setting) {
            return redirect()->route('workable-jobs.index')->with('error', 'No active Workable settings found.');
        }

        try {
            $jobs = $workableService->listJobs($setting);
            foreach ($jobs as $job) {
                WorkableJob::updateOrCreate(
                    ['workable_id' => $job['id']],
                    [
                        'title' => $job['title'] ?? '',
                        'full_title' => $job['full_title'] ?? null,
                        'shortcode' => $job['shortcode'] ?? '',
                        'department' => $job['department'] ?? null,
                        'location' => $job['location']['location_str'] ?? null,
                        'url' => $job['url'] ?? null,
                        'state' => $job['state'] ?? null,
                        'job_created_at' => $job['created_at'] ?? null,
                        'data' => $job,
                    ]
                );
            }
            return redirect()->route('workable-jobs.index')->with('success', 'Jobs fetched successfully.');
        } catch (\Exception $e) {
            return redirect()->route('workable-jobs.index')->with('error', 'Workable error: ' . $e->getMessage());
        }
    }
}
