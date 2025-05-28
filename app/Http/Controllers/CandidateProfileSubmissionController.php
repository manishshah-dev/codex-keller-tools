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
    public function show(CandidateProfile $profile): View
    {
        // $this->authorize('view', $project);

        $submissions = CandidateProfileSubmission::with('user')
            ->latest()
            ->paginate(20);


        return view('candidate_profile_submissions.show', compact('profile','submissions'));
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

        $template = \App\Mail\CandidateProfileSubmissionMail::defaultTemplate();

        return view('candidate_profile_submissions.create', compact('project', 'candidate', 'profile', 'template'));
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
            'client_name' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'email_body' => 'required|string',
            'attach_cv' => 'sometimes|boolean',
        ]);

        CandidateProfileSubmission::create([
            'candidate_profile_id' => $profile->id,
            'candidate_id' => $candidate->id,
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'client_email' => $data['client_email'],
            'client_name' => $data['client_name'] ?? '',
            'subject' => $data['subject'],
            'message' => $data['email_body'] ?? '',
        ]);

        // Mail::to($data['client_email'])->send(new CandidateProfileSubmissionMail($project, $candidate, $profile, $data['message'] ?? ''));

        Mail::to($data['client_email'])->send(
            new CandidateProfileSubmissionMail(
                $project,
                $candidate,
                $profile,
                $data['subject'],
                $data['email_body'],
                (bool)($data['attach_cv'] ?? false)
            )
        );

        $cv_available = false;

        if ($candidate->resume_path && isset($data['attach_cv']) && $data['attach_cv']) {
            $resumePath = basename((string) $candidate->resume_path);
            $fullPath = storage_path('app/private/' . $resumePath);
            
            if (file_exists($fullPath)) {
                $cv_available = true; 
            }
        }

        if($cv_available){
            return redirect()
                ->route('projects.candidates.profiles.submissions.show')
                ->with('success', 'Profile submitted to client successfully.');
        }else{
            return redirect()
                ->route('projects.candidates.profiles.submissions.show')
                ->with('warning', 'Profile submitted to client successfully. CV was not attached.');
        }
    }
}