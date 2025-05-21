<?php

namespace App\Http\Controllers;

use App\Models\AISetting;
use App\Models\AIPrompt;
// Removed AIUsageLog use statement
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
// Auth facade is likely already imported above
use App\Services\AIService; // Import AIService
use App\Services\ModelRegistryService; // Import the new service

class AISettingController extends Controller
{
    /**
     * Display a listing of AI settings.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $this->authorize('viewAny', AISetting::class);
        
        $aiSettings = AISetting::all();
        // Removed usage statistics fetching
        
        // Pass only necessary data to the view
        return view('ai_settings.index', compact('aiSettings'));
    }

    /**
     * Show the form for creating a new AI setting.
     *
     * @return \Illuminate\View\View
     */
    // Inject ModelRegistryService
    public function create(ModelRegistryService $modelRegistryService): View 
    {
        $this->authorize('create', AISetting::class);
        
        // Limit providers to the main ones for now
        $providers = [
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic',
            'google' => 'Google AI',
        ];
        
        $providerModels = $modelRegistryService->getModels(); // Fetch the provider->models map
        return view('ai_settings.create', compact('providers', 'providerModels')); // Pass the map
    }

    /**
     * Store a newly created AI setting in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AISetting::class);
        
        $validated = $request->validate([
            'provider' => ['required', 'string'], // Removed unique rule
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'organization_id' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'models' => 'nullable|array',
            'models.*' => 'string',
        ]);
        
        // Handle is_default
        if ($validated['is_default'] ?? false) {
            // Set all other providers to not default
            AISetting::where('is_default', true)->update(['is_default' => false]);
        }
        
        $aiSetting = AISetting::create([
            'provider' => $validated['provider'],
            'name' => $validated['name'],
            'api_key' => $validated['api_key'],
            'organization_id' => $validated['organization_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
            'models' => $validated['models'] ?? [],
        ]);
        
        return redirect()->route('ai-settings.index')
            ->with('success', 'AI provider added successfully.');
    }

    /**
     * Show the form for editing the specified AI setting.
     *
     * @param  \App\Models\AISetting  $aiSetting
     * @return \Illuminate\View\View
     */
    // Inject ModelRegistryService
    public function edit(AISetting $aiSetting, ModelRegistryService $modelRegistryService): View 
    {
        $this->authorize('update', $aiSetting);
        
        $providers = [ // Keep this for display name if needed, or remove if not used
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic',
            'google' => 'Google AI',
        ];
        
        $providerModels = $modelRegistryService->getModels(); // Fetch the provider->models map
        return view('ai_settings.edit', compact('aiSetting', 'providers', 'providerModels')); // Pass the map
    }

    /**
     * Update the specified AI setting in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AISetting  $aiSetting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AISetting $aiSetting): RedirectResponse
    {
        $this->authorize('update', $aiSetting);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required|string',
            'organization_id' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'models' => 'nullable|array',
            'models.*' => 'string',
        ]);
        
        // Handle is_default
        if ($validated['is_default'] ?? false) {
            // Set all other providers to not default
            AISetting::where('id', '!=', $aiSetting->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $aiSetting->update([
            'name' => $validated['name'],
            'api_key' => $validated['api_key'],
            'organization_id' => $validated['organization_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'is_default' => $validated['is_default'] ?? false,
            'models' => $validated['models'] ?? [],
        ]);
        
        return redirect()->route('ai-settings.index')
            ->with('success', 'AI provider updated successfully.');
    }

    /**
     * Remove the specified AI setting from storage.
     *
     * @param  \App\Models\AISetting  $aiSetting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AISetting $aiSetting): RedirectResponse
    {
        $this->authorize('delete', $aiSetting);
        
        $aiSetting->delete();
        
        return redirect()->route('ai-settings.index')
            ->with('success', 'AI provider deleted successfully.');
    }

    /**
     * Test the connection to the AI provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AISetting  $aiSetting
     * @param  \App\Services\AIService  $aiService  // Inject AIService
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(Request $request, AISetting $aiSetting, AIService $aiService) // Inject AIService
    {
        $this->authorize('update', $aiSetting);
        
        try {
            // Check if the setting is active and has models configured
            if (!$aiSetting->is_active) {
                return response()->json(['success' => false, 'message' => 'Provider is not active.'], 400);
            }
            if (empty($aiSetting->models)) {
                 return response()->json(['success' => false, 'message' => 'No models configured for this provider setting.'], 400);
            }

            // Use the first configured model for the test
            $testModel = $aiSetting->models[0];
            $testPrompt = "Test connection.";
            // Use Auth facade directly as it's confirmed to be imported
            $userId = Auth::id() ?? 1;

            // Make a simple call using the generateContent method
            $response = $aiService->generateContent(
                $aiSetting,
                $testModel,
                $testPrompt,
                ['max_tokens' => 5], // Keep token usage minimal
                $userId,
                'connection_test'
            );

            // If generateContent didn't throw an exception, assume success
            return response()->json([
                'success' => true,
                'message' => 'Connection successful!', // Updated message
            ]);
        } catch (\Exception $e) {
            Log::error('AI provider connection test failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Removed usageLogs method

    /**
     * Display the AI prompts management page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    // Added Request $request parameter
    public function prompts(Request $request): View 
    {
        $this->authorize('viewAny', AISetting::class);
        
        $query = AIPrompt::query();

        // Apply filters from query parameters
        $selectedFeature = $request->query('feature');
        $selectedProvider = $request->query('provider');

        if ($selectedFeature) {
            $query->where('feature', $selectedFeature);
        }
        if ($selectedProvider) {
            $query->where('provider', $selectedProvider);
        }

        $prompts = $query->orderBy('feature')->orderBy('name')->get();
        
        // Get distinct features and providers for filter dropdowns
        $features = AIPrompt::select('feature')->distinct()->pluck('feature');
        $providers = AISetting::select('provider')->distinct()->pluck('provider'); // Use AISetting for provider list
        
        // Pass selected filters back to the view
        return view('ai_settings.prompts', compact('prompts', 'features', 'providers', 'selectedFeature', 'selectedProvider'));
    }

    /**
     * Show the form for creating a new AI prompt.
     *
     * @return \Illuminate\View\View
     */
    public function createPrompt(): View
    {
        $this->authorize('create', AISetting::class);
        
        $features = [
            // Job Description related
            'job_description' => 'Job Description Generation',
            'qualifying_questions' => 'Qualifying Questions Generation',
            
            // Project related
            'company_research' => 'Company Research (Project)',
            'salary_comparison' => 'Salary Comparison (Project)',
            'search_strings' => 'Search Strings Generation (Project)',
            'keywords' => 'Keywords Generation (Project)',
            'ai_questions' => 'AI Interview Questions (Project)',
            'job_details' => 'Job Details Extraction (Project)',
            
            // Candidate related
            'candidate_questions' => 'Candidate Questions Generation',
            'recruiter_questions' => 'Recruiter Questions Generation',
            'cv_analyzer' => 'CV Analysis & Ranking',
            'resume_detail_extraction' => 'Resume Detail Extraction',
        ];
        
        $providers = AISetting::pluck('name', 'provider');
        
        return view('ai_settings.create_prompt', compact('features', 'providers'));
    }

    /**
     * Store a newly created AI prompt in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePrompt(Request $request): RedirectResponse
    {
        $this->authorize('create', AISetting::class);
        
        $validated = $request->validate([
            'feature' => 'required|string',
            'name' => 'required|string|max:255',
            'prompt_template' => 'required|string',
            'parameters' => 'nullable',
            'provider' => 'nullable|string',
            'model' => 'nullable|string',
            'is_default' => 'boolean',
        ]);
        
        // Process parameters - could be a JSON string or an array
        $parameters = [];
        if (isset($validated['parameters'])) {
            if (is_string($validated['parameters']) && !empty($validated['parameters'])) {
                // Try to decode JSON string
                $decoded = json_decode($validated['parameters'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $parameters = $decoded;
                }
            } elseif (is_array($validated['parameters'])) {
                $parameters = $validated['parameters'];
            }
        }
        
        // Handle is_default
        if ($validated['is_default'] ?? false) {
            // Set all other prompts for this feature to not default
            AIPrompt::where('feature', $validated['feature'])
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $prompt = AIPrompt::create([
            'feature' => $validated['feature'],
            'name' => $validated['name'],
            'prompt_template' => $validated['prompt_template'],
            'parameters' => $parameters,
            'provider' => $validated['provider'] ?? null,
            'model' => $validated['model'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'created_by' => Auth::id(),
        ]);
        
        return redirect()->route('ai-settings.prompts')
            ->with('success', 'AI prompt created successfully.');
    }

    /**
     * Show the form for editing the specified AI prompt.
     *
     * @param  \App\Models\AIPrompt  $prompt
     * @return \Illuminate\View\View
     */
    public function editPrompt(AIPrompt $prompt): View
    {
        $this->authorize('viewAny', AISetting::class);
        
        $features = [
            // Job Description related
            'job_description' => 'Job Description Generation',
            'qualifying_questions' => 'Qualifying Questions Generation',
            
            // Project related
            'company_research' => 'Company Research',
            'salary_comparison' => 'Salary Comparison',
            'search_strings' => 'Search Strings Generation',
            'keywords' => 'Keywords Generation',
            'ai_questions' => 'AI Interview Questions',
            'job_details' => 'Job Details Extraction',
            
            // Candidate related
            'candidate_questions' => 'Candidate Questions Generation',
            'recruiter_questions' => 'Recruiter Questions Generation',
            'cv_analyzer' => 'CV Analysis & Ranking',
            'resume_detail_extraction' => 'Resume Detail Extraction',
        ];
        
        $providers = AISetting::pluck('name', 'provider');
        
        return view('ai_settings.edit_prompt', compact('prompt', 'features', 'providers'));
    }

    /**
     * Update the specified AI prompt in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AIPrompt  $prompt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePrompt(Request $request, AIPrompt $prompt): RedirectResponse
    {
        $this->authorize('viewAny', AISetting::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prompt_template' => 'required|string',
            'parameters' => 'nullable',
            'provider' => 'nullable|string',
            'model' => 'nullable|string',
            'is_default' => 'boolean',
        ]);
        
        // Process parameters - could be a JSON string or an array
        $parameters = [];
        if (isset($validated['parameters'])) {
            if (is_string($validated['parameters']) && !empty($validated['parameters'])) {
                // Try to decode JSON string
                $decoded = json_decode($validated['parameters'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $parameters = $decoded;
                }
            } elseif (is_array($validated['parameters'])) {
                $parameters = $validated['parameters'];
            }
        }
        
        // Handle is_default
        if ($validated['is_default'] ?? false) {
            // Set all other prompts for this feature to not default
            AIPrompt::where('feature', $prompt->feature)
                ->where('id', '!=', $prompt->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }
        
        $prompt->update([
            'name' => $validated['name'],
            'prompt_template' => $validated['prompt_template'],
            'parameters' => $parameters,
            'provider' => $validated['provider'] ?? null,
            'model' => $validated['model'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
        ]);
        
        return redirect()->route('ai-settings.prompts')
            ->with('success', 'AI prompt updated successfully.');
    }

    /**
     * Remove the specified AI prompt from storage.
     *
     * @param  \App\Models\AIPrompt  $prompt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyPrompt(AIPrompt $prompt): RedirectResponse
    {
        $this->authorize('viewAny', AISetting::class);
        
        $prompt->delete();
        
        return redirect()->route('ai-settings.prompts')
            ->with('success', 'AI prompt deleted successfully.');
    }
}