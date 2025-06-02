<?php

namespace App\Http\Controllers;

use App\Models\WorkableJob;
use App\Models\WorkableCandidate;
use App\Models\WorkableSetting;
use App\Services\WorkableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkableJobController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', WorkableSetting::class);
        $jobs = WorkableJob::orderBy('title')->get();
        return view('workable_jobs.index', compact('jobs'));
    }

    public function fetch(WorkableService $service): RedirectResponse
    {
        $this->authorize('viewAny', WorkableSetting::class);
        $setting = WorkableSetting::where('is_active', true)->first();
        if (!$setting) {
            return redirect()->route('workable-jobs.index')->with('error', 'No active Workable settings found.');
        }

        try {
            $jobs = $service->listJobs($setting);
            foreach ($jobs as $job) {
                WorkableJob::updateOrCreate([
                    'workable_id' => $job['id'],
                ], [
                    'title' => $job['title'] ?? 'Untitled',
                    'shortcode' => $job['shortcode'] ?? null,
                ]);
            }
            return redirect()->route('workable-jobs.index')->with('success', 'Jobs imported.');
        } catch (\Exception $e) {
            return redirect()->route('workable-jobs.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function importCandidates(Request $request, WorkableService $service): RedirectResponse
    {
        $this->authorize('viewAny', WorkableSetting::class);
        $validated = $request->validate([
            'jobs' => 'required|array',
            'jobs.*' => 'integer',
        ]);

        $setting = WorkableSetting::where('is_active', true)->first();
        if (!$setting) {
            return redirect()->route('workable-jobs.index')->with('error', 'No active Workable settings found.');
        }

        $imported = 0;
        foreach ($validated['jobs'] as $jobId) {
            $job = WorkableJob::find($jobId);
            if (!$job) {
                continue;
            }
            try {
                $candidates = $service->listJobCandidates($setting, $job->workable_id);
                foreach ($candidates as $cand) {
                    WorkableCandidate::updateOrCreate([
                        'workable_id' => $cand['id'],
                    ], [
                        'name' => $cand['name'] ?? '',
                        'email' => $cand['email'] ?? null,
                        'phone' => $cand['phone'] ?? null,
                        'job_title' => $job->title,
                        'job_shortcode' => $job->shortcode,
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                // log or ignore
            }
        }

        return redirect()->route('workable-jobs.index')->with('success', "Imported {$imported} candidate(s).");
    }
}
