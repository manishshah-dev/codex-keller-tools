<?php

namespace App\Http\Controllers;

use App\Mail\CandidateProfileSubmissionMail;
use App\Models\Candidate;
use App\Models\CandidateProfile;
use App\Models\CandidateProfileSubmission;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CandidateProfileSubmissionController extends Controller
{
    public function show(Project $project, Candidate $candidate, CandidateProfile $profile): View
    {
        $this->authorize('view', $project);
        if ($candidate->project_id !== $project->id || $profile->candidate_id !== $candidate->id) {
            abort(404);
        }

        $submissions = CandidateProfileSubmission::where('candidate_profile_id', $profile->id)
            ->with('user')
            ->latest()
            ->get();

        return view('candidate_profile_submissions.show', compact('project', 'candidate', 'profile', 'submissions'));
    }

    
    
    /**
     * Show the submission form.
     */
    public function create(Project $project, Candidate $candidate, CandidateProfile $profile)
    {
        $this->authorize('view', $project);

        if ($profile->candidate_id !== $candidate->id || $profile->project_id !== $project->id) {
            abort(404, 'Profile not found for this candidate and project');
        }

        return view('candidate_profile_submissions.create', compact('project', 'candidate', 'profile'));
    }

    /**
     * Store the submission.
     * and send the email.
     */  

    public function store(Request $request, Project $project, Candidate $candidate, CandidateProfile $profile): RedirectResponse
    {
        
        $this->authorize('update', $project);
        if ($candidate->project_id !== $project->id || $profile->candidate_id !== $candidate->id) {
            abort(404);
        }

        $data = $request->validate([
            'client_email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        CandidateProfileSubmission::create([
            'candidate_profile_id' => $profile->id,
            'candidate_id' => $candidate->id,
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'client_email' => $data['client_email'],
            'subject' => $data['subject'],
            'message' => $data['message'] ?? '',
        ]);

        Mail::to($data['client_email'])->send(new CandidateProfileSubmissionMail($project, $candidate, $profile, $data['message'] ?? ''));

        $cv_available = true;

        if (!$candidate->resume_path) {
            $cv_available = false;
        }
        
        $resumePath = $candidate->resume_path;
        $fullPath = storage_path('app/private/' . $resumePath);
        
        if (!file_exists($fullPath)) {
            $cv_available = false;
        }

        if($cv_available){
            return redirect()
                ->route('projects.candidates.profiles.show', [$project, $candidate, $profile])
                ->with('success', 'Profile submitted to client successfully.');
        }else{
            return redirect()
                ->route('projects.candidates.profiles.show', [$project, $candidate, $profile])
                ->with('warning', 'Profile submitted to client successfully. But CV is not available.');
        }
    }
}