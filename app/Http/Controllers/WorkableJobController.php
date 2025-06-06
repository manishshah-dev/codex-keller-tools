<?php

namespace App\Http\Controllers;

use App\Models\WorkableJob;
use App\Models\IntegrationSetting;
use App\Services\WorkableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkableJobController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', WorkableJob::class);
        $jobs = WorkableJob::orderBy('title')->paginate(20);
        return view('workable_jobs.index', compact('jobs'));
    }

    public function fetch(WorkableService $service): RedirectResponse
    {
        $setting = IntegrationSetting::where('integration', 'workable')->where('is_active', true)->first();
        if (!$setting) {
            return redirect()->route('workable-jobs.index')->with('error', 'No active Workable settings found.');
        }

        $jobs = $service->listJobs($setting);

        foreach ($jobs as $job) {
            WorkableJob::updateOrCreate(
                ['workable_id' => $job['id']],
                [
                    'title' => $job['title'] ?? $job['full_title'] ?? 'Untitled',
                    'shortcode' => $job['shortcode'] ?? null,
                    'department' => $job['department'] ?? null,
                    'country' => $job['location']['country'] ?? null,
                    'city' => $job['location']['city'] ?? null,
                    'url' => $job['url'] ?? null,
                    'state' => $job['state'],
                    'job_created_at' => $job['created_at'] ?? null,
                ]
            );
        }

        return redirect()->route('workable-jobs.index')->with('success', 'Jobs imported.');
    }
}
