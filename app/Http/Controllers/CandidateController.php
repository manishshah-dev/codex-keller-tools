<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Project;
use App\Models\ProjectRequirement;
use App\Models\CandidateChatMessage;
use App\Models\AISetting;
use App\Services\AIService;
use App\Models\AIPrompt;
use App\Jobs\AnalyzeAllCandidatesJob; // Import the job class
use Illuminate\Http\Request;
// Removed duplicate Project import
// Removed duplicate Candidate import
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
// Removed duplicate Auth import
// Removed duplicate imports below
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;
use App\Services\ModelRegistryService; // Import the service
use App\Services\WorkableService;
use App\Models\WorkableSetting;
use App\Models\WorkableJob;
use Symfony\Component\HttpFoundation\StreamedResponse; // For file response

class CandidateController extends Controller
{
    /**
     * Display a listing of the candidates.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $candidates = Candidate::with('project')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('candidates.index', compact('candidates'));
    }
    
    /**
     * Display a listing of the candidates for a specific project.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function projectIndex(Request $request, Project $project, ModelRegistryService $modelRegistryService, WorkableService $workableService): View // Inject service
    {
        $this->authorize('view', $project);
        
        $candidates = $project->candidates()
            ->orderByScore() // Assumes scopeOrderByScore exists
            ->paginate(10);
        
        $requirements = $project->activeRequirements()->get();

        // Fetch AI Settings suitable for CV analysis
        $aiSettings = AISetting::active()
            ->get();
            
        // Fetch available prompts for CV analysis
        $prompts = AIPrompt::where('feature', 'cv_analyzer')->orderBy('name')->get();

        // Fetch the dynamic model map
        $providerModels = $modelRegistryService->getModels();

        // Workable data for import section
        $departments = WorkableJob::whereNotNull('department')->distinct()->pluck('department');
        $countries = WorkableJob::whereNotNull('country')->distinct()->pluck('country');
        $jobs = WorkableJob::all();

        $workableCandidates = [];
        $workableSetting = WorkableSetting::where('is_active', true)->first();
        if ($workableSetting && $request->query('workable_job')) {
            try {
                $job = WorkableJob::find($request->query('workable_job'));
                if ($job) {
                    $params = [
                        'shortcode' => $job->shortcode,
                    ];
                    if ($request->boolean('filter_email') && $request->filled('email')) {
                        $params['email'] = $request->input('email');
                    }
                    if ($request->boolean('filter_created_after') && $request->filled('created_after')) {
                        $params['created_after'] = $request->input('created_after');
                    }

                    $workableCandidates = $workableService->listCandidates($workableSetting, $params);
                }
            } catch (\Exception $e) {
                Log::error('Workable candidates fetch failed: ' . $e->getMessage());
            }
        }

        return view('candidates.project_index', compact(
            'project',
            'candidates',
            'requirements',
            'aiSettings',
            'prompts',
            'providerModels',
            'workableCandidates',
            'departments',
            'countries',
            'jobs'
        ));
    }
    
    /**
     * Show the form for creating a new candidate.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function create(Project $project): View
    {
        $this->authorize('update', $project);
        
        return view('candidates.create', compact('project'));
    }
    
    /**
     * Store a newly created candidate in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    // Updated to handle multiple file uploads from the create view
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'cv_files' => 'required|array|min:1', // Expecting an array of files
            'cv_files.*' => 'required|file|mimes:pdf,doc,docx|max:10240', // Max 10MB per file
        ]);

        $uploadedCount = 0;
        $failedFiles = [];
        $lastCandidateId = null; // To redirect to the last processed candidate

        foreach ($validated['cv_files'] as $file) {
            try {
                // Generate a unique name and store the file
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                // Use project ID and user ID for subdirectories, store in 'private' disk
                $path = $file->storeAs(
                    'resumes/project_' . $project->id . '/user_' . Auth::id(),
                    Str::slug($originalName) . '_' . uniqid() . '.' . $extension,
                    'private' // Use the 'private' disk
                );

                // Extract text from resume
                $resumeText = $this->extractTextFromResume($file);

                // Extract details using the new method
                $extractedDetails = $this->extractDetailsFromText($resumeText);

                // Attempt to parse name (basic split) - Keep this basic or improve later
                $nameParts = explode(' ', $originalName, 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '(CV)'; // Slightly better placeholder

                // Create a candidate record with extracted details
                $candidate = Candidate::create([
                    'project_id' => $project->id,
                    'user_id' => Auth::id(),
                    'first_name' => $firstName, // Use filename part as placeholder
                    'last_name' => $lastName,   // Use filename part or default
                    'email' => $extractedDetails['email'], // Use extracted email
                    'phone' => $extractedDetails['phone'], // Use extracted phone
                    'location' => $extractedDetails['location'], // Use extracted location
                    'resume_path' => $path,
                    'resume_text' => $resumeText,
                    'status' => 'new',
                    'source' => 'upload', // Keep source as 'upload'
                ]);
                $uploadedCount++;
                $lastCandidateId = $candidate->id;

                // Trigger analysis (assuming analyzeCandidate handles potential errors)
                 if ($candidate) {
                     $this->analyzeCandidate($candidate);
                 }

            } catch (\Exception $e) {
                $failedFiles[] = $file->getClientOriginalName();
            }
        }

        $message = "Successfully processed {$uploadedCount} CV(s).";
        $redirectRoute = 'projects.candidates.index'; // Default redirect
        $routeParams = [$project];

        if ($uploadedCount === 1 && $lastCandidateId) {
             // If only one succeeded, redirect to its show page
             $redirectRoute = 'projects.candidates.show'; // Use nested route
             $routeParams = [$project, Candidate::find($lastCandidateId)]; // Pass project and candidate
        } elseif ($uploadedCount > 1) {
             // If multiple succeeded, redirect to project candidate list
             $redirectRoute = 'projects.candidates.index';
             $routeParams = [$project];
        }


        if (!empty($failedFiles)) {
            $message .= " Failed to upload/process: " . implode(', ', $failedFiles);
            return redirect()->route($redirectRoute, $routeParams)
                         ->with('warning', $message);
        }

        return redirect()->route($redirectRoute, $routeParams)
                     ->with('success', $message);
    }
    
    /**
     * Display the specified candidate.
     *
     * @param  \App\Models\Candidate  $candidate
     * @return \Illuminate\View\View
     */
    // Removed Project $project due to shallow routing
    public function show(Project $project, Candidate $candidate, ModelRegistryService $modelRegistryService): View
    {
        if ($candidate->project_id !== $project->id) {
            abort(404, 'Candidate not found in this project');
        }
        $this->authorize('view', $project);
        
        $requirements = $project->activeRequirements()->get();
        $chatMessages = $candidate->chatMessages()->orderBy('created_at')->get();

        $aiSettings = AISetting::active()->get();
        $prompts = AIPrompt::where('feature', 'cv_analyzer')->orderBy('name')->get();
        $providerModels = $modelRegistryService->getModels();

        return view('candidates.show', compact('project', 'candidate', 'requirements', 'chatMessages', 'aiSettings', 'prompts', 'providerModels'));
    }
    
    /**
     * Display the specified candidate with project context.
     * This method handles the nested route: /projects/{project}/candidates/{candidate}
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\Candidate  $candidate
     * @return \Illuminate\View\View
     */
    public function projectShow(Project $project, Candidate $candidate, ModelRegistryService $modelRegistryService): View
    {
        $this->authorize('view', $project);
        
        // Verify that the candidate belongs to the project
        if ($candidate->project_id !== $project->id) {
            abort(404, 'Candidate not found in this project');
        }
        
        $requirements = $project->activeRequirements()->get();
        $chatMessages = $candidate->chatMessages()->orderBy('created_at')->get();

        $aiSettings = AISetting::active()->get();
        $prompts = AIPrompt::where('feature', 'cv_analyzer')->orderBy('name')->get();
        $providerModels = $modelRegistryService->getModels();

        return view('candidates.show', compact('project', 'candidate', 'requirements', 'chatMessages', 'aiSettings', 'prompts', 'providerModels'));    }
    
    /**
     * Show the form for editing the specified candidate.
     *
     * @param  \App\Models\Candidate  $candidate
     * @return \Illuminate\View\View
     */
     // Removed Project $project due to shallow routing
    public function edit(Project $project, Candidate $candidate): View
    {
        // Verify that the candidate belongs to the project
        if ($candidate->project_id !== $project->id) {
            abort(404, 'Candidate not found in this project');
        }
        $this->authorize('update', $project);
        
        return view('candidates.edit', compact('project', 'candidate'));
    }
    
    /**
     * Update the specified candidate in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Candidate  $candidate
     * @return \Illuminate\Http\RedirectResponse
     */
     // Removed Project $project due to shallow routing
    public function update(Request $request, Project $project, Candidate $candidate): RedirectResponse
    {
        // Verify that the candidate belongs to the project
        if ($candidate->project_id !== $project->id) {
            abort(404, 'Candidate not found in this project');
        }
        $this->authorize('update', $project);
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'current_company' => 'nullable|string|max:255',
            'current_position' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:new,contacted,interviewing,offered,hired,rejected,withdrawn',
        ]);
        
        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'] ?? $candidate->email, // Keep old if not provided
            'phone' => $validated['phone'] ?? $candidate->phone, // Keep old if not provided
            'location' => $validated['location'] ?? $candidate->location, // Keep old if not provided
            'current_company' => $validated['current_company'] ?? null,
            'current_position' => $validated['current_position'] ?? null,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'] ?? $candidate->status,
        ];

        // Handle resume upload
        if ($request->hasFile('resume')) {
            // Delete old resume if exists
            if ($candidate->resume_path) {
                // Use private disk for deletion
                Storage::disk('private')->delete($candidate->resume_path);
            }
            
            $file = $request->file('resume');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            // Use private disk for storage
            $resumePath = $file->storeAs(
                 'resumes/project_' . $candidate->project_id . '/user_' . Auth::id(),
                 Str::slug($validated['first_name'] . '-' . $validated['last_name']) . '_' . uniqid() . '.' . $extension,
                 'private'
            );
            
            // Extract text from resume
            $resumeText = $this->extractTextFromResume($file);
            
            // Extract details from the new resume text
            $extractedDetails = $this->extractDetailsFromText($resumeText);

            $updateData['resume_path'] = $resumePath;
            $updateData['resume_text'] = $resumeText;
            // Overwrite email/phone/location with newly extracted data if found
            // Only update if the extracted value is not null, otherwise keep the value from the form/existing data
            if (!is_null($extractedDetails['email'])) {
                $updateData['email'] = $extractedDetails['email'];
            }
            if (!is_null($extractedDetails['phone'])) {
                $updateData['phone'] = $extractedDetails['phone'];
            }
             if (!is_null($extractedDetails['location'])) {
                $updateData['location'] = $extractedDetails['location'];
            }
            
            // Re-analyze the candidate
            $this->analyzeCandidate($candidate); // Analyze after potential data update
        }
        
        // Update candidate
        $candidate->update($updateData);
        
        // Use the nested route name 'projects.candidates.show'
        return redirect()->route('projects.candidates.show', [$project, $candidate])
            ->with('success', 'Candidate updated successfully.');
    }
    
    /**
     * Remove the specified candidate from storage.
     *
     * @param  \App\Models\Candidate  $candidate
     * @return \Illuminate\Http\RedirectResponse
     */
     // Removed Project $project due to shallow routing
    public function destroy(Project $project, Candidate $candidate): RedirectResponse
    {
        // Verify that the candidate belongs to the project
        if ($candidate->project_id !== $project->id) {
            abort(404, 'Candidate not found in this project');
        }
        $this->authorize('update', $project);
        
        // Delete resume file from private disk
        if ($candidate->resume_path) {
            Storage::disk('private')->delete($candidate->resume_path);
        }
        
        $candidate->delete();
        
        // return redirect()->route('projects.candidates.index', $project)
        //     ->with('success', 'Candidate deleted successfully.');

        // redirect to back but not if the back route is the same as the current route
        if (url()->previous() === route('projects.candidates.show', [$project, $candidate])) {
            return redirect()->route('projects.candidates.index', $project)
                ->with('success', 'Candidate deleted successfully.');
        }else {
            return redirect()->back()
                ->with('success', 'Candidate deleted successfully.');
        }
    }
    
    /**
     * Show the CV Analyzer chat interface.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function analyzer(Project $project, ModelRegistryService $modelRegistryService): View
    {
        $this->authorize('view', $project);
        
        $candidates = $project->candidates()
            ->orderByScore()
            ->get();
        
        $requirements = $project->activeRequirements()->get();
        
        // Fetch AI Settings suitable for CV analysis
        $aiSettings = AISetting::active()
            ->get();
            
        // Fetch the dynamic model map
        $providerModels = $modelRegistryService->getModels();
        
        return view('candidates.analyzer', compact(
            'project',
            'candidates',
            'requirements',
            'aiSettings',
            'providerModels'
        ));
    }
    
    /**
     * Process a chat message in the CV Analyzer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $validated = $request->validate([
            'message' => 'required|string',
            'candidate_id' => 'nullable|exists:candidates,id',
            'ai_provider' => 'required|string',
            'ai_model' => 'required|string',
        ]);
        
        try {
            // Find a default candidate if none is specified
            $candidateId = null;
            if (!empty($validated['candidate_id'])) {
                $candidate = Candidate::findOrFail($validated['candidate_id']);
                $candidateId = $candidate->id;
            } else {
                // Get the first candidate in the project as a default
                $defaultCandidate = $project->candidates()->first();
                if ($defaultCandidate) {
                    $candidateId = $defaultCandidate->id;
                } else {
                    // If no candidates exist, we need to create a dummy candidate
                    $defaultCandidate = Candidate::create([
                        'project_id' => $project->id,
                        'user_id' => Auth::id(),
                        'first_name' => 'Project',
                        'last_name' => 'Chat',
                        'status' => 'new',
                        'source' => 'system',
                    ]);
                    $candidateId = $defaultCandidate->id;
                }
            }
            
            // Save user message
            $userMessage = new CandidateChatMessage([
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'candidate_id' => $candidateId, // Always set a candidate ID
                'message' => $validated['message'],
                'is_user' => true,
            ]);
            
            $userMessage->save();
            
            // Process the message to identify requirement changes
            $requirementsAdded = [];
            $requirementsRemoved = [];
            $requirementsUpdated = false; // Flag to track if re-analysis is needed

            // Simple keyword-based requirement extraction (basic implementation)
            $lowerMessage = strtolower($validated['message']);

            // Example: "add required skill PHP with weight 0.8"
            if (preg_match('/add(?:\s+required)?\s+(skill|experience|education)\s+(.*?)(?:\s+with\s+weight\s+([\d.]+))?$/i', $validated['message'], $matches)) {
                $newReq = [
                    'type' => strtolower($matches[1]),
                    'name' => trim($matches[2]),
                    'description' => 'Added via chat',
                    'weight' => isset($matches[3]) ? (float)$matches[3] : 1.0,
                    'is_required' => str_contains($lowerMessage, 'required'),
                ];
                $requirementsAdded[] = $newReq;
                $requirementsUpdated = true;
            }

            // Example: "remove requirement PHP" or "remove skill PHP"
            if (preg_match('/remove(?:\s+requirement|\s+skill|\s+experience|\s+education)?\s+(.*)$/i', $validated['message'], $matches)) {
                $reqNameToRemove = trim($matches[1]);
                $existingReq = $project->activeRequirements()->where('name', 'ILIKE', $reqNameToRemove)->first();
                if ($existingReq) {
                    $requirementsRemoved[] = $existingReq->id;
                    $requirementsUpdated = true;
                }
            }

            // TODO: Implement more sophisticated NLP for requirement extraction if needed
            // Call AI service
            $aiService = new AIService();
            $prompt = $this->buildChatPrompt($project, $validated['message'], $validated['candidate_id'] ?? null);
            
            // Get the AI setting based on the provider
            $aiSetting = AISetting::where('provider', $validated['ai_provider'])
                ->active()
                ->first();
                
            if (!$aiSetting) {
                throw new Exception("No active AI setting found for provider: {$validated['ai_provider']}");
            }
            
            $aiResponse = $aiService->generateContent(
                $aiSetting,
                $validated['ai_model'],
                $prompt,
                [],
                Auth::id(),
                'chat' // Use 'chat' feature instead of 'cv_analyzer' to avoid JSON formatting
            );
            
            // Save AI response
            $aiMessage = new CandidateChatMessage([
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'candidate_id' => $candidateId, // Use the same candidate ID as the user message
                'message' => $aiResponse['content'],
                'is_user' => false,
                'requirements_added' => $requirementsAdded,
                'requirements_removed' => $requirementsRemoved,
                'ai_provider' => $validated['ai_provider'],
                'ai_model' => $validated['ai_model'],
                'tokens_used' => $aiResponse['tokens_used'],
                'cost' => $aiResponse['cost'],
            ]);
            
            $aiMessage->save();
            
            // Apply requirement changes
            if (!empty($requirementsAdded)) {
                foreach ($requirementsAdded as $requirement) {
                    ProjectRequirement::create([
                        'project_id' => $project->id,
                        'user_id' => Auth::id(),
                        'type' => $requirement['type'],
                        'name' => $requirement['name'],
                        'description' => $requirement['description'] ?? null,
                        'weight' => $requirement['weight'] ?? 1.0,
                        'is_required' => $requirement['is_required'] ?? false,
                        'source' => 'chat',
                        'created_by_chat' => true,
                    ]);
                }
            }
            
            if (!empty($requirementsRemoved)) {
                foreach ($requirementsRemoved as $requirementId) {
                    $requirement = ProjectRequirement::find($requirementId);
                    if ($requirement && $requirement->project_id === $project->id) {
                        $requirement->is_active = false;
                        $requirement->save();
                    }
                }
            }
            
            // Re-analyze candidates if requirements changed
            if ($requirementsUpdated) {
                 $this->analyzeAllCandidates($project);
                 // Fetch updated candidates list for response (optional, depends on UI needs)
            }
            
            return response()->json([
                'success' => true,
                'user_message' => [
                    'id' => $userMessage->id,
                    'message' => $userMessage->message,
                    'is_user' => true,
                    'created_at' => $userMessage->created_at->format('Y-m-d H:i:s'),
                ],
                'ai_message' => [
                    'id' => $aiMessage->id,
                    'message' => $aiMessage->message,
                    'is_user' => false,
                    'created_at' => $aiMessage->created_at->format('Y-m-d H:i:s'),
                ],
                'requirements_added' => $requirementsAdded,
                'requirements_removed' => $requirementsRemoved,
            ]);
        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Import candidates from Workable.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importFromWorkable(Request $request, Project $project, WorkableService $workableService): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'workable_candidates' => 'required|array',
            'workable_candidates.*' => 'string',
        ]);

        $setting = WorkableSetting::where('is_active', true)->first();
        if (!$setting) {
            return redirect()->route('projects.candidates.index', $project)
                ->with('error', 'No active Workable settings found.');
        }

        $imported = 0;
        $failed = 0;

        foreach ($validated['workable_candidates'] as $candidateId) {
            try {
                $data = $workableService->getCandidate($setting, $candidateId);
                $info = $data['candidate'] ?? $data;

                // Extract name information
                $name = $info['name'] ?? '';
                $firstName = $info['firstname'] ?? '';
                $lastName = $info['lastname'] ?? '';
                
                // If firstname/lastname are not provided, parse from full name
                if (empty($firstName) && empty($lastName) && !empty($name)) {
                    [$firstName, $lastName] = array_pad(explode(' ', $name, 2), 2, '');
                }

                // Extract location information
                $location = null;
                if (isset($info['location']['location_str'])) {
                    $location = $info['location']['location_str'];
                } elseif (isset($info['address'])) {
                    $location = $info['address'];
                }

                // Extract LinkedIn URL from social profiles
                $linkedinUrl = null;
                if (isset($info['social_profiles']) && is_array($info['social_profiles'])) {
                    foreach ($info['social_profiles'] as $profile) {
                        if (isset($profile['type']) && $profile['type'] === 'linkedin' && isset($profile['url'])) {
                            $linkedinUrl = $profile['url'];
                            break;
                        }
                    }
                }

                // Extract current company from experience entries (most recent)
                $currentCompany = null;
                if (isset($info['experience_entries']) && is_array($info['experience_entries']) && !empty($info['experience_entries'])) {
                    // Get the most recent experience (assuming they're ordered)
                    $recentExperience = $info['experience_entries'][0];
                    $currentCompany = $recentExperience['company'] ?? null;
                }

                // Prepare comprehensive parsed data from Workable
                $workableParsedData = [
                    'workable_id' => $info['id'] ?? $candidateId,
                    'workable_profile_url' => $info['profile_url'] ?? null,
                    'headline' => $info['headline'] ?? null,
                    'image_url' => $info['image_url'] ?? null,
                    'stage' => $info['stage'] ?? null,
                    'stage_kind' => $info['stage_kind'] ?? null,
                    'disqualified' => $info['disqualified'] ?? false,
                    'disqualified_at' => $info['disqualified_at'] ?? null,
                    'withdrew' => $info['withdrew'] ?? false,
                    'disqualification_reason' => $info['disqualification_reason'] ?? null,
                    'hired_at' => $info['hired_at'] ?? null,
                    'sourced' => $info['sourced'] ?? false,
                    'domain' => $info['domain'] ?? null,
                    'cover_letter' => $info['cover_letter'] ?? null,
                    'summary' => $info['summary'] ?? null,
                    'resume_url' => $info['resume_url'] ?? null,
                    'education_entries' => $info['education_entries'] ?? [],
                    'experience_entries' => $info['experience_entries'] ?? [],
                    'skills' => $info['skills'] ?? [],
                    'answers' => $info['answers'] ?? [],
                    'social_profiles' => $info['social_profiles'] ?? [],
                    'tags' => $info['tags'] ?? [],
                    'location_details' => $info['location'] ?? null,
                    'job_details' => $info['job'] ?? null,
                    'account_details' => $info['account'] ?? null,
                ];

                // Determine status based on Workable stage
                $status = 'new';
                if (isset($info['stage_kind'])) {
                    $status = match($info['stage_kind']) {
                        'applied' => 'new',
                        'sourced' => 'contacted',
                        'interviewing' => 'interviewing',
                        'offered' => 'offered',
                        'hired' => 'hired',
                        'disqualified', 'rejected' => 'rejected',
                        'withdrawn' => 'withdrawn',
                        default => 'new',
                    };
                }

                // Download and store resume if available
                $resumePath = null;
                $resumeText = null;
                if (isset($info['resume_url']) && !empty($info['resume_url'])) {
                    try {
                        $resumeData = $this->downloadAndStoreResume($info['resume_url'], $candidateId, $project->id, $firstName, $lastName);
                        $resumePath = $resumeData['path'] ?? null;
                        $resumeText = $resumeData['text'] ?? null;
                    } catch (\Exception $e) {
                        Log::warning('Failed to download resume for candidate ' . $candidateId . ': ' . $e->getMessage());
                    }
                }

                // Create or update candidate
                Candidate::firstOrCreate([
                    'project_id' => $project->id,
                    'workable_id' => $candidateId,
                ], [
                    'user_id' => Auth::id(),
                    'first_name' => $firstName ?: 'Unknown',
                    'last_name' => $lastName ?: '',
                    'email' => $info['email'] ?? null,
                    'phone' => $info['phone'] ?? null,
                    'location' => $location,
                    'current_company' => $currentCompany,
                    'current_position' => $info['job']['title'] ?? null,
                    'linkedin_url' => $linkedinUrl,
                    'status' => $status,
                    'source' => 'workable',
                    'workable_url' => $info['profile_url'] ?? null,
                    'resume_path' => $resumePath,
                    'resume_text' => $resumeText,
                    'resume_parsed_data' => $workableParsedData,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Workable import error for candidate ' . $candidateId . ': ' . $e->getMessage());
            }
        }

        $message = "Imported {$imported} candidate(s).";
        if ($failed) {
            $message .= " {$failed} failed.";
            return redirect()->route('projects.candidates.index', $project)->with('warning', $message);
        }

        return redirect()->route('projects.candidates.index', $project)->with('success', $message);
    }

    /**
     * Download and store resume from Workable URL.
     *
     * @param string $resumeUrl
     * @param string $candidateId
     * @param string $firstName
     * @param string $lastName
     * @return array
     * @throws \Exception
     */
    private function downloadAndStoreResume(string $resumeUrl, string $candidateId, string $projectId, string $firstName, string $lastName): array
    {
        // Extract file extension from URL (before query parameters)
        $urlPath = parse_url($resumeUrl, PHP_URL_PATH);
        $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION)) ?: 'pdf';
        
        // Create a safe filename
        $safeName = Str::slug($firstName . '-' . $lastName . '-' . $candidateId . '-' . uniqid());
        $filename = $safeName . '.' . $extension;
        
        // Try downloading with Laravel HTTP client first
        $fileContent = null;
        $downloadMethod = 'http_client';
        
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,*/*',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
            ])
            ->withoutVerifying()
            ->timeout(60)
            ->retry(3, 1000)
            ->get($resumeUrl);
            
            if ($response->successful()) {
                $fileContent = $response->body();
            } else {
                Log::warning('HTTP client failed, trying cURL fallback', [
                    'candidate_id' => $candidateId,
                    'status' => $response->status()
                ]);
                throw new \Exception('HTTP client failed with status: ' . $response->status());
            }
        } catch (\Exception $e) {
            // Fallback to cURL if HTTP client fails
            Log::info('Trying cURL fallback for candidate ' . $candidateId);
            $downloadMethod = 'curl';
            
            $fileContent = $this->downloadWithCurl($resumeUrl);
            
            if ($fileContent === false) {
                Log::error('Both HTTP client and cURL failed to download resume', [
                    'candidate_id' => $candidateId,
                    'url' => $resumeUrl,
                    'http_error' => $e->getMessage()
                ]);
                throw new \Exception('Failed to download resume with both HTTP client and cURL');
            }
        }
        
        // Validate that we got actual file content
        if (empty($fileContent)) {
            throw new \Exception('Downloaded file is empty');
        }
        
        // Check if it's actually a PDF by looking at file signature
        if ($extension === 'pdf' && !str_starts_with($fileContent, '%PDF')) {
            Log::warning('Downloaded file does not appear to be a valid PDF', [
                'candidate_id' => $candidateId,
                'first_bytes' => bin2hex(substr($fileContent, 0, 20))
            ]);
        }
        
        Log::info('Successfully downloaded resume for candidate ' . $candidateId, [
            'size' => strlen($fileContent),
            'extension' => $extension,
            'filename' => $filename,
            'method' => $downloadMethod,
            'url' => $resumeUrl,
            'first_bytes' => bin2hex(substr($fileContent, 0, 10))
        ]);
        
        // Store the file
        $path = 'resumes/project_' . $projectId . '/user_' . Auth::id() . '/' . $filename;
        Storage::disk('private')->put($path, $fileContent);
        
        // Extract text from the resume using existing method
        $resumeText = null;
        try {
            // Create a temporary file to use with the existing extractTextFromResume method
            $tempPath = tempnam(sys_get_temp_dir(), 'workable_resume_');
            $tempFile = $tempPath . '.' . $extension;
            file_put_contents($tempFile, $fileContent);
            
            // Create a mock UploadedFile-like object
            $mockFile = new class($tempFile, $extension) {
                private $path;
                private $extension;
                
                public function __construct($path, $extension) {
                    $this->path = $path;
                    $this->extension = $extension;
                }
                
                public function getPathname() {
                    return $this->path;
                }
                
                public function getClientOriginalExtension() {
                    return $this->extension;
                }
            };
            
            $resumeText = $this->extractTextFromResume($mockFile);
            
            // Clean up temporary file
            unlink($tempFile);
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract text from resume for candidate ' . $candidateId . ': ' . $e->getMessage());
        }
        
        return [
            'path' => $path,
            'text' => $resumeText,
        ];
    }

    
    /**
     * Batch upload resumes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batchUpload(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'resumes' => 'required|array|min:1',
            'resumes.*' => 'required|file|mimes:pdf,doc,docx|max:10240', // Max 10MB per file
        ]);

        $uploadedCount = 0;
        $failedFiles = [];

        foreach ($validated['resumes'] as $file) {
            try {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $path = $file->storeAs(
                    'resumes/project_' . $project->id . '/user_' . Auth::id(),
                    Str::slug($originalName) . '_' . uniqid() . '.' . $extension,
                    'private'
                );

                $resumeText = $this->extractTextFromResume($file);

                // Extract details using the new method
                $extractedDetails = $this->extractDetailsFromText($resumeText);

                // Attempt to parse name (basic split)
                $nameParts = explode(' ', $originalName, 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '(Uploaded CV)';


                Candidate::create([
                    'project_id' => $project->id,
                    'user_id' => Auth::id(),
                    'first_name' => $firstName, // Use parsed name if possible
                    'last_name' => $lastName,
                    'email' => $extractedDetails['email'], // Use extracted email
                    'phone' => $extractedDetails['phone'], // Use extracted phone
                    'location' => $extractedDetails['location'], // Use extracted location
                    'resume_path' => $path,
                    'resume_text' => $resumeText,
                    'status' => 'new',
                    'source' => 'batch_upload',
                ]);
                $uploadedCount++;

                // Optionally trigger individual analysis here if desired,
                // but batch analysis via project_index is likely better
                // $candidate = Candidate::latest()->first(); // Get the just created candidate
                // $this->analyzeCandidate($candidate);

            } catch (\Exception $e) {
                $failedFiles[] = $file->getClientOriginalName();
            }
        }

        $message = "Successfully processed {$uploadedCount} CV(s) via batch upload.";
        if (!empty($failedFiles)) {
            $message .= " Failed to upload/process: " . implode(', ', $failedFiles);
            return redirect()->route('projects.candidates.index', $project)
                         ->with('warning', $message);
        }

        return redirect()->route('projects.candidates.index', $project)
                     ->with('success', $message);
    }

    /**
     * Extract text content from a resume file (PDF, DOC, DOCX).
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string|null
     */
    private function extractTextFromResume($file): ?string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getPathname();

        try {
            if ($extension === 'pdf') {
                $parser = new Parser();
                $pdf = $parser->parseFile($filePath);
                return $pdf->getText();
            } elseif ($extension === 'docx') {
                $phpWord = IOFactory::load($filePath, 'Word2007');
                $text = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text .= $element->getText() . ' ';
                        } elseif (method_exists($element, 'getElements')) { // Handle containers like TextRun
                             foreach ($element->getElements() as $innerElement) {
                                 if (method_exists($innerElement, 'getText')) {
                                     $text .= $innerElement->getText();
                                 }
                             }
                             $text .= ' '; // Add space after container
                        }
                    }
                     $text .= "\n"; // Add newline between sections
                }
                return trim($text);
            } elseif ($extension === 'doc') {
                 // Basic DOC handling - limited support
                 // Note: .doc files are binary format and text extraction is limited
                 // Recommend users to save as DOCX or PDF for better results
                 try {
                     $content = file_get_contents($filePath);
                     // Try to extract readable text from binary content
                     // This is a basic approach and may not work perfectly
                     $text = '';
                     if ($content !== false) {
                         // Remove null bytes and control characters
                         $content = str_replace(["\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08", "\x0B", "\x0C", "\x0E", "\x0F"], '', $content);
                         // Extract printable characters
                         $text = preg_replace('/[^\x20-\x7E\x0A\x0D]/', ' ', $content);
                         // Clean up multiple spaces
                         $text = preg_replace('/\s+/', ' ', $text);
                         $text = trim($text);
                     }
                     return $text ?: 'Unable to extract text from DOC file. Please convert to PDF or DOCX for better results.';
                 } catch (\Exception $e) {
                     Log::warning('Failed to extract text from DOC file: ' . $e->getMessage());
                     return 'Unable to extract text from DOC file. Please convert to PDF or DOCX for better results.';
                 }
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Trigger AI analysis for a single candidate.
     * This might be called after upload or manually.
     *
     * @param Candidate $candidate
     * @param AISetting|null $aiSetting Specific AI setting to use (optional)
     * @param string|null $model Specific model to use (optional)
     * @param int|null $promptId Specific prompt ID to use (optional)
     * @return void
     */
    private function analyzeCandidate(Candidate $candidate, ?AISetting $aiSetting = null, ?string $model = null, ?int $promptId = null): void
    {
        // If no specific setting is provided, try to find a default or the first available one
        if (!$aiSetting) {
            $aiSetting = AISetting::where('is_default', true)
                                ->active()
                                ->first();
            if (!$aiSetting) {
                 $aiSetting = AISetting::active()
                                     ->first();
            }
        }

        if (!$aiSetting) {
            $candidate->status = 'analysis_failed';
            $candidate->save();
            return;
        }

        // If no specific model provided, use the first enabled model from the setting
        if (!$model) {
             $enabledModels = $aiSetting->models ?? [];
             if (empty($enabledModels)) {
                  $candidate->status = 'analysis_failed';
                  $candidate->save();
                  return;
             }
             $model = $enabledModels[0]; // Use the first available model
        } elseif (!in_array($model, $aiSetting->models ?? [])) {
             $candidate->status = 'analysis_failed';
             $candidate->save();
             return;
        }

        // If no specific prompt ID provided, check if there's a default prompt for the feature
        if ($promptId === null) {
             $defaultPrompt = AIPrompt::where('feature', 'cv_analyzer')
                                     ->where('is_default', true)
                                     ->first();
             $aiPrompt = $defaultPrompt; // Use default or null if none exists
             $promptId = $defaultPrompt?->id; // Get ID if default exists
        } else {
             $aiPrompt = AIPrompt::find($promptId);
             // Optional: Add validation to ensure the found prompt is compatible
             if ($aiPrompt && (($aiPrompt->provider && $aiPrompt->provider !== $aiSetting->provider) || ($aiPrompt->model && $aiPrompt->model !== $model))) {
                  $aiPrompt = null; // Fallback
             }
        }


        if (empty($candidate->resume_text)) {
            $candidate->status = 'analysis_failed';
            $candidate->save();
            return;
        }

        $project = $candidate->project;
        if (!$project) {
            $candidate->status = 'analysis_failed';
            $candidate->save();
            return;
        }

        $requirementsText = $project->requirements->map(function ($req) {
            return "- " . $req->name . ($req->is_required ? ' (Required)' : '') . ' [Weight: ' . $req->weight . ']';
        })->implode("\n");

        $candidate->status = 'analyzing';
        $candidate->save(); // Update status before calling AI

        try {
            $aiService = new AIService();
            $analysisResult = $aiService->analyzeCvAgainstRequirements(
                $project,
                $aiSetting,
                $model,
                $aiPrompt, // Pass the prompt object or null
                $candidate->resume_text,
                $requirementsText
            );

            if ($analysisResult['error']) {
                $candidate->status = 'analysis_failed';
            } else {
                $candidate->match_score = $analysisResult['match_score'];
                $candidate->status = $analysisResult['status']; // Should be 'analyzed' if score parsed
                
                // Ensure analysis_details is properly formatted JSON
                $analysisDetails = $analysisResult['analysis_details'];
                
                // If it's already an array, encode it to a clean JSON string
                if (is_array($analysisDetails)) {
                    $candidate->analysis_details = json_encode($analysisDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                }
                // If it's a string that might contain JSON, try to decode and re-encode it
                else if (is_string($analysisDetails)) {
                    // Try to decode the string
                    $decoded = json_decode($analysisDetails, true);
                    
                    // If it's valid JSON, re-encode it properly
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $candidate->analysis_details = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                    }
                    // Otherwise, store it as is
                    else {
                        $candidate->analysis_details = $analysisDetails;
                    }
                }
                // For any other case, store as is
                else {
                    $candidate->analysis_details = $analysisDetails;
                }
                
            }

        } catch (\Exception $e) {
            $candidate->status = 'analysis_failed';
        } finally {
             $candidate->last_analyzed_at = now();
             $candidate->save();
        }
    }

    /**
     * Trigger analysis for all candidates in a project (used by chat).
     * NOTE: This currently runs synchronously. Consider moving to jobs for chat updates too.
     *
     * @param Project $project
     * @return void
     */
    private function analyzeAllCandidates(Project $project): void
    {
         // Fetch default/first setting for analysis if not specified
         $aiSetting = AISetting::where('is_default', true)
                                ->active()
                                ->first() ??
                        AISetting::active()
                                ->first();

         if (!$aiSetting) {
              return;
         }
         $model = $aiSetting->models[0] ?? null; // Use first available model
         if (!$model) {
              return;
         }
         // Use default prompt or null
         $defaultPrompt = AIPrompt::where('feature', 'cv_analyzer')->where('is_default', true)->first();


         foreach ($project->candidates as $candidate) {
             try {
                 $this->analyzeCandidate($candidate, $aiSetting, $model, $defaultPrompt?->id);
             } catch (\Exception $e) {
                 // Continue to next candidate
             }
         }
    }

    /**
     * Build the prompt for the CV Analyzer chat.
     *
     * @param Project $project
     * @param string $message
     * @param int|null $candidateId
     * @return string
     */
    private function buildChatPrompt(Project $project, string $message, ?int $candidateId): string
    {
        // Create a conversational prompt instead of a JSON-formatted one
        $prompt = "You are an AI assistant helping a recruiter analyze candidates for a job position.\n\n";
        
        // Add project context
        $prompt .= "Job Title: " . ($project->job_title ?? 'Not specified') . "\n";
        $prompt .= "Company: " . ($project->company_name ?? 'Not specified') . "\n\n";
        
        // Add requirements
        $requirements = $project->activeRequirements()->get();
        if ($requirements->count() > 0) {
            $prompt .= "Job Requirements:\n";
            foreach ($requirements as $req) {
                $weight = $req->weight * 100;
                $required = $req->is_required ? ' (Required)' : '';
                $prompt .= "- {$req->name} - {$weight}%{$required}\n";
            }
            $prompt .= "\n";
        }
        
        // Add candidate context if specified
        if ($candidateId) {
            $candidate = Candidate::find($candidateId);
            if ($candidate) {
                $prompt .= "Currently analyzing candidate: {$candidate->first_name} {$candidate->last_name}\n\n";
                
                // Include a summary of the candidate's resume
                if ($candidate->resume_text) {
                    $prompt .= "Resume Summary: " . substr($candidate->resume_text, 0, 500) . "...\n\n";
                }
            }
        }
        
        // Add chat history context (last 5 messages)
        $chatHistory = CandidateChatMessage::where('project_id', $project->id)
            ->when($candidateId, function ($query) use ($candidateId) {
                return $query->where('candidate_id', $candidateId);
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->reverse();
            
        if ($chatHistory->count() > 0) {
            $prompt .= "Recent conversation:\n";
            foreach ($chatHistory as $chat) {
                $role = $chat->is_user ? "Recruiter" : "Assistant";
                $prompt .= "{$role}: {$chat->message}\n";
            }
            $prompt .= "\n";
        }
        
        // Add the current message
        $prompt .= "Recruiter: {$message}\n";
        $prompt .= "Assistant: ";
        
        // Instructions for response format
        $prompt .= "\n\nPlease respond in a helpful, conversational manner. Provide specific insights about candidates and requirements when asked. Do not use JSON formatting in your response.";
        
        return $prompt;
        $prompt = "You are helping a recruiter analyze candidates for the following position:\n\n";
        $prompt .= "Job Title: {$project->job_title}\n";
        $prompt .= "Department: {$project->department}\n";
        $prompt .= "Company: {$project->company_name}\n";
        $prompt .= "Location: {$project->location}\n\n";
        
        $prompt .= "Current Requirements:\n";
        $requirements = $project->activeRequirements()->get();
        foreach ($requirements as $requirement) {
            $prompt .= "- {$requirement->name} (Type: {$requirement->type}, Weight: {$requirement->weight}, Required: " . ($requirement->is_required ? 'Yes' : 'No') . ")\n";
        }
        
        $prompt .= "\nThe recruiter is asking: {$message}\n\n";
        
        if ($candidateId) {
            $candidate = Candidate::find($candidateId);
            if ($candidate) {
                $prompt .= "They are specifically asking about the candidate: {$candidate->full_name}\n";
                $prompt .= "Candidate Details:\n";
                $prompt .= "- Current Company: {$candidate->current_company}\n";
                $prompt .= "- Current Position: {$candidate->current_position}\n";
                $prompt .= "- Location: {$candidate->location}\n";
                $prompt .= "- Match Score: {$candidate->match_score_percentage}\n\n";
                
                $prompt .= "Resume Text:\n{$candidate->resume_text}\n\n";
            }
        }
        
        $prompt .= "Please respond to the recruiter's question. If they are asking to add or remove requirements, identify those changes.";
        
        return $prompt;
    }

    /**
     * Trigger analysis for all candidates in a project via Job Dispatch.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function analyzeAll(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'ai_setting_id' => 'required|exists:ai_settings,id',
            'ai_model' => 'required|string',
            'ai_prompt_id' => 'nullable|exists:ai_prompts,id', // Allow null, but validate if present
        ]);

        // Ensure the selected model is valid for the selected setting
        $setting = AISetting::findOrFail($validated['ai_setting_id']);
        if (!in_array($validated['ai_model'], $setting->models ?? [])) {
            return back()->withErrors(['ai_model' => 'The selected model is not valid for the chosen AI setting.'])->withInput();
        }
        

        // Ensure the selected prompt (if any) is compatible
        if (!empty($validated['ai_prompt_id'])) {
            $prompt = AIPrompt::find($validated['ai_prompt_id']);
            if (!$prompt || ($prompt->provider && $prompt->provider !== $setting->provider) || ($prompt->model && $prompt->model !== $validated['ai_model'])) {
                 return back()->withErrors(['ai_prompt_id' => 'The selected prompt is not compatible with the chosen AI setting or model.'])->withInput();
            }
        }

        // Check if there are candidates with resume text to analyze
        $candidateCount = $project->candidates()->whereNotNull('resume_text')->count();
        if ($candidateCount === 0) {
            return redirect()->route('projects.candidates.index', $project)
                         ->with('info', 'No candidates with resume text found to analyze.');
        }

        // Dispatch the job
        AnalyzeAllCandidatesJob::dispatch(
            $project->id,
            $validated['ai_setting_id'],
            $validated['ai_model'],
            $validated['ai_prompt_id'] // Pass null if not provided
        );

        return redirect()->route('projects.candidates.index', $project)
                     ->with('success', 'Batch analysis job dispatched for ' . $candidateCount . ' candidate(s). Candidates will be analyzed in the background.');
    }

    /**
     * Extract basic details from resume text using regex.
     * NOTE: This is a basic implementation and may not be accurate for all resume formats.
     *
     * @param string|null $text
     * @return array
     */
    private function extractDetailsFromText(?string $text): array
    {
        if (empty($text)) {
            return [
                'email' => null,
                'phone' => null,
                'location' => null,
                'current_company' => null,
                'current_position' => null
            ];
        }

        $details = [
            'email' => null,
            'phone' => null,
            'location' => null,
            'current_company' => null,
            'current_position' => null
        ];

        // Email extraction - improved regex
        if (preg_match('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/', $text, $matches)) {
            $details['email'] = $matches[0];
        }

        // Phone extraction - improved to handle more formats
        if (preg_match('/(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $text, $matches)) {
            $details['phone'] = preg_replace('/[-.\s\(\)]+/', '', $matches[0]);
        }

        // Location extraction - improved to handle more formats
        if (preg_match('/\b([A-Za-z\s]+),\s*([A-Z]{2})\b/', $text, $matches)) { // City, ST
            $details['location'] = trim($matches[0]);
        } elseif (preg_match('/\b([A-Za-z\s]+),\s*([A-Za-z\s]+)\b/', $text, $matches)) { // City, State
            if (strlen($matches[1]) > 2 && strlen($matches[2]) > 2 && !preg_match('/(Experience|Education|Skills)/i', $matches[0])) {
                $details['location'] = trim($matches[0]);
            }
        }

        // Current company extraction
        // Look for common patterns like "Company: XYZ" or "Currently at XYZ"
        if (preg_match('/(?:Company|Employer|Currently at|Currently working at|Working at|Employed at|Work at)[\s:]+([A-Za-z0-9\s&.,\-\'\"]+)(?:\r|\n|$|,|\.|;)/', $text, $matches)) {
            $details['current_company'] = trim($matches[1]);
        } elseif (preg_match('/([A-Za-z0-9\s&.,\-\'\"]+)(?:\s+Inc\.|\s+LLC|\s+Ltd\.|\s+Corporation|\s+Corp\.|\s+Company)/', $text, $matches)) {
            $details['current_company'] = trim($matches[0]);
        }

        // Current position extraction
        // Look for common patterns like "Position: XYZ" or "Title: XYZ"
        if (preg_match('/(?:Position|Title|Role|Job Title|Designation)[\s:]+([A-Za-z0-9\s&.,\-\'\"]+)(?:\r|\n|$|,|\.|;)/', $text, $matches)) {
            $details['current_position'] = trim($matches[1]);
        } elseif (preg_match('/(?:Senior|Junior|Lead|Principal|Staff|Software|Frontend|Backend|Full Stack|DevOps|Cloud|Data|AI|ML|Product|Project|Program|UX|UI)\s+(?:Engineer|Developer|Architect|Designer|Manager|Director|Consultant|Analyst|Scientist)/', $text, $matches)) {
            $details['current_position'] = trim($matches[0]);
        }

        return $details;
    }
    
    /**
     * Display the resume file.
     *
     * @param  \App\Models\Candidate  $candidate
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function viewResume(Candidate $candidate)
    {
        $project = $candidate->project;
        $this->authorize('view', $project);
        
        if (!$candidate->resume_path || !Storage::disk('private')->exists($candidate->resume_path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }
        
        $filename = basename($candidate->resume_path);
        $mimeType = Storage::disk('private')->mimeType($candidate->resume_path);
        
        return Storage::disk('private')->response($candidate->resume_path, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    /**
     * Download the resume file.
     *
     * @param  \App\Models\Candidate  $candidate
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadResume(Candidate $candidate)
    {
        $project = $candidate->project;
        $this->authorize('view', $project);
        
        if (!$candidate->resume_path || !Storage::disk('private')->exists($candidate->resume_path)) {
            return redirect()->back()->with('error', 'Resume file not found.');
        }
        
        $filename = basename($candidate->resume_path);
        $mimeType = Storage::disk('private')->mimeType($candidate->resume_path);
        
        return Storage::disk('private')->download($candidate->resume_path, $candidate->full_name . ' - Resume.' . pathinfo($filename, PATHINFO_EXTENSION), [
            'Content-Type' => $mimeType,
        ]);
    }

    /**
     * Analyze a specific candidate.
     *
     * @param  \App\Models\Candidate  $candidate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function analyze(Request $request, Candidate $candidate): RedirectResponse
    {
        $project = $candidate->project;
        $this->authorize('update', $project);
        
         $validated = $request->validate([
            'ai_setting_id' => 'required|exists:ai_settings,id',
            'ai_model' => 'required|string',
            'ai_prompt_id' => 'nullable|exists:ai_prompts,id',
        ]);

        $setting = AISetting::findOrFail($validated['ai_setting_id']);
        if (!in_array($validated['ai_model'], $setting->models ?? [])) {
            return back()->withErrors(['ai_model' => 'The selected model is not valid for the chosen AI setting.'])->withInput();
        }

        $promptId = $validated['ai_prompt_id'] ?? null;
        if ($promptId) {
            $prompt = AIPrompt::find($promptId);
            if (!$prompt || ($prompt->provider && $prompt->provider !== $setting->provider) || ($prompt->model && $prompt->model !== $validated['ai_model'])) {
                return back()->withErrors(['ai_prompt_id' => 'The selected prompt is not compatible with the chosen AI setting or model.'])->withInput();
            }
        }

        try {
            $this->analyzeCandidate($candidate, $setting, $validated['ai_model'], $promptId);

            $candidate->refresh();
            if ($candidate->status === 'analyzed') {
                return redirect()->back()->with('success', 'Candidate analyzed successfully.');
            }

            return redirect()->back()->with('error', 'Candidate analysis failed.');

            // return redirect()->back()->with('success', 'Candidate analyzed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to analyze candidate: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle batch actions for candidates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batchAction(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        
        $validated = $request->validate([
            'action' => 'required|string|in:analyze,delete',
            'selected_candidates' => 'required|array',
            'selected_candidates.*' => 'exists:candidates,id',
        ]);
        
        $action = $validated['action'];
        $selectedCandidateIds = $validated['selected_candidates'];
        $count = count($selectedCandidateIds);
        
        // Verify all candidates belong to this project
        $validCandidates = Candidate::whereIn('id', $selectedCandidateIds)
            ->where('project_id', $project->id)
            ->get();
        
        if ($validCandidates->count() !== $count) {
            return redirect()->route('projects.candidates.index', $project)
                ->with('error', 'Some selected candidates do not belong to this project.');
        }
        
        try {
            if ($action === 'analyze') {
                // Get default AI setting
                $aiSetting = AISetting::active()->first();
                
                if (!$aiSetting) {
                    return redirect()->route('projects.candidates.index', $project)
                        ->with('error', 'No active AI setting found for analysis.');
                }
                
                // Get default model
                $model = $aiSetting->default_model ?? $aiSetting->models[0] ?? null;
                
                if (!$model) {
                    return redirect()->route('projects.candidates.index', $project)
                        ->with('error', 'No models configured for the selected AI setting.');
                }
                
                // Analyze each candidate
                foreach ($validCandidates as $candidate) {
                    $this->analyzeCandidate($candidate, $aiSetting, $model);
                }
                
                return redirect()->route('projects.candidates.index', $project)
                    ->with('success', "{$count} candidates analyzed successfully.");
            } elseif ($action === 'delete') {
                // Delete each candidate
                foreach ($validCandidates as $candidate) {
                    // Delete resume file if exists
                    if ($candidate->resume_path) {
                        Storage::disk('private')->delete($candidate->resume_path);
                    }
                    
                    $candidate->delete();
                }
                
                return redirect()->route('projects.candidates.index', $project)
                    ->with('success', "{$count} candidates deleted successfully.");
            }
            
            return redirect()->route('projects.candidates.index', $project)
                ->with('error', 'Invalid action specified.');
        } catch (\Exception $e) {
            
            return redirect()->route('projects.candidates.index', $project)
                ->with('error', "Failed to {$action} candidates: " . $e->getMessage());
        }
    }

    /**
     * Download file using cURL as fallback method.
     *
     * @param string $url
     * @return string|false
     */
    private function downloadWithCurl(string $url)
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,*/*',
                'Accept-Language: en-US,en;q=0.9',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Cache-Control: no-cache',
            ],
        ]);
        
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($data === false || !empty($error)) {
            Log::error('cURL download failed', [
                'url' => $url,
                'error' => $error,
                'http_code' => $httpCode
            ]);
            return false;
        }
        
        if ($httpCode !== 200) {
            Log::error('cURL download failed with HTTP code', [
                'url' => $url,
                'http_code' => $httpCode
            ]);
            return false;
        }
        
        return $data;
    }
}
