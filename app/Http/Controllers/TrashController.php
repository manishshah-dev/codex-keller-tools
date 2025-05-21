<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\JobDescription;
use App\Models\Candidate;
use App\Models\JobDescriptionTemplate;
use App\Models\QualifyingQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TrashController extends Controller
{
    /**
     * Display the trash page with tabs for each table.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Get soft-deleted records for each table
        $deletedProjects = Project::onlyTrashed()
            ->where('user_id', Auth::id())
            ->orderBy('deleted_at', 'desc')
            ->get();
            
        $deletedJobDescriptions = JobDescription::onlyTrashed()
            ->where('user_id', Auth::id())
            ->orderBy('deleted_at', 'desc')
            ->get();
            
        $deletedCandidates = Candidate::onlyTrashed()
            ->where('user_id', Auth::id())
            ->orderBy('deleted_at', 'desc')
            ->get();
            
        $deletedTemplates = JobDescriptionTemplate::onlyTrashed()
            ->where('created_by', Auth::id())
            ->orderBy('deleted_at', 'desc')
            ->get();
            
        // For qualifying questions, we need to filter by job descriptions that belong to the user
        // First, get all job description IDs that belong to the user
        $userJobDescriptionIds = JobDescription::where('user_id', Auth::id())->pluck('id')->toArray();
        
        // Then get all qualifying questions that belong to those job descriptions
        $deletedQuestions = QualifyingQuestion::onlyTrashed()
            ->whereIn('job_description_id', $userJobDescriptionIds)
            ->orderBy('deleted_at', 'desc')
            ->get();
        
        return view('trash.index', compact(
            'deletedProjects',
            'deletedJobDescriptions',
            'deletedCandidates',
            'deletedTemplates',
            'deletedQuestions'
        ));
    }
    
    /**
     * Restore a soft-deleted project.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreProject(int $id): RedirectResponse
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to restore this project
        if ($project->user_id !== Auth::id()) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to restore this project.');
        }
        
        $project->restore();
        
        return redirect()->route('trash.index')
            ->with('success', 'Project restored successfully.');
    }
    
    /**
     * Restore a soft-deleted job description.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreJobDescription(int $id): RedirectResponse
    {
        $jobDescription = JobDescription::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to restore this job description
        if ($jobDescription->user_id !== Auth::id()) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to restore this job description.');
        }
        
        // Check if the associated project exists and is not deleted
        if (!$jobDescription->project || $jobDescription->project->trashed()) {
            // Restore the associated project first
            if ($jobDescription->project && $jobDescription->project->trashed()) {
                $jobDescription->project->restore();
            } else {
                return redirect()->route('trash.index')
                    ->with('error', 'Cannot restore job description because the associated project does not exist.');
            }
        }
        
        $jobDescription->restore();
        
        return redirect()->route('trash.index')
            ->with('success', 'Job description restored successfully.');
    }
    
    /**
     * Restore a soft-deleted candidate.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreCandidate(int $id): RedirectResponse
    {
        $candidate = Candidate::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to restore this candidate
        if ($candidate->user_id !== Auth::id()) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to restore this candidate.');
        }
        
        // Check if the associated project exists and is not deleted
        if ($candidate->project_id && (!$candidate->project || $candidate->project->trashed())) {
            // Restore the associated project first
            if ($candidate->project && $candidate->project->trashed()) {
                $candidate->project->restore();
            } else {
                return redirect()->route('trash.index')
                    ->with('error', 'Cannot restore candidate because the associated project does not exist.');
            }
        }
        
        $candidate->restore();
        
        return redirect()->route('trash.index')
            ->with('success', 'Candidate restored successfully.');
    }
    
    /**
     * Restore a soft-deleted job description template.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreTemplate(int $id): RedirectResponse
    {
        $template = JobDescriptionTemplate::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to restore this template
        if ($template->created_by !== Auth::id()) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to restore this template.');
        }
        
        $template->restore();
        
        return redirect()->route('trash.index')
            ->with('success', 'Job description template restored successfully.');
    }
    
    /**
     * Restore a soft-deleted qualifying question.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreQuestion(int $id): RedirectResponse
    {
        $question = QualifyingQuestion::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to restore this question
        // We need to check if the associated job description belongs to the user
        $jobDescription = $question->jobDescription;
        
        if (!$jobDescription || $jobDescription->user_id !== Auth::id()) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to restore this question.');
        }
        
        $question->restore();
        
        return redirect()->route('trash.index')
            ->with('success', 'Qualifying question restored successfully.');
    }
    
    /**
     * Permanently delete a soft-deleted project.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyProject(int $id): RedirectResponse
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to delete this project
        if ($project->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to permanently delete this project.');
        }
        
        // Force delete the project
        $project->forceDelete();
        
        return redirect()->route('trash.index')
            ->with('success', 'Project permanently deleted.');
    }
    
    /**
     * Permanently delete a soft-deleted job description.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyJobDescription(int $id): RedirectResponse
    {
        $jobDescription = JobDescription::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to delete this job description
        if ($jobDescription->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to permanently delete this job description.');
        }
        
        // Force delete the job description
        $jobDescription->forceDelete();
        
        return redirect()->route('trash.index')
            ->with('success', 'Job description permanently deleted.');
    }
    
    /**
     * Permanently delete a soft-deleted candidate.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCandidate(int $id): RedirectResponse
    {
        $candidate = Candidate::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to delete this candidate
        if ($candidate->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to permanently delete this candidate.');
        }
        
        // Force delete the candidate
        $candidate->forceDelete();
        
        return redirect()->route('trash.index')
            ->with('success', 'Candidate permanently deleted.');
    }
    
    /**
     * Permanently delete a soft-deleted job description template.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyTemplate(int $id): RedirectResponse
    {
        $template = JobDescriptionTemplate::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to delete this template
        if ($template->created_by !== Auth::id() && !Auth::user()->is_admin) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to permanently delete this template.');
        }
        
        // Force delete the template
        $template->forceDelete();
        
        return redirect()->route('trash.index')
            ->with('success', 'Job description template permanently deleted.');
    }
    
    /**
     * Permanently delete a soft-deleted qualifying question.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyQuestion(int $id): RedirectResponse
    {
        $question = QualifyingQuestion::onlyTrashed()->findOrFail($id);
        
        // Check if the user is authorized to delete this question
        $jobDescription = $question->jobDescription;
        
        if ((!$jobDescription || $jobDescription->user_id !== Auth::id()) && !Auth::user()->is_admin) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to permanently delete this question.');
        }
        
        // Force delete the question
        $question->forceDelete();
        
        return redirect()->route('trash.index')
            ->with('success', 'Qualifying question permanently deleted.');
    }
    
    /**
     * Manually purge old soft-deleted records.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function purge(): RedirectResponse
    {
        // Only allow administrators to purge records
        if (!Auth::user()->is_admin) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to purge trash items.');
        }
        
        try {
            // Run the command to purge soft-deleted records older than 30 days
            $exitCode = Artisan::call('trash:purge', ['days' => 30]);
            
            // Get the output from the command
            $output = Artisan::output();
            
            // Log the output
            Log::info('Manual trash purge executed', [
                'user_id' => Auth::id(),
                'exit_code' => $exitCode,
                'output' => $output
            ]);
            
            if ($exitCode === 0) {
                return redirect()->route('trash.index')
                    ->with('success', 'Trash items older than 30 days have been permanently deleted.');
            } else {
                return redirect()->route('trash.index')
                    ->with('error', 'Failed to purge trash items. Please check the logs for details.');
            }
        } catch (\Exception $e) {
            Log::error('Error purging trash items: ' . $e->getMessage());
            
            return redirect()->route('trash.index')
                ->with('error', 'An error occurred while purging trash items: ' . $e->getMessage());
        }
    }
    
    /**
     * Bulk delete selected projects.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroyProjects(Request $request): RedirectResponse
    {
        $ids = $request->input('selected_projects', []);
        
        if (empty($ids)) {
            return redirect()->route('trash.index')
                ->with('error', 'No projects selected for deletion.');
        }
        
        // Get the projects that belong to the user
        $projects = Project::onlyTrashed()
            ->whereIn('id', $ids)
            ->where(function($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere(function($q) {
                        // Allow admins to delete any project
                        if (Auth::user()->is_admin) {
                            $q->whereRaw('1=1');
                        }
                    });
            })
            ->get();
        
        $count = $projects->count();
        
        if ($count === 0) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to delete the selected projects.');
        }
        
        // Force delete the projects
        foreach ($projects as $project) {
            $project->forceDelete();
        }
        
        return redirect()->route('trash.index')
            ->with('success', "{$count} projects permanently deleted.");
    }
    
    /**
     * Bulk delete selected job descriptions.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroyJobDescriptions(Request $request): RedirectResponse
    {
        $ids = $request->input('selected_job_descriptions', []);
        
        if (empty($ids)) {
            return redirect()->route('trash.index')
                ->with('error', 'No job descriptions selected for deletion.');
        }
        
        // Get the job descriptions that belong to the user
        $jobDescriptions = JobDescription::onlyTrashed()
            ->whereIn('id', $ids)
            ->where(function($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere(function($q) {
                        // Allow admins to delete any job description
                        if (Auth::user()->is_admin) {
                            $q->whereRaw('1=1');
                        }
                    });
            })
            ->get();
        
        $count = $jobDescriptions->count();
        
        if ($count === 0) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to delete the selected job descriptions.');
        }
        
        // Force delete the job descriptions
        foreach ($jobDescriptions as $jobDescription) {
            $jobDescription->forceDelete();
        }
        
        return redirect()->route('trash.index')
            ->with('success', "{$count} job descriptions permanently deleted.");
    }
    
    /**
     * Bulk delete selected candidates.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroyCandidates(Request $request): RedirectResponse
    {
        $ids = $request->input('selected_candidates', []);
        
        if (empty($ids)) {
            return redirect()->route('trash.index')
                ->with('error', 'No candidates selected for deletion.');
        }
        
        // Get the candidates that belong to the user
        $candidates = Candidate::onlyTrashed()
            ->whereIn('id', $ids)
            ->where(function($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere(function($q) {
                        // Allow admins to delete any candidate
                        if (Auth::user()->is_admin) {
                            $q->whereRaw('1=1');
                        }
                    });
            })
            ->get();
        
        $count = $candidates->count();
        
        if ($count === 0) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to delete the selected candidates.');
        }
        
        // Force delete the candidates
        foreach ($candidates as $candidate) {
            $candidate->forceDelete();
        }
        
        return redirect()->route('trash.index')
            ->with('success', "{$count} candidates permanently deleted.");
    }
    
    /**
     * Bulk delete selected job description templates.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroyTemplates(Request $request): RedirectResponse
    {
        $ids = $request->input('selected_templates', []);
        
        if (empty($ids)) {
            return redirect()->route('trash.index')
                ->with('error', 'No templates selected for deletion.');
        }
        
        // Get the templates that belong to the user
        $templates = JobDescriptionTemplate::onlyTrashed()
            ->whereIn('id', $ids)
            ->where(function($query) {
                $query->where('created_by', Auth::id())
                    ->orWhere(function($q) {
                        // Allow admins to delete any template
                        if (Auth::user()->is_admin) {
                            $q->whereRaw('1=1');
                        }
                    });
            })
            ->get();
        
        $count = $templates->count();
        
        if ($count === 0) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to delete the selected templates.');
        }
        
        // Force delete the templates
        foreach ($templates as $template) {
            $template->forceDelete();
        }
        
        return redirect()->route('trash.index')
            ->with('success', "{$count} templates permanently deleted.");
    }
    
    /**
     * Bulk delete selected qualifying questions.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDestroyQuestions(Request $request): RedirectResponse
    {
        $ids = $request->input('selected_questions', []);
        
        if (empty($ids)) {
            return redirect()->route('trash.index')
                ->with('error', 'No questions selected for deletion.');
        }
        
        // Get the questions that belong to job descriptions owned by the user
        $userJobDescriptionIds = JobDescription::where('user_id', Auth::id())->pluck('id')->toArray();
        
        $questions = QualifyingQuestion::onlyTrashed()
            ->whereIn('id', $ids)
            ->where(function($query) use ($userJobDescriptionIds) {
                $query->whereIn('job_description_id', $userJobDescriptionIds)
                    ->orWhere(function($q) {
                        // Allow admins to delete any question
                        if (Auth::user()->is_admin) {
                            $q->whereRaw('1=1');
                        }
                    });
            })
            ->get();
        
        $count = $questions->count();
        
        if ($count === 0) {
            return redirect()->route('trash.index')
                ->with('error', 'You are not authorized to delete the selected questions.');
        }
        
        // Force delete the questions
        foreach ($questions as $question) {
            $question->forceDelete();
        }
        
        return redirect()->route('trash.index')
            ->with('success', "{$count} questions permanently deleted.");
    }
}