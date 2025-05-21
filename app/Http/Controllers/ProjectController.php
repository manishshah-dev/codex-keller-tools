<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\AISetting;
use App\Models\AIPrompt;
use App\Services\AIService;
use App\Services\ModelRegistryService;
use App\Jobs\ProjectResearchWithAI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

class ProjectController extends Controller
{
    // Middleware is now applied in the routes file
    
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $projects = Project::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(ModelRegistryService $modelRegistryService): View
    {
        // Get active AI providers
        $aiProviders = AISetting::where('is_active', true)->get();
        
        // Get provider models map from service
        $providerModels = $modelRegistryService->getModels();
        
        // Get available prompts for project generation
        $prompts = AIPrompt::whereIn('feature', [
            'job_details',
            'company_research',
            'salary_comparison',
            'search_strings',
            'keywords',
            'ai_questions'
        ])->orderBy('name')->get();
        
        return view('projects.multi_step_form', compact('aiProviders', 'providerModels', 'prompts'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validationRules = [
            // Basic project information
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'use_ai' => 'nullable|boolean',
            'enable_search' => 'nullable|boolean',
            'requirements_document' => 'nullable|file|mimes:pdf,docx,doc|max:10240',
            
            // Intake form fields
            'job_title' => 'nullable|string|max:255',
            'required_skills' => 'nullable|string',
            'preferred_skills' => 'nullable|string',
            'experience_level' => 'nullable|string|max:255',
            'education_requirements' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'additional_notes' => 'nullable|string',
            'claap_recording_url' => 'nullable|url|max:255',
            'claap_transcript' => 'nullable|string',
            'claap_transcript_file' => 'nullable|file|mimes:txt,pdf,docx,doc|max:10240',
            
            // Company research fields
            'company_name' => 'nullable|string|max:255',
            'founding_date' => 'nullable|date',
            'company_size' => 'nullable|string|max:255',
            'turnover' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'competitors' => 'nullable|string',
            'industry_details' => 'nullable|string',
            'typical_clients' => 'nullable|string',
            
            // Job description fields
            'overview' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'requirements_non_negotiable' => 'nullable|string',
            'requirements_preferred' => 'nullable|string',
            'compensation_range' => 'nullable|string|max:255',
            'benefits' => 'nullable|string',
            'jd_status' => 'nullable|string|max:255',
            
            // Salary comparison fields
            'average_salary' => 'nullable|numeric',
            'min_salary' => 'nullable|numeric',
            'max_salary' => 'nullable|numeric',
            'similar_job_postings' => 'nullable|string',
            'salary_data_source' => 'nullable|string|max:255',
            
            // Search strings fields
            'linkedin_boolean_string' => 'nullable|string',
            'google_xray_linkedin_string' => 'nullable|string',
            'google_xray_cv_string' => 'nullable|string',
            'search_string_notes' => 'nullable|string',
            
            // Keywords fields
            'keywords' => 'nullable|string',
            'synonyms' => 'nullable|string',
            'translations' => 'nullable|string',
            'translation_language' => 'nullable|string|max:10',
            
            // AI Settings
            'ai_setting_id' => 'nullable|exists:ai_settings,id',
            'ai_model' => 'nullable|string|max:100',
            'ai_prompt_id' => 'nullable|array',
            'ai_prompt_id.*' => 'exists:ai_prompts,id',
            
            // AI Questions fields
            'candidate_questions' => 'nullable|string',
            'recruiter_questions' => 'nullable|string',
        ];

        $validated = $request->validate($validationRules);
        
        // Remove use_ai, enable_search, requirements_document, and claap_transcript_file from validated data
        $useAi = $request->has('use_ai');
        $enableSearch = $request->has('enable_search');
        $requirementsDocument = $request->file('requirements_document');
        $claapTranscriptFile = $request->file('claap_transcript_file');
        $claapTranscript = $validated['claap_transcript'] ?? null;
        unset($validated['use_ai']);
        unset($validated['enable_search']);
        unset($validated['requirements_document']);
        unset($validated['claap_transcript_file']);
        unset($validated['claap_transcript']);
        
        // Process Claap transcript file if uploaded
        if ($claapTranscriptFile) {
            try {
                // Extract text from the Claap transcript file
                $claapTranscriptText = $this->extractTextFromDocument($claapTranscriptFile);
                
                // Set the extracted text as the Claap transcript
                $validated['claap_transcript'] = $claapTranscriptText;
                
            } catch (Exception $e) {
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to process Claap transcript file: ' . $e->getMessage());
            }
        }else if ($claapTranscript) {
            // If Claap transcript is provided, set it in the validated data
            $validated['claap_transcript'] = $claapTranscript;
        }


        $project = new Project($validated);
        $project->user_id = Auth::id();
        $project->status = 'active';
        $project->save();

        // Process document with AI if requested
        if ($useAi && $requirementsDocument) {
            try {
                // Get the selected AI setting
                $aiSettingId = $validated['ai_setting_id'] ?? null;
                $aiModel = $validated['ai_model'] ?? null;
                $aiPromptIds = $validated['ai_prompt_id'] ?? null;
                
                if ($aiSettingId) {
                    $aiSetting = AISetting::findOrFail($aiSettingId);
                } else {
                    // Fallback to default AI setting
                    $aiSetting = AISetting::where('is_active', true)->where('is_default', true)->first();
                    
                    if (!$aiSetting) {
                        // If no default setting, get the first active one
                        $aiSetting = AISetting::where('is_active', true)->first();
                    }
                }
                
                if ($aiSetting) {
                    // Extract text from document
                    $documentText = $this->extractTextFromDocument($requirementsDocument);
                    
                    // Process with AI
                    $this->processDocumentWithAI($project, $aiSetting, $documentText, $aiModel, $enableSearch, $aiPromptIds);
                    
                    return redirect()->route('projects.show', $project)
                        ->with('success', 'Project created successfully. AI is processing your document and populating project details.');
                }
            } catch (Exception $e) {                
                return redirect()->route('projects.show', $project)
                    ->with('warning', 'Project created successfully, but AI processing failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function show(Project $project): View
    {
        $this->authorize('view', $project);
        
        // Count \n in candidate_questions and recruiter_questions
        $candidate_questions_count = substr_count($project->candidate_questions, '\n') + 1;
        $recruiter_questions_count = substr_count($project->recruiter_questions, '\n') + 1;

        if($candidate_questions_count > 1) {
            $project->candidate_questions = explode('\n', $project->candidate_questions);
        }
        
        if($recruiter_questions_count > 1) {
            $project->recruiter_questions = explode('\n', $project->recruiter_questions);
        }

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\View\View
     */
    public function edit(Project $project, ModelRegistryService $modelRegistryService): View
    {
        $this->authorize('update', $project);

        // Get active AI providers
        $aiProviders = AISetting::where('is_active', true)->get();
        
        // Get provider models map from service
        $providerModels = $modelRegistryService->getModels();
        
        // Get available prompts for project generation
        $prompts = AIPrompt::whereIn('feature', [
            'job_details',
            'company_research',
            'salary_comparison',
            'search_strings',
            'keywords',
            'ai_questions'
        ])->orderBy('name')->get();
        
        return view('projects.multi_step_form', compact('project', 'aiProviders', 'providerModels', 'prompts'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validationRules = [
            // Basic project information
            'title' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,completed,on-hold,cancelled',
            'use_ai' => 'nullable|boolean',
            'requirements_document' => 'nullable|file|mimes:pdf,docx,doc|max:10240',
            'ai_setting_id' => 'nullable|exists:ai_settings,id',
            'ai_model' => 'nullable|string|max:100',
            'ai_prompt_id' => 'nullable|array',
            'ai_prompt_id.*' => 'nullable|exists:ai_prompts,id',
            
            // Intake form fields
            'job_title' => 'nullable|string|max:255',
            'required_skills' => 'nullable|string',
            'preferred_skills' => 'nullable|string',
            'experience_level' => 'nullable|string|max:255',
            'education_requirements' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'additional_notes' => 'nullable|string',
            'claap_recording_url' => 'nullable|url|max:255',
            'claap_transcript' => 'nullable|string',
            'claap_transcript_file' => 'nullable|file|mimes:txt,pdf,docx,doc|max:10240',
            
            // Company research fields
            'company_name' => 'nullable|string|max:255',
            'founding_date' => 'nullable|date',
            'company_size' => 'nullable|string|max:255',
            'turnover' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            'competitors' => 'nullable|string',
            'industry_details' => 'nullable|string',
            'typical_clients' => 'nullable|string',
            
            // Job description fields
            'overview' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'requirements_non_negotiable' => 'nullable|string',
            'requirements_preferred' => 'nullable|string',
            'compensation_range' => 'nullable|string|max:255',
            'benefits' => 'nullable|string',
            'jd_status' => 'nullable|string|max:255',
            
            // Salary comparison fields
            'average_salary' => 'nullable|numeric',
            'min_salary' => 'nullable|numeric',
            'max_salary' => 'nullable|numeric',
            'similar_job_postings' => 'nullable|string',
            'salary_data_source' => 'nullable|string|max:255',
            
            // Search strings fields
            'linkedin_boolean_string' => 'nullable|string',
            'google_xray_linkedin_string' => 'nullable|string',
            'google_xray_cv_string' => 'nullable|string',
            'search_string_notes' => 'nullable|string',
            
            // Keywords fields
            'keywords' => 'nullable|string',
            'synonyms' => 'nullable|string',
            'translations' => 'nullable|string',
            'translation_language' => 'nullable|string|max:10',
            
            // AI Questions fields
            'candidate_questions' => 'nullable|string',
            'recruiter_questions' => 'nullable|string',
        ];

        $validated = $request->validate($validationRules);
        
        // Remove use_ai, requirements_document, and claap_transcript_file from validated data
        $useAi = $request->has('use_ai');
        $requirementsDocument = $request->file('requirements_document');
        $enableSearch = $request->has('enable_search');
        $claapTranscriptFile = $request->file('claap_transcript_file');
        unset($validated['use_ai']);
        unset($validated['requirements_document']);
        unset($validated['claap_transcript_file']);
        
        // Process Claap transcript file if uploaded
        if ($claapTranscriptFile) {
            try {
                // Extract text from the Claap transcript file
                $claapTranscriptText = $this->extractTextFromDocument($claapTranscriptFile);
                
                // Set the extracted text as the Claap transcript
                $validated['claap_transcript'] = $claapTranscriptText;
               
            } catch (Exception $e) {
                
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Failed to process Claap transcript file: ' . $e->getMessage());
            }
        }

        $project->update($validated);

        // Process document with AI if requested
        if ($useAi && $requirementsDocument) {
            try {
                // Get the selected AI setting
                $aiSettingId = $validated['ai_setting_id'] ?? null;
                $aiModel = $validated['ai_model'] ?? null;
                $aiPromptIds = $validated['ai_prompt_id'] ?? null;
                
                if ($aiSettingId) {
                    $aiSetting = AISetting::findOrFail($aiSettingId);
                } else {
                    // Fallback to default AI setting
                    $aiSetting = AISetting::where('is_active', true)->where('is_default', true)->first();
                    
                    if (!$aiSetting) {
                        // If no default setting, get the first active one
                        $aiSetting = AISetting::where('is_active', true)->first();
                    }
                }
                
                if ($aiSetting) {
                    // Extract text from document
                    $documentText = $this->extractTextFromDocument($requirementsDocument);
                    
                    // Process with AI
                    $this->processDocumentWithAI($project, $aiSetting, $documentText, $aiModel, $enableSearch, $aiPromptIds);
                    
                    return redirect()->route('projects.show', $project)
                        ->with('success', 'Project updated successfully. AI is processing your document and populating project details.');
                }
            } catch (Exception $e) {
                
                return redirect()->route('projects.show', $project)
                    ->with('warning', 'Project updated successfully, but AI processing failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Extract text from uploaded document (PDF or DOCX).
     *
     * @param \Illuminate\Http\UploadedFile $document
     * @return string
     * @throws \Exception
     */
    private function extractTextFromDocument($document): string
    {
        $extension = $document->getClientOriginalExtension();
        $tempPath = $document->getRealPath();
        
        try {
            if (strtolower($extension) === 'pdf') {
                try {
                    // For PDF files, use the Smalot PDF Parser
                    $parser = new Parser();
                    $pdf = $parser->parseFile($tempPath);
                    $text = $pdf->getText();
                    
                    return $text;
                } catch (\Exception $e) {
                    
                    // Try alternative extraction method
                    try {                     
                        // Use pdftotext if available (Linux/Mac)
                        if (function_exists('shell_exec') && strtolower(PHP_OS) !== 'winnt') {
                            $outputFile = tempnam(sys_get_temp_dir(), 'pdf_');
                            $command = "pdftotext " . escapeshellarg($tempPath) . " " . escapeshellarg($outputFile);
                            shell_exec($command);
                            
                            if (file_exists($outputFile)) {
                                $text = file_get_contents($outputFile);
                                unlink($outputFile); // Clean up
                                
                                if (!empty($text)) {
                                    return $text;
                                }
                            }
                        }
                        
                        throw new Exception("All PDF extraction methods failed");
                    } catch (\Exception $fallbackEx) {
                        throw $e; // Throw the original exception
                    }
                }
            } elseif (in_array(strtolower($extension), ['docx', 'doc'])) {
                try {
                    
                    // For DOCX files, use PhpWord
                    if (strtolower($extension) === 'docx') {
                        $phpWord = IOFactory::load($tempPath);
                    } else {
                        // For DOC files, specify the reader explicitly
                        $phpWord = IOFactory::createReader('MsDoc')->load($tempPath);
                    }
                    
                    $text = '';
                    $elementCount = 0;
                    
                    // Extract text from each section
                    foreach ($phpWord->getSections() as $sectionIndex => $section) {
                        $elements = $section->getElements();
                        $elementCount += count($elements);
                                                
                        foreach ($elements as $elementIndex => $element) {
                            // Handle text elements
                            if (method_exists($element, 'getText')) {
                                $elementText = $element->getText();
                                $text .= $elementText . ' ';
                            }
                            // Handle tables
                            elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                                foreach ($element->getRows() as $row) {
                                    foreach ($row->getCells() as $cell) {
                                        foreach ($cell->getElements() as $cellElement) {
                                            if (method_exists($cellElement, 'getText')) {
                                                $text .= $cellElement->getText() . ' ';
                                            }
                                        }
                                    }
                                }
                            }
                            // Handle other container elements
                            elseif (method_exists($element, 'getElements')) {
                                foreach ($element->getElements() as $childElement) {
                                    if (method_exists($childElement, 'getText')) {
                                        $text .= $childElement->getText() . ' ';
                                    }
                                }
                            }
                        }
                    }
                    
                    return $text;
                } catch (\Exception $e) {
                    // Try alternative extraction method
                    try {                        
                        // Use shell command if available (Linux/Mac)
                        if (function_exists('shell_exec') && strtolower(PHP_OS) !== 'winnt') {
                            $command = "cat " . escapeshellarg($tempPath) . " | strings";
                            $text = shell_exec($command);
                            
                            if (!empty($text)) {
                                return $text;
                            }
                        }
                        
                        // If we're on Windows or shell_exec failed, try file_get_contents as last resort
                        $rawContent = file_get_contents($tempPath);
                        $text = preg_replace('/[^\x20-\x7E\r\n]/', ' ', $rawContent);
                        $text = preg_replace('/\s+/', ' ', $text);
                        
                        return $text;
                    } catch (\Exception $fallbackEx) {
                        throw $e; // Throw the original exception
                    }
                }
            } elseif (in_array(strtolower($extension), ['txt'])) {
                // For plain text files, just read the content
                $text = file_get_contents($tempPath);
                
                return $text;
            } else {
                throw new Exception("Unsupported file format: " . $extension);
            }
        } catch (Exception $e) {
            
            // If we're in production, provide a fallback rather than failing completely
            if (app()->environment('production')) {
               
                // Try to get at least some content from the file
                try {
                    $rawContent = file_get_contents($tempPath);
                    $text = preg_replace('/[^\x20-\x7E\r\n]/', ' ', $rawContent);
                    $text = preg_replace('/\s+/', ' ', $text);
                    
                    if (!empty(trim($text))) {
                        return $text;
                    }
                } catch (\Exception $fallbackEx) {
                    Log::error('Fallback extraction also failed: ' . $fallbackEx->getMessage());
                }
                
                // If all else fails, return a message that can be used by the AI
                return "Document text extraction failed. Please analyze this job based on the title and other available information. Error: " . $e->getMessage();
            }
            
            // In development, throw the exception to help with debugging
            throw $e;
        }
    }

    /**
     * Process document with AI and update project fields.
     *
     * @param \App\Models\Project $project
     * @param \App\Models\AISetting $aiSetting
     * @param string $documentText
     * @return void
     * @throws \Exception
     */
    private function processDocumentWithAI(Project $project, AISetting $aiSetting, string $documentText, ?string $selectedModel = null, bool $enableSearch = false, ?array $aiPromptIds = null): void
    {
        // Update the project status to indicate that AI processing is in progress
        $project->update([
            'ai_processing_status' => 'processing'
        ]);
        
        // Use the selected model if provided, otherwise use default
        $aiModel = $selectedModel ?? 'gpt-4';
        try {
            // Get the current user ID
            $userId = Auth::id();
            
            // Dispatch the job to process the document with AI in the background
            ProjectResearchWithAI::dispatch(
                $project,
                $aiSetting,
                $documentText,
                $selectedModel,
                $enableSearch,
                $aiPromptIds,
                $userId
            );
            
            // Update the project status to indicate that AI processing is in progress
            $project->update([
                'ai_processing_status' => 'processing'
            ]);
            
        } catch (Exception $e) {
            throw $e;
        }
    }

}

