<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\JobDescription;
use App\Models\QualifyingQuestion;
use App\Models\JobDescriptionTemplate;
use App\Models\AISetting;
use App\Models\AIPrompt;
// Removed AIUsageLog use statement
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\ModelRegistryService;

class JobDescriptionController extends Controller
{
    /**
     * Display a listing of all job descriptions.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $jobDescriptions = JobDescription::with('project')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $templates = JobDescriptionTemplate::active()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('job_descriptions.index', compact('jobDescriptions', 'templates'));
    }
    
    /**
     * Display a listing of the job descriptions for the specified project.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function projectIndex(Project $project): View
    {
        $this->authorize('view', $project);
        
        $jobDescriptions = $project->jobDescriptions()
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $templates = JobDescriptionTemplate::active()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('job_descriptions.project_index', compact('project', 'jobDescriptions', 'templates'));
    }
    
    /**
     * Display the specified job description.
     *
     * @param  \App\Models\JobDescription  $jobDescription
     * @return \Illuminate\View\View
     */
    public function show(JobDescription $jobDescription): View
    {
        $this->authorize('view', $jobDescription->project);
        
        $project = $jobDescription->project;
        $qualifyingQuestions = $jobDescription->qualifyingQuestions()->ordered()->get();
        
        return view('job_descriptions.show', compact('jobDescription', 'project', 'qualifyingQuestions'));
    }
    
    /**
     * Display the job description for the specified project.
     *
     * @param  \App\Models\Project  $project
     * @param  \App\Models\JobDescription|null  $jobDescription
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function projectShow(Project $project, JobDescription $jobDescription = null)
    {
        $this->authorize('view', $project);
        
        // If no specific job description is provided, get the latest one
        if (!$jobDescription) {
            $jobDescription = $project->latestJobDescription();
            
            if (!$jobDescription) {
                return redirect()->route('job-descriptions.create')
                    ->with('info', 'No job description found. Create one now.');
            }
        } else {
            // Ensure the job description belongs to the project
            if ($jobDescription->project_id !== $project->id) {
                abort(404);
            }
        }
        
        $qualifyingQuestions = $jobDescription->qualifyingQuestions()->ordered()->get();
        
        return view('job_descriptions.show', compact('project', 'jobDescription', 'qualifyingQuestions'));
    }

    /**
     * Show the form for creating a new job description.
     *
     * @return \Illuminate\View\View
     */
    // Inject ModelRegistryService
    public function create(ModelRegistryService $modelRegistryService): View
    {
        // Get available projects
        $projects = Project::orderBy('title')
            ->where('user_id', Auth::id())->get();
        
        // Get available templates
        $templates = JobDescriptionTemplate::active()->get();
        
        // Get AI providers for job description generation
        $aiProviders = AISetting::active()
            ->get(); // $aiProviders is now a collection of AISetting models
            
        // Get available prompts for job description generation
        $prompts = AIPrompt::where('feature', 'job_description')->orderBy('name')->get();
        
        $providerModels = $modelRegistryService->getModels(); // Fetch dynamic map from service
        
        // Prepare templates data for JavaScript
        $templatesData = $templates->mapWithKeys(function ($template) {
            // Get all template attributes
            $templateData = $template->toArray();
                        
            return [$template->id => $templateData];
        });

        return view('job_descriptions.create', compact(
            'projects', 'templates', 'aiProviders', 'prompts', 'providerModels', 'templatesData'
        ));
    }

    /**
     * Store a newly created job description in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Check which form was submitted
        $formType = $request->input('form_type', 'job_description');
        
        if ($formType === 'job_description') {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'overview' => 'nullable|string',
                'responsibilities' => 'nullable|string',
                'requirements_non_negotiable' => 'nullable|string',
                'requirements_preferred' => 'nullable|string',
                'compensation_range' => 'nullable|string|max:255',
                'benefits' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'disclaimer' => 'nullable|string',
                'industry' => 'nullable|string|max:255',
                'experience_level' => 'nullable|string|max:255',
                'employment_type' => 'nullable|string|max:255',
                'education_requirements' => 'nullable|string|max:255',
                'skills_required' => 'nullable|string',
                'skills_preferred' => 'nullable|string',
                'template_id' => 'nullable|exists:job_description_templates,id',
                'status' => 'nullable|string|in:draft,review,approved,published',
            ]);
            
            $project = Project::findOrFail($validated['project_id']);
            $this->authorize('update', $project);

            // --- Versioning Logic ---
            $latestJD = $project->latestJobDescription();
            if ($latestJD) {
                $jobDescription = $latestJD->createNewVersion();
                $jobDescription->fill($validated); // Fill with new form data
            } else {
                $jobDescription = new JobDescription($validated);
                $jobDescription->version = 1; // Set initial version
            }
            // --- End Versioning Logic ---

            $jobDescription->user_id = Auth::id();
            $jobDescription->status = $validated['status'] ?? 'draft';
            
            // Apply template if selected
            if (!empty($validated['template_id'])) {
                $template = JobDescriptionTemplate::findOrFail($validated['template_id']);
                $jobDescription->template_used = $template->id;
            }
            
            $project->jobDescriptions()->save($jobDescription); // Save new or versioned JD
            
            // Handle qualifying questions if submitted with the JD form (unlikely but possible)
            if ($request->has('questions')) {
                 $this->saveQualifyingQuestions($request, $jobDescription);
            }

            return redirect()->route('job-descriptions.show', $jobDescription)
                ->with('success', 'Job description created successfully.');

        } else {
            // This is the qualifying questions form (likely submitted from edit page)
            // This logic might need review if creating questions without a JD is intended
            $request->validate([
                'project_id' => 'required|exists:projects,id',
                'job_description_id' => 'required|exists:job_descriptions,id', // Need existing JD ID
            ]);
            
            $jobDescription = JobDescription::findOrFail($request->job_description_id);
            $this->authorize('update', $jobDescription->project);

            if ($request->has('questions')) {
                $this->saveQualifyingQuestions($request, $jobDescription);
            }
            
            return redirect()->route('job-descriptions.show', $jobDescription)
                ->with('success', 'Qualifying questions updated successfully.');
        }
    }

    /**
     * Show the form for editing the job description.
     *
     * @param  \App\Models\JobDescription  $jobDescription
     * @return \Illuminate\View\View
     */
    public function edit(JobDescription $jobDescription): View
    {
        $project = $jobDescription->project;
        $this->authorize('update', $project);
        
        $qualifyingQuestions = $jobDescription->qualifyingQuestions()->ordered()->get();
        $templates = JobDescriptionTemplate::active()->get();
        
        return view('job_descriptions.edit', compact('jobDescription', 'project', 'qualifyingQuestions', 'templates'));
    }

    /**
     * Update the job description in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobDescription  $jobDescription
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, JobDescription $jobDescription): RedirectResponse
    {
        $project = $jobDescription->project;
        $this->authorize('update', $project);
        
        // --- Versioning Logic ---
        // Create a new version based on the one being edited
        $newVersion = $jobDescription->createNewVersion();
        // --- End Versioning Logic ---

        // Validate and update job description fields on the NEW version
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'overview' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'requirements_non_negotiable' => 'nullable|string',
            'requirements_preferred' => 'nullable|string',
            'compensation_range' => 'nullable|string|max:255',
            'benefits' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'disclaimer' => 'nullable|string',
            'industry' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'education_requirements' => 'nullable|string|max:255',
            'skills_required' => 'nullable|string',
            'skills_preferred' => 'nullable|string',
            'status' => 'nullable|string|in:draft,review,approved,published',
        ]);
        
        $newVersion->fill($validated); // Update the new version
        $newVersion->status = $validated['status'] ?? 'draft'; // Ensure status is set
        $newVersion->save(); // Save the updated new version
        
        // Handle qualifying questions for the NEW version
        if ($request->has('questions')) {
            $this->saveQualifyingQuestions($request, $newVersion); // Save questions to the new version
        }
        
        return redirect()->route('job-descriptions.show', $newVersion) // Redirect to the new version
            ->with('success', 'Job description updated successfully (New Version Created).');
    }

    /**
     * Remove the job description from storage.
     *
     * @param  \App\Models\JobDescription  $jobDescription
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(JobDescription $jobDescription): RedirectResponse
    {
        $project = $jobDescription->project;
        $this->authorize('update', $project);
        
        // Maybe add logic here to prevent deleting the only version?
        // Or handle cascading deletes appropriately if needed.
        
        $jobDescription->delete(); // Soft delete
        
        // Redirect to project's JD list or general list
        return redirect()->route('projects.job_descriptions.index', $project) 
            ->with('success', 'Job description deleted successfully.');
    }

    /**
     * Generate a job description using AI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'ai_setting_id' => 'required|exists:ai_settings,id', 
            'ai_model' => 'required|string',
            'template_id' => 'nullable|exists:job_description_templates,id',
            'industry' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'ai_prompt_id' => 'nullable|exists:ai_prompts,id',
        ]);
        
        $project = Project::findOrFail($validated['project_id']);
        $this->authorize('update', $project);
        
        try {
            // Get the selected AI Setting
            $aiSetting = AISetting::findOrFail($validated['ai_setting_id']);
            
            // Ensure the selected model is actually available for this setting
            // if (!in_array($validated['ai_model'], $aiSetting->models ?? [])) {
            //      return redirect()->back()->withInput()->with('error', 'The selected AI model is not available for the chosen AI setting.');
            // }
            
            // Get prompt for job description generation
            if (!empty($validated['ai_prompt_id'])) {
                $prompt = AIPrompt::findOrFail($validated['ai_prompt_id']);
            } else {
                // Fallback to default prompt logic if no specific prompt was selected
                try {
                    $prompt = AIPrompt::forFeature('job_description')
                        ->forProvider($aiSetting->provider) 
                        ->forModel($validated['ai_model'])
                        ->default()
                        ->firstOrFail();
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    // Create a default prompt if none exists, asking for JSON output
                    $defaultPromptTemplate = "Create a job description for a {{job_title}} position at {{company_name}} based on the following details.\n\n";
                    $defaultPromptTemplate .= "Company Information:\n";
                    $defaultPromptTemplate .= "- Company: {{company_name}}\n";
                    $defaultPromptTemplate .= "- Industry: {{industry}}\n";
                    $defaultPromptTemplate .= "- Size: {{company_size}}\n\n";
                    $defaultPromptTemplate .= "Job Details:\n";
                    $defaultPromptTemplate .= "- Title: {{job_title}}\n";
                    $defaultPromptTemplate .= "- Department: {{department}}\n";
                    $defaultPromptTemplate .= "- Location: {{location}}\n";
                    $defaultPromptTemplate .= "- Experience Level: {{experience_level}}\n";
                    $defaultPromptTemplate .= "- Compensation Range: {{salary_range}}\n"; 
                    $defaultPromptTemplate .= "- Employment Type: {{employment_type}}\n\n"; 
                    $defaultPromptTemplate .= "Required Skills:\n{{required_skills}}\n\n";
                    $defaultPromptTemplate .= "Preferred Skills:\n{{preferred_skills}}\n\n";
                    $defaultPromptTemplate .= "Education Requirements:\n{{education_requirements}}\n\n";
                    $defaultPromptTemplate .= "Format the entire response as a single JSON object with the following keys:\n"; 
                    $defaultPromptTemplate .= "- 'overview' (string)\n";
                    $defaultPromptTemplate .= "- 'responsibilities' (string, use bullet points or numbered lists)\n";
                    $defaultPromptTemplate .= "- 'requirements_non_negotiable' (string, use bullet points or numbered lists)\n";
                    $defaultPromptTemplate .= "- 'requirements_preferred' (string, use bullet points or numbered lists)\n";
                    $defaultPromptTemplate .= "- 'compensation_range' (string, e.g., '$100,000 - $120,000')\n";
                    $defaultPromptTemplate .= "- 'industry' (string)\n"; 
                    $defaultPromptTemplate .= "- 'benefits' (string, use bullet points or numbered lists)\n";
                    $defaultPromptTemplate .= "- 'disclaimer' (string, standard EEO statement)\n"; 
                    $defaultPromptTemplate .= "Ensure the output is only the JSON object, without any introductory text or markdown formatting like ```json.\n\nYour entire response must be only the valid JSON object specified above."; 
                    
                    // Create and save the prompt for future use
                    $prompt = new AIPrompt([
                        'feature' => 'job_description',
                        'name' => 'Default Job Description Prompt for ' . $aiSetting->provider . '/' . $validated['ai_model'], // Corrected variable name
                        'prompt_template' => $defaultPromptTemplate,
                        'provider' => $aiSetting->provider, 
                        'model' => $validated['ai_model'],
                        'is_default' => true,
                        'created_by' => Auth::id(),
                    ]);
                    $prompt->save();
                } 
            } 
            
            // Prepare data for prompt
            $promptData = [
                'project_title' => $project->title,
                'job_title' => $project->job_title ?? $project->title,
                'department' => $project->department,
                'location' => $project->location,
                'company_name' => $project->company_name,
                'company_size' => $project->company_size,
                'industry' => $validated['industry'] ?? $project->industry_details,
                'experience_level' => $validated['experience_level'] ?? $project->experience_level,
                'required_skills' => $project->required_skills,
                'preferred_skills' => $project->preferred_skills,
                'education_requirements' => $project->education_requirements,
                'employment_type' => $project->employment_type,
                'salary_range' => $project->salary_range,
            ];
            
            // Format prompt
            $formattedPrompt = $prompt->formatPrompt($promptData);
            // Append JSON instruction AFTER formatting
            $formattedPrompt .= "\n\nFormat the entire response as a single JSON object with the following keys:\n";
            $formattedPrompt .= "- 'overview' (string)\n";
            $formattedPrompt .= "- 'responsibilities' (string, use bullet points or numbered lists)\n";
            $formattedPrompt .= "- 'requirements_non_negotiable' (string, use bullet points or numbered lists)\n";
            $formattedPrompt .= "- 'requirements_preferred' (string, use bullet points or numbered lists)\n";
            $formattedPrompt .= "- 'compensation_range' (string, e.g., '$100,000 - $120,000')\n";
            $formattedPrompt .= "- 'industry' (string)\n";
            $formattedPrompt .= "- 'benefits' (string, use bullet points or numbered lists)\n";
            $formattedPrompt .= "- 'disclaimer' (string, standard EEO statement)\n";
            $formattedPrompt .= "Ensure the output is only the JSON object, without any introductory text or markdown formatting like ```json.\n\nYour entire response must be only the valid JSON object specified above.";
                    
            // Call the AI service to generate the job description
            $aiService = new \App\Services\AIService();
            $aiResponse = $aiService->generateContent(
                $aiSetting, 
                $validated['ai_model'],
                $formattedPrompt,
                [],
                Auth::id(),
                'job_description'
            );
            
            // Attempt to parse the JSON response directly
            $sections = json_decode($aiResponse['content'], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($sections)) {
                 // Attempt fallback parsing using the method defined below
                 $sections = $this->parseJobDescriptionContentFallback($aiResponse['content']);
            } 
            
            // Prepare data for saving/updating
             $jdData = [
                 'title' => $project->job_title ?? $project->title,
                 'overview' => $sections['overview'] ?? '',
                 'responsibilities' => is_array($sections['responsibilities'] ?? null) ? implode("\n", $sections['responsibilities']) : ($sections['responsibilities'] ?? ''),
                 'requirements_non_negotiable' => is_array($sections['requirements_non_negotiable'] ?? null) ? implode("\n", $sections['requirements_non_negotiable']) : ($sections['requirements_non_negotiable'] ?? ''),
                 'requirements_preferred' => is_array($sections['requirements_preferred'] ?? null) ? implode("\n", $sections['requirements_preferred']) : ($sections['requirements_preferred'] ?? ''),
                 'compensation_range' => $sections['compensation_range'] ?? ($project->salary_range ?? null),
                 'benefits' => is_array($sections['benefits'] ?? null) ? implode("\n", $sections['benefits']) : ($sections['benefits'] ?? ''),
                 'disclaimer' => $sections['disclaimer'] ?? '',
                 'industry' => $sections['industry'] ?? ($validated['industry'] ?? null),
                 'location' => $project->location,
                 'experience_level' => $validated['experience_level'] ?? $project->experience_level,
                 'employment_type' => $project->employment_type,
                 'education_requirements' => $project->education_requirements,
                 'skills_required' => $project->required_skills,
                 'skills_preferred' => $project->preferred_skills,
                 'ai_provider' => $aiSetting->provider,
                 'ai_model' => $validated['ai_model'],
                 'generated_at' => now(),
                 'generation_parameters' => [
                     'prompt' => $formattedPrompt, // Log the final prompt sent
                     'tokens_used' => $aiResponse['tokens_used'],
                     'cost' => $aiResponse['cost'],
                 ],
                 'status' => 'draft', 
                 'user_id' => Auth::id(),
                 'project_id' => $project->id, 
             ];

            // --- Versioning Logic ---
            $latestJD = $project->latestJobDescription();
            if ($latestJD) {
                $jobDescription = $latestJD->createNewVersion();
                $jobDescription->fill($jdData); 
            } else {
                $jdData['version'] = 1; 
                $jobDescription = new JobDescription($jdData);
            }
            // --- End Versioning Logic ---

            // Apply template if selected (do this *before* saving if creating new)
            if (!empty($validated['template_id'])) {
                $template = JobDescriptionTemplate::findOrFail($validated['template_id']);
                $jobDescription->template_used = $template->id;
            }
            
            $jobDescription->save(); // Save the new/updated version
            
            // --- Generate Qualifying Questions ---
            try {
                // Get prompt for qualifying questions
                 $qPrompt = AIPrompt::forFeature('qualifying_questions')
                     ->forProvider($aiSetting->provider) // Use same provider
                     ->forModel($validated['ai_model']) // Use same model
                     ->default()
                     ->first(); 
                 // Log::debug("Qualifying Question Prompt Found:", ['prompt_id' => $qPrompt?->id]); // Removed

                 if ($qPrompt) {
                     $qPromptData = [
                         'job_title' => $jobDescription->title,
                         'job_description_overview' => $jobDescription->overview,
                         'company_name' => $project->company_name,
                         'department' => $project->department,
                         'required_skills' => $project->required_skills,
                         'experience_level' => $project->experience_level,
                         'job_description_responsibilities' => $jobDescription->responsibilities,
                         'job_description_requirements' => $jobDescription->requirements_non_negotiable . "\n" . $jobDescription->requirements_preferred,
                         'compensation_range' => $jobDescription->compensation_range,
                         'location' => $jobDescription->location,
                     ];
                     $qFormattedPrompt = $qPrompt->formatPrompt($qPromptData);
                     
                     // Append JSON instruction AFTER formatting
                     $qFormattedPrompt .= "\n\nPlease provide 5-7 relevant qualifying questions based on the job description above. Format the output as a JSON array of objects. Each object should have keys: 'question' (string), 'type' (string: 'yes_no', 'multiple_choice', 'text', 'numeric'), and optionally 'options' (array of strings for multiple_choice). Example: [{'question': 'Do you have 5+ years of PHP experience?', 'type': 'yes_no'}, {'question': 'What is your desired salary?', 'type': 'numeric'}]\n\nYour entire response must be only the valid JSON array specified above."; 

                     $qResponse = $aiService->generateContent(
                         $aiSetting, 
                         $validated['ai_model'],
                         $qFormattedPrompt,
                         ['temperature' => 0.5], 
                         Auth::id(),
                         'qualifying_questions'
                     );

                     // Parse and save questions
                     $jsonContent = preg_replace('/^```json\s*|\s*```$/', '', trim($qResponse['content']));
                     $parsedQuestions = json_decode($jsonContent, true);
                     if (json_last_error() === JSON_ERROR_NONE && is_array($parsedQuestions)) {
                         // Log::debug("Parsed Qualifying Questions:", $parsedQuestions); // Removed
                         $order = 0;
                         foreach ($parsedQuestions as $qData) {
                             if (isset($qData['question']) && isset($qData['type'])) {
                                 // Assign data to variable first
                                 $qSaveData = [
                                     'job_description_id' => $jobDescription->id,
                                     'question' => $qData['question'],
                                     'type' => $qData['type'] ?? 'text',
                                     'options' => ($qData['type'] === 'multiple_choice' && isset($qData['options']) && is_array($qData['options'])) ? $qData['options'] : null,
                                     'required' => true, 
                                     'order' => $order++,
                                     'is_ai_generated' => true,
                                     'ai_provider' => $aiSetting->provider,
                                     'ai_model' => $validated['ai_model'],
                                 ];
                                 // Log::debug("Saving Qualifying Question:", $qSaveData); // Removed
                                 QualifyingQuestion::create($qSaveData); 
                             }
                         }
                     }
                 } 
            } catch (\Exception $e) {
                 Log::error("Qualifying question generation failed for JD ID {$jobDescription->id}: {$e->getMessage()}");
                 // Don't fail the whole request, just log the error
            }
            // --- End Qualifying Questions ---

            // Redirect to EDIT page after generation
            return redirect()->route('job-descriptions.show', $jobDescription) 
                ->with('success', 'Job description generated successfully.');
            
        } catch (\Exception $e) { // Main catch block
            return redirect()->back()
                ->with('error', 'Failed to generate job description: ' . $e->getMessage())
                ->withInput();
        }
    } // End of generate() method

    /**
     * Export the job description to various formats.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobDescription  $jobDescription
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request, JobDescription $jobDescription)
    {
        $this->authorize('view', $jobDescription->project);
        
        $format = $request->input('format', 'pdf');
        
        try {
            // Get the qualifying questions for this job description
            $qualifyingQuestions = $jobDescription->qualifyingQuestions()->ordered()->get();
            
            $exportService = new \App\Services\JobDescriptionExportService();
            $result = $exportService->exportJobDescription($jobDescription, $format, $qualifyingQuestions);
            
            return response()->download(
                storage_path('app/' . $result['file_path']),
                basename($result['file_path'])
            );
        } catch (\Exception $e) {
            return redirect()->route('job-descriptions.show', $jobDescription)
                ->with('error', 'Failed to export job description: ' . $e->getMessage());
        }
    }

    /**
     * Create a new version of the job description.
     *
     * @param  \App\Models\JobDescription  $jobDescription
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createVersion(JobDescription $jobDescription): RedirectResponse
    {
        $project = $jobDescription->project;
        $this->authorize('update', $project);
        
        $newVersion = $jobDescription->createNewVersion();
        
        return redirect()->route('job-descriptions.edit', $newVersion)
            ->with('success', 'New version created successfully.');
    }
    /**
     * Attempt to parse job description content using regex as a fallback.
     *
     * @param string $content
     * @return array
     */
    private function parseJobDescriptionContentFallback(string $content): array
    {
        $sections = [
            'overview' => '',
            'responsibilities' => '',
            'requirements_non_negotiable' => '',
            'requirements_preferred' => '',
            'compensation_range' => '',
            'industry' => '',
            'benefits' => '',
            'disclaimer' => '',
        ];

        // Use the flexible regex patterns
        $patterns = [
             'overview' => '/(?:\*\*)*(?:Overview|About the Role|Job Summary|Position Summary)(?:\*\*)*\s*:?\s*\n(.*?)(?=(?:\*\*)*(?:Responsibilities|Key Responsibilities|Duties|Job Responsibilities|What You\'ll Do)|$)/is',
             'responsibilities' => '/(?:\*\*)*(?:Responsibilities|Key Responsibilities|Duties|Job Responsibilities|What You\'ll Do)(?:\*\*)*\s*:?\s*\n(.*?)(?=(?:\*\*)*(?:Requirements|Qualifications|Required Skills|Non-Negotiable Requirements|What You\'ll Need)|$)/is',
             'requirements_non_negotiable' => '/(?:\*\*)*(?:Requirements|Qualifications|Required Skills|Non-Negotiable Requirements|What You\'ll Need)(?:\*\*)*\s*:?\s*\n(.*?)(?=(?:\*\*)*(?:Preferred|Nice to Have|Compensation|Salary|Benefits|Perks|What We Offer)|$)/is',
             'requirements_preferred' => '/(?:\*\*)*(?:Preferred|Nice to Have|Preferred Skills|Preferred Qualifications|Requirements \(Preferred\)|Preferred Requirements)(?:\*\*)*\s*:?\s*\n(.*?)(?=(?:\*\*)*(?:Compensation|Salary|Benefits|Perks|What We Offer|Disclaimer|Equal Opportunity|EEO)|$)/is',
             'compensation_range' => '/(?:\*\*)*(?:Compensation|Salary|Salary Range|Pay)(?:\*\*)*\s*:?\s*\n(.*?)(?=(?:\*\*)*(?:Benefits|Perks|What We Offer|Industry|Disclaimer|Equal Opportunity|EEO)|$)/is',
             'industry' => '/(?:\*\*)*(?:Industry)(?:\*\*)*\s*:?\s*\n(.*?)(?=(?:\*\*)*(?:Benefits|Perks|What We Offer|Disclaimer|Equal Opportunity|EEO)|$)/is',
             'benefits' => '/(?:\*\*)*(?:Benefits|Perks|What We Offer)(?:\*\*)*\s*:?\s*\n(.*?)(?=(?:\*\*)*(?:Disclaimer|Equal Opportunity|EEO)|$)/is',
             'disclaimer' => '/(?:\*\*)*(?:Disclaimer|Equal Opportunity|EEO)(?:\*\*)*\s*:?\s*\n(.*?)$/is',
        ];

        foreach ($patterns as $section => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                 // Trim whitespace and potential leftover markdown like trailing ** or list markers
                $sections[$section] = trim(preg_replace('/^[\s\*\-\#\>]+|[\s\*\-\#\>]+$/', '', $matches[1]));
            }
        }

        // Basic paragraph split fallback if regex fails significantly
        if (empty(array_filter(array_intersect_key($sections, array_flip(['overview', 'responsibilities', 'requirements_non_negotiable']))))) { // Check key sections
             $paragraphs = preg_split('/\n\s*\n+/', trim($content)); // Split by one or more blank lines
             if (count($paragraphs) >= 3) {
                 $sections['overview'] = trim($paragraphs[0]);
                 $sections['responsibilities'] = trim($paragraphs[1]);
                 $sections['requirements_non_negotiable'] = trim($paragraphs[2]);
                 // Assign others if available
                 if (isset($paragraphs[3])) $sections['requirements_preferred'] = trim($paragraphs[3]);
                 if (isset($paragraphs[4])) $sections['benefits'] = trim($paragraphs[4]); // Assuming order might include compensation/industry before benefits
                 if (isset($paragraphs[5])) $sections['disclaimer'] = trim($paragraphs[5]);
             } else {
                 $sections['overview'] = trim($content); // Last resort
             }
         }

        return $sections;
    }
}