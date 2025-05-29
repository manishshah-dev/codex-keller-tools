<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Generate Profile Content') }}: {{ $profile->title }}
                </h2>
                <p class="text-gray-600 mt-1">
                    <a href="{{ route('projects.candidates.profiles.edit', [$project, $candidate, $profile]) }}" class="text-indigo-600 hover:text-indigo-900">
                        Back to Edit Profile
                    </a>
                </p>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Generate Profile Content</h3>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        Use AI to generate content for your profile. You can generate a full profile or content for existing headings.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- Candidate Info Card -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2">Candidate Information</h4>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Name:</span>
                                        <p class="font-medium">{{ $candidate->full_name }}</p>
                                    </div>
                                    @if($candidate->current_position)
                                    <div>
                                        <span class="text-sm text-gray-500">Current Position:</span>
                                        <p>{{ $candidate->current_position }}</p>
                                    </div>
                                    @endif
                                    @if($candidate->current_company)
                                    <div>
                                        <span class="text-sm text-gray-500">Current Company:</span>
                                        <p>{{ $candidate->current_company }}</p>
                                    </div>
                                    @endif
                                    <div>
                                        <span class="text-sm text-gray-500">Resume:</span>
                                        <p>
                                            <a href="{{ route('candidates.resume.view', $candidate) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                                View Resume
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Job Info Card -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2">Job Information</h4>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Job Title:</span>
                                        <p class="font-medium">{{ $project->title }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Requirements:</span>
                                        <p>{{ count($requirements) }} defined</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Top Requirements:</span>
                                        <ul class="list-disc pl-5 text-sm">
                                            @foreach($requirements->sortByDesc('weight')->take(3) as $requirement)
                                                <li>{{ $requirement->name }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Info Card -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2">Profile Information</h4>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Title:</span>
                                        <p class="font-medium">{{ $profile->title }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Status:</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $profile->status_badge_class }}">
                                            {{ ucfirst($profile->status) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Headings:</span>
                                        <p>{{ count($profile->formatted_headings) }} defined</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Completion:</span>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-1">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $profile->completion_percentage }}%"></div>
                                        </div>
                                        <div class="text-xs">{{ $profile->completion_percentage }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('projects.candidates.profiles.generate', [$project, $candidate, $profile]) }}" method="POST">
                            @csrf
                            <div class="space-y-8">
                                <!-- AI Settings -->
                                <div>
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">AI Settings</h3>
                                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                        <div class="sm:col-span-3">
                                            <label for="ai_setting_id" class="block text-sm font-medium text-gray-700">AI Setting</label>
                                            <select id="ai_setting_id" name="ai_setting_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Select AI Setting</option>
                                                @foreach($aiSettings as $setting)
                                                     <option value="{{ $setting->id }}"
                                                             data-provider="{{ $setting->provider }}"
                                                             data-models='@json($setting->models ?? [])'
                                                    >
                                                         {{ $setting->name }} ({{ ucfirst($setting->provider) }})
                                                     </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="ai_model" class="block text-sm font-medium text-gray-700">AI Model</label>
                                            <select id="ai_model" name="ai_model" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <!-- Will be populated by JavaScript -->
                                                <option value="">Select AI Setting first</option>
                                            </select>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="ai_prompt_id" class="block text-sm font-medium text-gray-700">Resume Extraction Prompt</label>
                                            <select id="ai_prompt_id" name="ai_prompt_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Use Default/Generic Prompt</option>
                                                @foreach($prompts as $prompt)
                                                    <option value="{{ $prompt->id }}"
                                                            data-provider="{{ $prompt->provider }}"
                                                            data-model="{{ $prompt->model }}"
                                                            {{ $prompt->is_default ? '(Default)' : '' }}>
                                                        {{ $prompt->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500">Used for extracting data from the resume</p>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="summary_prompt_id" class="block text-sm font-medium text-gray-700">Summary Prompt</label>
                                            <select id="summary_prompt_id" name="summary_prompt_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Use Default/Generic Prompt</option>
                                                @foreach($summaryPrompts as $prompt)
                                                    <option value="{{ $prompt->id }}"
                                                            data-provider="{{ $prompt->provider }}"
                                                            data-model="{{ $prompt->model }}"
                                                            {{ $prompt->is_default ? '(Default)' : '' }}>
                                                        {{ $prompt->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500">Used for generating the profile summary</p>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="headings_prompt_id" class="block text-sm font-medium text-gray-700">Headings Prompt</label>
                                            <select id="headings_prompt_id" name="headings_prompt_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Use Default/Generic Prompt</option>
                                                @foreach($headingsPrompts as $prompt)
                                                    <option value="{{ $prompt->id }}"
                                                            data-provider="{{ $prompt->provider }}"
                                                            data-model="{{ $prompt->model }}"
                                                            {{ $prompt->is_default ? '(Default)' : '' }}>
                                                        {{ $prompt->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500">Used for generating profile section headings</p>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="content_prompt_id" class="block text-sm font-medium text-gray-700">Content Prompt</label>
                                            <select id="content_prompt_id" name="content_prompt_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">Use Default/Generic Prompt</option>
                                                @foreach($contentPrompts as $prompt)
                                                    <option value="{{ $prompt->id }}"
                                                            data-provider="{{ $prompt->provider }}"
                                                            data-model="{{ $prompt->model }}"
                                                            {{ $prompt->is_default ? '(Default)' : '' }}>
                                                        {{ $prompt->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="mt-1 text-xs text-gray-500">Used for generating content for each heading</p>
                                        </div>

                                    </div>
                                </div>

                                <!-- Generation Options -->
                                <div>
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Generation Options</h3>
                                    <div class="mt-4">
                                        <div class="space-y-4">
                                            <div class="relative flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="generation_type_full" name="generation_type" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" value="full" checked>
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="generation_type_full" class="font-medium text-gray-700">Generate Full Profile</label>
                                                    <p class="text-gray-500">Generate a complete profile including summary, headings, and content. This will replace any existing content.</p>
                                                </div>
                                            </div>
                                            
                                            <!-- "Generate Headings Only" option removed as it was not functioning correctly and deemed unnecessary -->
                                            
                                            <div class="relative flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="generation_type_content" name="generation_type" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" value="content">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="generation_type_content" class="font-medium text-gray-700">Generate Content for Existing Headings</label>
                                                    <p class="text-gray-500">Generate content for the existing headings. This will replace any existing content but keep the headings structure.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Custom Headings section removed as it was only used with the "Generate Headings Only" option -->

                                <!-- Submit Button -->
                                <div class="pt-5 border-t border-gray-200">
                                    <div class="flex justify-end">
                                        <a href="{{ route('projects.candidates.profiles.edit', [$project, $candidate, $profile]) }}" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Cancel
                                        </a>
                                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Generate Content
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Provider models data from PHP
        const settingModels = @json($aiSettings->mapWithKeys(function($setting) {
            return [$setting->id => $setting->models ?? []];
        }));

        const providerModels = @json($providerModels ?? []);
        const allPrompts = @json($prompts ?? []);
        const summaryPrompts = @json($summaryPrompts ?? []);
        const headingsPrompts = @json($headingsPrompts ?? []);
        const contentPrompts = @json($contentPrompts ?? []);
        
        document.addEventListener('DOMContentLoaded', function() {
            const aiSettingId = document.getElementById('ai_setting_id');
            const aiModel = document.getElementById('ai_model');
            const aiPromptId = document.getElementById('ai_prompt_id');
            // References to removed elements deleted
            
            // Function to update model dropdown based on selected setting
            function updateModelDropdown() {
                const settingId = aiSettingId.value;
                const selectedOption = aiSettingId.options[aiSettingId.selectedIndex];
                const provider = selectedOption ? selectedOption.dataset.provider : null;
                
                // Clear current options
                aiModel.innerHTML = '';
                
                if (!provider) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Select AI Setting first';
                    aiModel.appendChild(option);
                    aiModel.disabled = true;
                    return;
                }
                
                // Get models for this provider from the providerModels map
                const models = providerModels[provider] || [];
                const currentModel = "{{ $profile->ai_model }}";
                
                // Get the list of models enabled in this specific setting
                let enabledModelsInSetting = [];
                try {
                    enabledModelsInSetting = JSON.parse(selectedOption.dataset.models || '[]');
                    if (!Array.isArray(enabledModelsInSetting)) enabledModelsInSetting = [];
                } catch (e) {
                    console.error("Error parsing enabled models data attribute:", e);
                    enabledModelsInSetting = [];
                }
                
                // Determine which model to select
                let modelToSelect = null;
                
                // First, check if the current model is in the available models
                if (currentModel && models.includes(currentModel)) {
                    modelToSelect = currentModel;
                } else if (enabledModelsInSetting.length > 0) {
                    // Otherwise, select the first model enabled in this setting
                    modelToSelect = enabledModelsInSetting[0];
                } else if (models.length > 0) {
                    // If no enabled models in setting, use the first available model for this provider
                    modelToSelect = models[0];
                }
                
                if (models.length > 0) {
                    models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model;
                        option.textContent = model;
                        if (model === modelToSelect) {
                            option.selected = true;
                        }
                        aiModel.appendChild(option);
                    });
                    aiModel.disabled = false;
                } else {
                    // Add a default option if no models are available
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No models available for ' + provider;
                    aiModel.appendChild(option);
                    aiModel.disabled = true;
                }
                
                updatePromptsDropdown();
            }

            function updatePromptsDropdown() {
                const selectedOption = aiSettingId.options[aiSettingId.selectedIndex];
                const provider = selectedOption ? selectedOption.dataset.provider : null;
                const model = aiModel.value;
                
                // Get all prompt dropdowns
                const promptDropdowns = [
                    {
                        element: aiPromptId,
                        feature: 'resume_detail_extraction',
                        prompts: allPrompts
                    },
                    {
                        element: document.getElementById('summary_prompt_id'),
                        feature: 'profile_summary',
                        prompts: summaryPrompts
                    },
                    {
                        element: document.getElementById('headings_prompt_id'),
                        feature: 'profile_headings',
                        prompts: headingsPrompts
                    },
                    {
                        element: document.getElementById('content_prompt_id'),
                        feature: 'profile_content',
                        prompts: contentPrompts
                    }
                ];
                
                // Update each dropdown
                promptDropdowns.forEach(dropdown => {
                    if (!dropdown.element) return;
                    
                    // Clear current options
                    dropdown.element.innerHTML = '<option value="">Use Default/Generic Prompt</option>';
                    
                    if (!provider || !model) {
                        dropdown.element.disabled = true;
                        return;
                    }
                    
                    // Filter prompts by provider, model and feature
                    const filtered = dropdown.prompts.filter(p =>
                        (p.feature === dropdown.feature) &&
                        (!p.provider || p.provider === provider) &&
                        (!p.model || p.model === model)
                    );
                    
                    if (filtered.length > 0) {
                        filtered.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.id;
                            opt.textContent = p.name + (p.is_default ? ' (Default)' : '');
                            dropdown.element.appendChild(opt);
                        });
                        dropdown.element.disabled = false;
                    } else {
                        dropdown.element.disabled = false; // Still allow selection of "Use Default/Generic Prompt"
                    }
                });
            }
            
            // Functions related to custom headings section removed
            
            // Update models when setting changes
            // aiSettingId.addEventListener('change', updateModelDropdown);
             aiSettingId.addEventListener('change', function() {
                updateModelDropdown();
                updatePromptsDropdown();
            });

            // Update prompts when model changes
            aiModel.addEventListener('change', updatePromptsDropdown);

            // Toggle custom headings section when generation type changes
            document.querySelectorAll('input[name="generation_type"]').forEach(function(radio) {
                radio.addEventListener('change', toggleCustomHeadingsSection);
            });
            
            // Add custom heading input when button is clicked
            addHeadingBtn.addEventListener('click', addCustomHeadingInput);
            
            // Event listeners for custom headings removed
            
            // Initialize model dropdown and prompts dropdown
            updateModelDropdown();
            updatePromptsDropdown();
            
            // Custom headings initialization removed
        });
    </script>
</x-app-layout>