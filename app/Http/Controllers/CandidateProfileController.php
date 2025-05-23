<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateProfile;
use App\Models\ProfileCustomHeading;
use App\Models\Project;
use App\Models\AISetting;
use App\Services\AIService;
use App\Services\CandidateProfileExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Services\ModelRegistryService;
use App\Models\AIPrompt;
use Exception;

class CandidateProfileController extends Controller
{
    /**
     * Display a list of projects for profile selection.
     */
    public function projectSelection(): View
    {
        // Get all projects the user has access to
        $projects = Project::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('candidate_profiles.project_selection', compact('projects'));
    }

    /**
     * Display a listing of the profiles for a project.
     */
    public function index(Project $project): View
    {
        $this->authorize('view', $project);
        
        // Get all candidates in the project
        $candidates = $project->candidates()
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Get all profiles for the project
        $profiles = CandidateProfile::where('project_id', $project->id)
            ->with('candidate')
            ->get()
            ->keyBy('candidate_id');
        
        return view('candidate_profiles.index', compact('project', 'candidates', 'profiles'));
    }

    /**
     * Show the form for creating a new profile.
     */
    public function create(Project $project, Candidate $candidate): View
    {
        $this->authorize('update', $project);
        
        // Check if the candidate belongs to the project
        if ($candidate->project_id !== $project->id) {
            abort(404, 'Candidate not found in this project');
        }
        
        // Get existing profile if any
        $profile = CandidateProfile::where('candidate_id', $candidate->id)
            ->where('project_id', $project->id)
            ->first();
        
        // If profile exists, redirect to edit
        if ($profile) {
            return redirect()->route('projects.candidates.profiles.edit', [
                'project' => $project,
                'candidate' => $candidate,
                'profile' => $profile
            ])->with('info', 'Profile already exists for this candidate. Editing existing profile.');
        }

        
        // Get available prompts for resume detail extraction / profile generation
        $prompts = AIPrompt::where('feature', 'resume_detail_extraction')
            ->orderBy('name')
            ->get();
        
        
        // Get all active AI settings without filtering by capabilities
        $aiSettings = AISetting::active()->get();
        
        // Get custom headings for this project
        try {
            $customHeadings = ProfileCustomHeading::getAllForProject($project->id);
        } catch (\Exception $e) {
            // If the table doesn't exist yet, provide an empty collection
            Log::warning('Failed to get custom headings: ' . $e->getMessage());
            $customHeadings = collect([]);
        }
        
        return view('candidate_profiles.create', compact(
            'project',
            'candidate',
            'aiSettings',
            'customHeadings'
        ));
    }

    /**
     * Store a newly created profile in storage.
     */
    public function store(Request $request, Project $project, Candidate $candidate): RedirectResponse
    {
        $this->authorize('update', $project);
        
        // Check if the candidate belongs to the project
        if ($candidate->project_id !== $project->id) {
            abort(404, 'Candidate not found in this project');
        }
        
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'ai_setting_id' => 'nullable|exists:ai_settings,id',
            'ai_model' => 'nullable|string',
        ]);
        
        // Get the AI setting if provided
        $aiSetting = null;
        if (!empty($validated['ai_setting_id'])) {
            $aiSetting = AISetting::find($validated['ai_setting_id']);
        }
        
        // Create the profile
        $profile = CandidateProfile::create([
            'candidate_id' => $candidate->id,
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'summary' => $validated['summary'] ?? null,
            'status' => 'draft',
            'ai_provider' => $aiSetting ? $aiSetting->provider : null,
            'ai_model' => $validated['ai_model'] ?? null,
        ]);
        
        // If AI generation is requested, redirect to generate
        if ($request->has('generate_content') && $request->generate_content) {
            return redirect()->route('projects.candidates.profiles.generate', [
                'project' => $project,
                'candidate' => $candidate,
                'profile' => $profile
            ]);
        }
        
        return redirect()->route('projects.candidates.profiles.edit', [
            'project' => $project,
            'candidate' => $candidate,
            'profile' => $profile
        ])->with('success', 'Profile created successfully.');
    }

    /**
     * Display the specified profile.
     */
    public function show(Project $project, Candidate $candidate, CandidateProfile $profile): View
    {
        $this->authorize('view', $project);
        
        // Check if the profile belongs to the candidate and project
        if ($profile->candidate_id !== $candidate->id || $profile->project_id !== $project->id) {
            abort(404, 'Profile not found for this candidate and project');
        }

        return view('candidate_profiles.show', compact('project', 'candidate', 'profile'));
    }

    /**
     * Show the form for editing the specified profile.
     */
    public function edit(Project $project, Candidate $candidate, CandidateProfile $profile): View
    {
        $this->authorize('update', $project);
        
        // Check if the profile belongs to the candidate and project
        if ($profile->candidate_id !== $candidate->id || $profile->project_id !== $project->id) {
            abort(404, 'Profile not found for this candidate and project');
        }
        
        // Get custom headings for this project
        $customHeadings = ProfileCustomHeading::getAllForProject($project->id);
        
        // Get AI settings for profile generation
        $aiSettings = AISetting::active()
            ->get();

        return view('candidate_profiles.edit', compact(
            'project',
            'candidate',
            'profile',
            'customHeadings',
            'aiSettings'
        ));
    }

    /**
     * Update the specified profile in storage.
     */
    public function update(Request $request, Project $project, Candidate $candidate, CandidateProfile $profile): RedirectResponse
    {
        $this->authorize('update', $project);
        
        // Check if the profile belongs to the candidate and project
        if ($profile->candidate_id !== $candidate->id || $profile->project_id !== $project->id) {
            abort(404, 'Profile not found for this candidate and project');
        }

        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'headings' => 'nullable|array',
            'headings.*.title' => 'required|string|max:255',
            'headings.*.content' => 'required|array',
            'headings.*.order' => 'nullable|integer',
            'extracted_data' => 'nullable|array',
            'finalize' => 'nullable|boolean',
        ]);
        
        // Update basic profile info
        $profile->update([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
        ]);


        if(!array_key_exists('finalize', $validated) && $profile->is_finalized) {
            $profile->update(['is_finalized' => false]);
            $profile->finalized_at = null;
        }

        // Normalize and save extracted data if provided
        if (isset($validated['extracted_data'])) {
            $extractedData = $validated['extracted_data'];

            if (isset($extractedData['experience']) && is_array($extractedData['experience'])) {
                foreach ($extractedData['experience'] as $idx => $exp) {
                    if (isset($exp['responsibilities'])) {
                        if (is_string($exp['responsibilities'])) {
                            $lines = preg_split('/\r?\n/', $exp['responsibilities']);
                            $extractedData['experience'][$idx]['responsibilities'] = array_values(array_filter(array_map('trim', $lines)));
                        } elseif (!is_array($exp['responsibilities'])) {
                            $extractedData['experience'][$idx]['responsibilities'] = [];
                        }
                    }
                }
            }

            $profile->update(['extracted_data' => $extractedData]);
        }
        
        // Update headings
        if (isset($validated['headings'])) {
            // If headings are provided, map them
            $headings = collect($validated['headings'])->map(function ($heading, $index) {
                return [
                    'title' => $heading['title'],
                    'content' => $heading['content'],
                    'order' => $heading['order'] ?? $index,
                ];
            })->toArray();
            
            $profile->update(['headings' => $headings]);
        } else if ($request->has('headings')) {
            // If headings field was submitted but is empty, set headings to empty array
            // This handles the case where all headings were removed in the form
            $profile->update(['headings' => []]);
            Log::info('All headings removed from profile', ['profile_id' => $profile->id]);
        }
        
        // Handle finalization if requested
        // if ($request->has('finalize') && $request->finalize) {
        if ($request->boolean('finalize')) {

            $profile->finalize();
            return redirect()->route('projects.candidates.profiles.show', [
                'project' => $project,
                'candidate' => $candidate,
                'profile' => $profile
            ])->with('success', 'Profile finalized successfully.');
        }
        
        return redirect()->route('projects.candidates.profiles.edit', [
            'project' => $project,
            'candidate' => $candidate,
            'profile' => $profile
        ])->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove the specified profile from storage.
     */
    public function destroy(Project $project, Candidate $candidate, CandidateProfile $profile): RedirectResponse
    {
        $this->authorize('update', $project);
        
        // Check if the profile belongs to the candidate and project
        if ($profile->candidate_id !== $candidate->id || $profile->project_id !== $project->id) {
            abort(404, 'Profile not found for this candidate and project');
        }
        
        $profile->delete();
        
        return redirect()->route('projects.candidates.profiles.index', [
            'project' => $project
        ])->with('success', 'Profile deleted successfully.');
    }

      /**
     * Normalize extracted data input from the edit form.
     */
    private function normalizeExtractedData(array $input, array $existing = []): array
    {
        $data = $existing;

        if (isset($input['contact_info'])) {
            $data['contact_info'] = $input['contact_info'];
        }

        $data['education'] = [];
        if (!empty($input['education']) && is_array($input['education'])) {
            foreach ($input['education'] as $item) {
                if (!array_filter($item)) {
                    continue;
                }
                $data['education'][] = [
                    'degree' => $item['degree'] ?? '',
                    'institution' => $item['institution'] ?? '',
                    'date_range' => $item['date_range'] ?? '',
                    'highlights' => isset($item['highlights'])
                        ? array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $item['highlights']))))
                        : [],
                ];
            }
        }

        $data['experience'] = [];
        if (!empty($input['experience']) && is_array($input['experience'])) {
            foreach ($input['experience'] as $item) {
                if (!array_filter($item)) {
                    continue;
                }
                $data['experience'][] = [
                    'title' => $item['title'] ?? '',
                    'company' => $item['company'] ?? '',
                    'date_range' => $item['date_range'] ?? '',
                    'responsibilities' => isset($item['responsibilities'])
                        ? array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $item['responsibilities']))))
                        : [],
                    'achievements' => isset($item['achievements'])
                        ? array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $item['achievements']))))
                        : [],
                ];
            }
        }

        $data['skills'] = [];
        if (isset($input['skills']) && is_array($input['skills'])) {
            foreach (['technical', 'soft', 'languages', 'certifications'] as $type) {
                if (isset($input['skills'][$type])) {
                    $data['skills'][$type] = array_values(array_filter(array_map('trim', explode(',', $input['skills'][$type]))));
                }
            }
        }

        $data['additional_info'] = [];
        if (isset($input['additional_info']) && is_array($input['additional_info'])) {
            foreach (['interests', 'volunteer_work', 'publications'] as $type) {
                if (isset($input['additional_info'][$type])) {
                    $data['additional_info'][$type] = array_values(array_filter(array_map('trim', explode(',', $input['additional_info'][$type]))));
                }
            }
        }

        return $data;
    }


    /**
     * Show the form for generating profile content.
     */
    public function showGenerate(Project $project, Candidate $candidate, CandidateProfile $profile, ModelRegistryService $modelRegistryService): View
    {
        $this->authorize('update', $project);
        
        // Check if the profile belongs to the candidate and project
        if ($profile->candidate_id !== $candidate->id || $profile->project_id !== $project->id) {
            abort(404, 'Profile not found for this candidate and project');
        }
        
        // Get all active AI settings without filtering by capabilities
        $aiSettings = AISetting::active()->get();
        
        // Get available prompts for different features
        $prompts = AIPrompt::where('feature', 'resume_detail_extraction')
            ->orderBy('name')
            ->get();
            
        $summaryPrompts = AIPrompt::where('feature', 'profile_summary')
            ->orderBy('name')
            ->get();
            
        $headingsPrompts = AIPrompt::where('feature', 'profile_headings')
            ->orderBy('name')
            ->get();
            
        $contentPrompts = AIPrompt::where('feature', 'profile_content')
            ->orderBy('name')
            ->get();
            
        // Get provider models map from service
        $providerModels = $modelRegistryService->getModels();
        
        // Get custom headings for this project
        try {
            $customHeadings = ProfileCustomHeading::getAllForProject($project->id);
        } catch (\Exception $e) {
            // If the table doesn't exist yet, provide an empty collection
            Log::warning('Failed to get custom headings: ' . $e->getMessage());
            $customHeadings = collect([]);
        }
        
        // Get project requirements
        $requirements = $project->activeRequirements()->get();
        
        return view('candidate_profiles.generate', compact(
            'project',
            'candidate',
            'profile',
            'aiSettings',
            'customHeadings',
            'requirements',
            'prompts',
            'summaryPrompts',
            'headingsPrompts',
            'contentPrompts',
            'providerModels'
        ));
    }

    /**
     * Generate profile content using AI.
     */
    public function generate(Request $request, Project $project, Candidate $candidate, CandidateProfile $profile): RedirectResponse
    {
        $this->authorize('update', $project);
        
        // Check if the profile belongs to the candidate and project
        if ($profile->candidate_id !== $candidate->id || $profile->project_id !== $project->id) {
            abort(404, 'Profile not found for this candidate and project');
        }
        
        // Validate the request
        $validated = $request->validate([
            'ai_setting_id' => 'required|exists:ai_settings,id',
            'ai_model' => 'required|string',
            'ai_prompt_id' => 'nullable|exists:ai_prompts,id',
            'summary_prompt_id' => 'nullable|exists:ai_prompts,id',
            'headings_prompt_id' => 'nullable|exists:ai_prompts,id',
            'content_prompt_id' => 'nullable|exists:ai_prompts,id',
            'generation_type' => 'required|in:full,headings,content',
            'custom_headings' => 'nullable|array',
            'custom_headings.*' => 'nullable|string',
        ]);
        
        try {
            // Get the AI setting by ID
            $aiSetting = AISetting::find($validated['ai_setting_id']);
            
            if (!$aiSetting || !$aiSetting->is_active) {
                return redirect()->back()->with('error', 'Selected AI setting is not active or does not exist.');
            }
            
            // Create AI service
            $aiService = new AIService();
            
            // Update profile with AI provider and model
            $profile->update([
                'ai_provider' => $aiSetting->provider,
                'ai_model' => $validated['ai_model'],
                'status' => 'in_progress',
            ]);
            
            // Get the selected prompts if provided
            $extractionPrompt = !empty($validated['ai_prompt_id']) ? AIPrompt::find($validated['ai_prompt_id']) : null;
            $summaryPrompt = !empty($validated['summary_prompt_id']) ? AIPrompt::find($validated['summary_prompt_id']) : null;
            $headingsPrompt = !empty($validated['headings_prompt_id']) ? AIPrompt::find($validated['headings_prompt_id']) : null;
            $contentPrompt = !empty($validated['content_prompt_id']) ? AIPrompt::find($validated['content_prompt_id']) : null;
            
            // Verify the prompts are for the correct features
            if ($extractionPrompt && $extractionPrompt->feature !== 'resume_detail_extraction') {
                $extractionPrompt = null;
            }
            
            if ($summaryPrompt && $summaryPrompt->feature !== 'profile_summary') {
                $summaryPrompt = null;
            }
            
            if ($headingsPrompt && $headingsPrompt->feature !== 'profile_headings') {
                $headingsPrompt = null;
            }
            
            if ($contentPrompt && $contentPrompt->feature !== 'profile_content') {
                $contentPrompt = null;
            }
            
            // Create a prompts array to pass to generation methods
            $prompts = [
                'extraction' => $extractionPrompt,
                'summary' => $summaryPrompt,
                'headings' => $headingsPrompt,
                'content' => $contentPrompt
            ];
            
            // Generate content based on generation type
            switch ($validated['generation_type']) {
                case 'full':
                    $this->generateFullProfile($aiService, $aiSetting, $validated['ai_model'], $profile, $candidate, $prompts);
                    break;
                    
                case 'headings':
                    $this->generateHeadings($aiService, $aiSetting, $validated['ai_model'], $profile, $candidate, $validated['custom_headings'] ?? [], $prompts);
                    break;
                    
                case 'content':
                    $this->generateContent($aiService, $aiSetting, $validated['ai_model'], $profile, $candidate, $prompts);
                    break;
            }
            
            return redirect()->route('projects.candidates.profiles.edit', [
                'project' => $project,
                'candidate' => $candidate,
                'profile' => $profile
            ])->with('success', 'Profile content generated successfully.');
            
        } catch (Exception $e) {
            Log::error('Profile generation failed', [
                'error' => $e->getMessage(),
                'profile_id' => $profile->id,
                'candidate_id' => $candidate->id,
            ]);
            
            return redirect()->back()->with('error', 'Failed to generate profile content: ' . $e->getMessage());
        }
    }

    /**
     * Generate a full profile (summary, headings, and content).
     */
    private function generateFullProfile(AIService $aiService, AISetting $aiSetting, string $model, CandidateProfile $profile, Candidate $candidate, array $prompts = [])
    {
        // First, extract data from resume and other sources
        $extractedData = $this->extractCandidateData($aiService, $aiSetting, $model, $candidate, $prompts['extraction'] ?? null);
        
        // Ensure extracted data is an array
        if (!is_array($extractedData)) {
            $extractedData = $this->getDefaultExtractedData($candidate);
        }
        
        $profile->update(['extracted_data' => $extractedData]);
        
        // Generate summary
        $summary = $this->generateSummary($aiService, $aiSetting, $model, $candidate, $extractedData, $prompts['summary'] ?? null);
        $profile->update(['summary' => $summary]);
        
        // Generate heading suggestions
        $headingSuggestions = $this->generateHeadingSuggestions($aiService, $aiSetting, $model, $candidate, $extractedData, $prompts['headings'] ?? null);
        
        // Create headings from suggestions
        $headings = [];
        foreach ($headingSuggestions as $index => $suggestion) {
            // Generate content for each heading
            $content = $this->generateHeadingContent($aiService, $aiSetting, $model, $candidate, $suggestion['heading'], $extractedData, $prompts['content'] ?? null);
            
            $headings[] = [
                'title' => $suggestion['heading'],
                'content' => $content,
                'order' => $index,
            ];
        }
        
        $profile->update(['headings' => $headings]);
    }

    /**
     * Generate just the headings for a profile.
     */
    private function generateHeadings(AIService $aiService, AISetting $aiSetting, string $model, CandidateProfile $profile, Candidate $candidate, array $customHeadings = [], array $prompts = [])
    {
        // Extract data if not already done
        if (empty($profile->extracted_data)) {
            $extractedData = $this->extractCandidateData($aiService, $aiSetting, $model, $candidate, $prompts['extraction'] ?? null);
            
            // Ensure extracted data is an array
            if (!is_array($extractedData)) {
                $extractedData = $this->getDefaultExtractedData($candidate);
            }
            
            $profile->update(['extracted_data' => $extractedData]);
        } else {
            $extractedData = $profile->extracted_data;
        }
        
        // If custom headings are provided, use those
        if (!empty($customHeadings)) {
            $headings = [];
            foreach ($customHeadings as $index => $heading) {
                $headings[] = [
                    'title' => $heading,
                    'content' => [],
                    'order' => $index,
                ];
            }
        } else {
            // Generate heading suggestions
            $headingSuggestions = $this->generateHeadingSuggestions($aiService, $aiSetting, $model, $candidate, $extractedData, $prompts['headings'] ?? null);
            
            $headings = [];
            foreach ($headingSuggestions as $index => $suggestion) {
                $headings[] = [
                    'title' => $suggestion['heading'],
                    'content' => [],
                    'order' => $index,
                ];
            }
        }
        
        $profile->update(['headings' => $headings]);
    }

    /**
     * Generate content for existing headings.
     */
    private function generateContent(AIService $aiService, AISetting $aiSetting, string $model, CandidateProfile $profile, Candidate $candidate, array $prompts = [])
    {
        // Extract data if not already done
        if (empty($profile->extracted_data)) {
            $extractedData = $this->extractCandidateData($aiService, $aiSetting, $model, $candidate, $prompts['extraction'] ?? null);
            
            // Ensure extracted data is an array
            if (!is_array($extractedData)) {
                $extractedData = $this->getDefaultExtractedData($candidate);
            }
            
            $profile->update(['extracted_data' => $extractedData]);
        } else {
            $extractedData = $profile->extracted_data;
        }
        
        // Generate content for each existing heading
        $headings = $profile->headings ?: [];
        foreach ($headings as $index => $heading) {
            $content = $this->generateHeadingContent($aiService, $aiSetting, $model, $candidate, $heading['title'], $extractedData, $prompts['content'] ?? null);
            $headings[$index]['content'] = $content;
        }
        
        $profile->update(['headings' => $headings]);
    }

    /**
     * Extract candidate data from resume and other sources.
     */
    private function extractCandidateData(AIService $aiService, AISetting $aiSetting, string $model, Candidate $candidate, ?AIPrompt $selectedPrompt = null)
    {
        // Retrieve default prompt for resume detail extraction
        $prompt = AIPrompt::forFeature('resume_detail_extraction')
            ->forProvider($aiSetting->provider)
            ->forModel($model)
            ->default()
            ->first();

        if (!$prompt) {
            $template = "You are an expert recruiter assistant tasked with extracting key information from a candidate's resume.\n\n" .
                "RESUME TEXT:\n{{resume_text}}\n\n" .
                "Extract the following information in JSON format:\n" .
                "{\n" .
                "  \"contact_info\": {\n" .
                "    \"name\": \"\",\n" .
                "    \"email\": \"\",\n" .
                "    \"phone\": \"\",\n" .
                "    \"location\": \"\",\n" .
                "    \"linkedin\": \"\"\n" .
                "  },\n" .
                "  \"education\": [\n" .
                "    {\n" .
                "      \"degree\": \"\",\n" .
                "      \"institution\": \"\",\n" .
                "      \"date_range\": \"\",\n" .
                "      \"highlights\": []\n" .
                "    }\n" .
                "  ],\n" .
                "  \"experience\": [\n" .
                "    {\n" .
                "      \"title\": \"\",\n" .
                "      \"company\": \"\",\n" .
                "      \"date_range\": \"\",\n" .
                "      \"responsibilities\": [],\n" .
                "      \"achievements\": []\n" .
                "    }\n" .
                "  ],\n" .
                "  \"skills\": {\n" .
                "    \"technical\": [],\n" .
                "    \"soft\": [],\n" .
                "    \"languages\": [],\n" .
                "    \"certifications\": []\n" .
                "  },\n" .
                "  \"additional_info\": {\n" .
                "    \"interests\": [],\n" .
                "    \"volunteer_work\": [],\n" .
                "    \"publications\": []\n" .
                "  }\n" .
                "}\n\n" .
                "IMPORTANT INSTRUCTIONS:\n" .
                "1. Return ONLY valid JSON without any additional text or explanation\n" .
                "2. If information is not available, use null or empty arrays/objects\n" .
                "3. For dates, use the format provided in the resume\n" .
                "4. Extract ALL relevant information, even if not explicitly mentioned in the template\n" .
                "5. For skills, categorize them appropriately based on context";

            $prompt = new AIPrompt([
                'feature' => 'resume_detail_extraction',
                'name' => 'Resume Detail Extraction Prompt',
                'prompt_template' => $template,
                'provider' => $aiSetting->provider,
                'model' => $model,
                'is_default' => true,
                'created_by' => Auth::id(),
            ]);
            $prompt->save();
        }

        $formattedPrompt = $prompt->formatPrompt([
            'resume_text' => $candidate->resume_text,
        ]);
        
        try {
            // Call AI service
            $response = $aiService->generateContent(
                $aiSetting,
                $model,
                $formattedPrompt,
                ['response_format' => 'json_object'],
                Auth::id(),
                // 'profile_creation',
                'resume_detail_extraction'
            );


            // Parse and return the extracted data
            try {
                $content = preg_replace('/^```json\s*|\s*```$/', '', $response['content']);
                $data = json_decode($content, true);

                // Check if json_decode returned null (invalid JSON)
                if ($data === null) {
                    // Return a default structure instead of null
                    return $this->getDefaultExtractedData($candidate);
                }
                
                return $data;
            } catch (Exception $e) {
                // Return a default structure instead of null
                return $this->getDefaultExtractedData($candidate);
            }
        } catch (Exception $e) {
            // Return a default structure instead of null
            return $this->getDefaultExtractedData($candidate);
        }
    }
    
    /**
     * Get default extracted data structure when AI extraction fails.
     */
    private function getDefaultExtractedData(Candidate $candidate): array
    {
        // Create a minimal default structure with basic candidate info
        return [
            'contact_info' => [
                'name' => $candidate->full_name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'location' => $candidate->location,
                'linkedin' => '',
            ],
            'education' => [],
            'experience' => [
                [
                    'title' => $candidate->current_position ?? '',
                    'company' => $candidate->current_company ?? '',
                    'date_range' => '',
                    'responsibilities' => [],
                    'achievements' => [],
                ]
            ],
            'skills' => [
                'technical' => [],
                'soft' => [],
                'languages' => [],
                'certifications' => [],
            ],
            'additional_info' => [
                'interests' => [],
                'volunteer_work' => [],
                'publications' => [],
            ],
        ];
    }

    /**
     * Generate a summary for the profile.
     */
    private function generateSummary(AIService $aiService, AISetting $aiSetting, string $model, Candidate $candidate, array $extractedData)
    {
        // Try to find a prompt for profile summary
        $prompt = AIPrompt::where('feature', 'profile_summary')
            ->where(function($query) use ($aiSetting) {
                $query->where('provider', $aiSetting->provider)
                    ->orWhereNull('provider');
            })
            ->where(function($query) use ($model) {
                $query->where('model', $model)
                    ->orWhereNull('model');
            })
            ->where('is_default', true)
            ->first();
            
        if (!$prompt) {
            // Create a default prompt if none exists
            $promptTemplate = "You are an expert recruiter assistant tasked with creating a professional summary for a candidate profile.\n\n" .
                "CANDIDATE DATA:\n{{extracted_data_json}}\n\n" .
                "JOB TITLE: {{job_title}}\n\n" .
                "Create a concise, professional summary (2-3 paragraphs) that highlights the candidate's key qualifications, experience, and fit for the role. Focus on their most relevant skills and achievements.\n\n" .
                "The summary should be written in third person and maintain a professional tone. Do not include any personal opinions or subjective assessments.\n\n" .
                "IMPORTANT: Return ONLY the summary text without any additional explanations or formatting.";
                
            $prompt = new AIPrompt([
                'feature' => 'profile_summary',
                'name' => 'Profile Summary Generation Prompt',
                'prompt_template' => $promptTemplate,
                'provider' => $aiSetting->provider,
                'model' => $model,
                'is_default' => true,
                'created_by' => Auth::id(),
            ]);
            $prompt->save();
        
        }
        
        // Format the prompt with data
        $formattedPrompt = $prompt->formatPrompt([
            'extracted_data_json' => json_encode($extractedData, JSON_PRETTY_PRINT),
            'job_title' => $candidate->project->title
        ]);
        
        // Call AI service
        $response = $aiService->generateContent(
            $aiSetting,
            $model,
            $formattedPrompt,
            [],
            Auth::id(),
            'profile_creation'
        );
        
        return $response['content'];
    }

    /**
     * Generate heading suggestions for the profile.
     */
    private function generateHeadingSuggestions(AIService $aiService, AISetting $aiSetting, string $model, Candidate $candidate, array $extractedData)
    {
        // Get project requirements
        $requirements = $candidate->project->activeRequirements()->get();
        $requirementsJson = $requirements->map(function ($req) {
            return [
                'name' => $req->name,
                'type' => $req->type,
                'weight' => $req->weight,
                'is_required' => $req->is_required,
            ];
        })->toJson();
        
        // Try to find a prompt for profile headings
        $prompt = AIPrompt::where('feature', 'profile_headings')
            ->where(function($query) use ($aiSetting) {
                $query->where('provider', $aiSetting->provider)
                    ->orWhereNull('provider');
            })
            ->where(function($query) use ($model) {
                $query->where('model', $model)
                    ->orWhereNull('model');
            })
            ->where('is_default', true)
            ->first();
            
        if (!$prompt) {
            // Create a default prompt if none exists
            $promptTemplate = "You are an expert recruiter assistant tasked with suggesting custom headings for a candidate profile.\n\n" .
                "CANDIDATE DATA:\n{{extracted_data_json}}\n\n" .
                "JOB REQUIREMENTS:\n{{requirements_json}}\n\n" .
                "JOB TITLE: {{job_title}}\n\n" .
                "Suggest 5 custom headings for this candidate's profile that highlight the candidate's strengths for this role. Return in JSON format:\n" .
                "{\n" .
                "  \"suggested_headings\": [\n" .
                "    {\n" .
                "      \"heading\": \"\",\n" .
                "      \"rationale\": \"\"\n" .
                "    }\n" .
                "  ]\n" .
                "}\n\n" .
                "IMPORTANT INSTRUCTIONS:\n" .
                "1. Return ONLY valid JSON without any additional text or explanation\n" .
                "2. Create headings that are specific, not generic (e.g., \"Frontend Development Expertise\" not \"Technical Skills\")\n" .
                "3. Ensure headings highlight areas where the candidate is strong AND that align with job requirements\n" .
                "4. Provide a clear rationale for each heading suggestion\n" .
                "5. Include exactly 5 heading suggestions";
                
            $prompt = new AIPrompt([
                'feature' => 'profile_headings',
                'name' => 'Profile Heading Content Generation Prompt',
                'prompt_template' => $promptTemplate,
                'provider' => $aiSetting->provider,
                'model' => $model,
                'is_default' => true,
                'created_by' => Auth::id(),
            ]);
            $prompt->save();
        }
        
        // Format the prompt with data
        $formattedPrompt = $prompt->formatPrompt([
            'extracted_data_json' => json_encode($extractedData, JSON_PRETTY_PRINT),
            'requirements_json' => $requirementsJson,
            'job_title' => $candidate->project->title
        ]);
        
        // Call AI service
        $response = $aiService->generateContent(
            $aiSetting,
            $model,
            $formattedPrompt,
            ['response_format' => 'json_object'],
            Auth::id(),
            'profile_creation'
        );
        
        // Parse and return the heading suggestions
        try {
            $data = json_decode($response['content'], true);
            return $data['suggested_headings'] ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Generate content for a specific heading.
     */
    private function generateHeadingContent(AIService $aiService, AISetting $aiSetting, string $model, Candidate $candidate, string $heading, array $extractedData)
    {
        // Try to find a prompt for profile content
        $prompt = AIPrompt::where('feature', 'profile_content')
            ->where(function($query) use ($aiSetting) {
                $query->where('provider', $aiSetting->provider)
                    ->orWhereNull('provider');
            })
            ->where(function($query) use ($model) {
                $query->where('model', $model)
                    ->orWhereNull('model');
            })
            ->where('is_default', true)
            ->first();
            
        if (!$prompt) {
            // Create a default prompt if none exists
            $promptTemplate = "You are an expert recruiter assistant tasked with creating impactful bullet points for a candidate profile.\n\n" .
                "CANDIDATE DATA:\n{{extracted_data_json}}\n\n" .
                "HEADING: {{heading}}\n\n" .
                "JOB TITLE: {{job_title}}\n\n" .
                "Generate 3-5 concise, impactful bullet points that highlight the candidate's relevant experience, skills, and achievements for this heading. Return in JSON format:\n" .
                "{\n" .
                "  \"bullet_points\": [\n" .
                "    {\n" .
                "      \"content\": \"\",\n" .
                "      \"evidence_source\": \"resume|interview|web_presence\"\n" .
                "    }\n" .
                "  ]\n" .
                "}\n\n" .
                "IMPORTANT INSTRUCTIONS:\n" .
                "1. Return ONLY valid JSON without any additional text or explanation\n" .
                "2. Each bullet point should be 1-2 sentences maximum\n" .
                "3. Focus on quantifiable achievements and concrete examples where possible\n" .
                "4. Use active voice and strong action verbs\n" .
                "5. Cite the source of evidence for each bullet point (usually 'resume' based on the data provided)\n" .
                "6. Avoid generic statements - be specific and evidence-based";
                
            $prompt = new AIPrompt([
                'feature' => 'profile_content',
                'name' => 'Profile Heading Content Generation Prompt',
                'prompt_template' => $promptTemplate,
                'provider' => $aiSetting->provider,
                'model' => $model,
                'is_default' => true,
                'created_by' => Auth::id(),
            ]);
            $prompt->save();
        }
        
        // Format the prompt with data
        $formattedPrompt = $prompt->formatPrompt([
            'extracted_data_json' => json_encode($extractedData, JSON_PRETTY_PRINT),
            'heading' => $heading,
            'job_title' => $candidate->project->title
        ]);
        
        // Call AI service
        $response = $aiService->generateContent(
            $aiSetting,
            $model,
            $formattedPrompt,
            ['response_format' => 'json_object'],
            Auth::id(),
            'profile_creation'
        );
        
        // Parse and return the content
        try {
            $data = json_decode($response['content'], true);
            return $data['bullet_points'] ?? [];
        } catch (Exception $e) {
            Log::error('Failed to parse heading content', [
                'error' => $e->getMessage(),
                'response' => $response['content'],
            ]);
            return [];
        }
    }

    /**
     * Export the profile to various formats.
     */
    public function export(Request $request, Project $project, Candidate $candidate, CandidateProfile $profile)
    {
        $this->authorize('view', $project);
        
        // Check if the profile belongs to the candidate and project
        if ($profile->candidate_id !== $candidate->id || $profile->project_id !== $project->id) {
            abort(404, 'Profile not found for this candidate and project');
        }
        
        // Validate the request
        $validated = $request->validate([
            'format' => 'required|in:pdf,docx,html',
        ]);
        
        $format = $validated['format'];

        try {
            $exportService = new \App\Services\CandidateProfileExportService();
            $result = $exportService->exportProfile($profile, $validated['format']);

            return response()->download(
                storage_path('app/' . $result['file_path']),
                basename($result['file_path'])
            );
        } catch (\Exception $e) {
            return redirect()->route('projects.candidates.profiles.show', [$project, $candidate, $profile])
                ->with('error', 'Failed to export profile: ' . $e->getMessage());
        }
    }
}
