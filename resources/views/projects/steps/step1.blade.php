<!-- Step 1: Basic Information -->
<div class="step-content" id="step-1">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Project Information</h3>
    
    <div class="mb-4">
        <x-input-label for="title" :value="__('Project Title')" />
        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $project->title ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <h4 class="font-medium text-gray-700 mb-2">Client Requirements Document</h4>
        <p class="text-sm text-gray-600 mb-4">Upload a client requirements document to automatically populate project details using AI, or enter details manually.</p>
        
        <div class="mb-4">
            <x-input-label for="requirements_document" :value="__('Upload Document (PDF, DOCX)')" />
            <input id="requirements_document" type="file" name="requirements_document" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 mt-1" accept=".pdf,.docx,.doc" />
            <x-input-error :messages="$errors->get('requirements_document')" class="mt-2" />
        </div>
        
        <div class="mt-6 pt-4 border-t border-gray-200">
            <h5 class="font-medium text-gray-700 mb-2">Claap Meeting Transcript</h5>
            <p class="text-sm text-gray-600 mb-4">Upload a Claap meeting transcript to include in the AI analysis. This will help extract key requirements from client discussions.</p>
            
            <div class="mb-4">
                <x-input-label for="claap_transcript_file" :value="__('Upload Claap Transcript')" />
                <input id="claap_transcript_file" type="file" name="claap_transcript_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 mt-1" accept=".txt,.pdf,.docx,.doc" />
                <x-input-error :messages="$errors->get('claap_transcript_file')" class="mt-2" />
                <p class="text-sm text-gray-500 mt-1">Upload a transcript file from Claap (PDF, DOCX, DOC, or TXT). <strong>This will be processed by AI along with the requirements document</strong> to extract key requirements.</p>
            </div>
            
            <div class="mb-4">
                <x-input-label for="claap_transcript" :value="__('Claap Transcript Text')" />
                <textarea id="claap_transcript" name="claap_transcript" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Paste the transcript from Claap here if you don't have a file to upload">{{ old('claap_transcript', $project->claap_transcript ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('claap_transcript')" class="mt-2" />
                <p class="text-sm text-gray-500 mt-1">If you've uploaded a transcript file, this field will be automatically populated. Otherwise, you can paste the transcript text here.</p>
            </div>
        </div>

        
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 border-t border-gray-200 pt-4">
            <div>
                <x-input-label for="ai_setting_id" :value="__('AI Setting')" />
                <select id="ai_setting_id" name="ai_setting_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @if(count($aiProviders) > 0)
                        <option value="">Select AI Setting</option>
                        @foreach($aiProviders as $setting)
                            <option value="{{ $setting->id }}"
                                    data-provider-type="{{ $setting->provider }}"
                                    data-models="{{ json_encode($setting->models ?? []) }}"
                                    {{ old('ai_setting_id', $project->ai_setting_id ?? '') == $setting->id ? 'selected' : '' }}>
                                {{ $setting->name }} ({{ ucfirst($setting->provider) }})
                            </option>
                        @endforeach
                    @else
                        <option value="">No AI providers configured</option>
                    @endif
                </select>
                
                @if(count($aiProviders ?? []) == 0)
                <p class="text-sm text-red-600 mt-1">
                    No AI providers found.
                    <a href="{{ route('ai-settings.create') }}" class="text-indigo-600 hover:text-indigo-900">Add an AI provider</a>
                </p>
                @endif
                
                <x-input-error :messages="$errors->get('ai_setting_id')" class="mt-2" />
            </div>
            
            <div>
                <x-input-label for="ai_model" :value="__('AI Model')" />
                <select id="ai_model" name="ai_model" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" disabled>
                    <option value="">Select AI Setting first</option>
                    <!-- Models will be populated by JavaScript -->
                </select>
                <x-input-error :messages="$errors->get('ai_model')" class="mt-2" />
            </div>
        </div>

        <div class="mb-4 border-t border-gray-200 pt-4">
            <h4 class="font-medium text-gray-700 mb-2">AI Prompts (Select one per category)</h4>
            <p class="text-sm text-gray-500 mb-4">Select one prompt template for each research type. Each category will use the selected prompt or fall back to the default if none is selected.</p>
            
            <div id="prompt-containers">
                <!-- Prompt containers will be dynamically created here -->
                <div class="text-sm text-gray-500 py-2">Select AI Setting and Model first to see available prompts</div>
            </div>
            
            <x-input-error :messages="$errors->get('ai_prompt_id')" class="mt-2" />
        </div>
        
        <div class="flex items-center mb-2">
            <input id="use_ai" name="use_ai" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('use_ai') ? 'checked' : '' }} @if(count($aiProviders ?? []) == 0) disabled @endif>
            <label for="use_ai" class="ml-2 block text-sm text-gray-900">Process with AI to automatically populate fields</label>
        </div>
        <div id="ai_validation_error" class="text-sm text-red-600 mt-1 mb-2 hidden">
            Please provide at least one of: Requirements Document, Claap Transcript File, or Claap Transcript Text when using AI processing.
        </div>
        
        <div class="flex items-center" id="enable_search_container" style="display: none;">
            <input id="enable_search" name="enable_search" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" {{ old('enable_search') ? 'checked' : '' }}>
            <label for="enable_search" class="ml-2 block text-sm text-gray-900">Enable Google Search for company research (company URL, size, founding date, etc.)</label>
        </div>

    </div>

    <div class="mb-4">
        <x-input-label for="department" :value="__('Department')" />
        <x-text-input id="department" class="block mt-1 w-full" type="text" name="department" :value="old('department', $project->department ?? '')" />
        <x-input-error :messages="$errors->get('department')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="location" :value="__('Location')" />
        <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $project->location ?? '')" />
        <x-input-error :messages="$errors->get('location')" class="mt-2" />
    </div>

    <div class="mb-4">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $project->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    @if(isset($project))
    <div class="mb-4">
        <x-input-label for="status" :value="__('Status')" />
        <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
            <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="on-hold" {{ old('status', $project->status) === 'on-hold' ? 'selected' : '' }}>On Hold</option>
            <option value="cancelled" {{ old('status', $project->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
            const aiSettingSelect = document.getElementById('ai_setting_id');
            const aiModelSelect = document.getElementById('ai_model');
            const promptContainers = document.getElementById('prompt-containers');
            const enableSearchContainer = document.getElementById('enable_search_container');
            const allPrompts = {!! json_encode($prompts ?? []) !!}; // Get all prompts
            const providerModelsMap = {!! json_encode($providerModels ?? []) !!}; // Get the full model map
            const oldAiSettingId = '{{ old('ai_setting_id', $project->ai_setting_id ?? '') }}'; // Get old setting ID
            const oldAiModel = '{{ old('ai_model', $project->ai_model ?? '') }}'; // Get old model
            const oldAiPromptId = '{{ old('ai_prompt_id', $project->ai_prompt_id ?? '') }}'; // Get old prompt ID
        
        console.log("Script loaded, initializing AI dropdowns");
        
        // Function to update model options based on selected AI setting
        function updateModelOptions() {
            console.log("Updating model options");
            
            // Get the selected option
            const selectedOption = aiSettingSelect.options[aiSettingSelect.selectedIndex];
            console.log("Selected AI setting:", selectedOption?.value);
            
            // Get provider type and models from data attributes
            const providerType = selectedOption?.getAttribute('data-provider-type');
            const modelsJson = selectedOption?.getAttribute('data-models');
            console.log("Provider type:", providerType);
            console.log("Models JSON:", modelsJson);
            
            // Clear existing options
            aiModelSelect.innerHTML = '';
            
            // Reset prompt containers
            promptContainers.innerHTML = '<div class="text-sm text-gray-500 py-2">Select AI Model first</div>';
            
            // Show/hide Google Search option based on provider
            if (providerType === 'google') {
                enableSearchContainer.style.display = 'flex';
            } else {
                enableSearchContainer.style.display = 'none';
            }
            
            // Ensure we have a selected option with a value before proceeding
            if (!selectedOption || !selectedOption.value) {
                aiModelSelect.innerHTML = '<option value="">Select AI Setting first</option>';
                aiModelSelect.disabled = true;
                promptContainers.innerHTML = '<div class="text-sm text-gray-500 py-2">Select AI Setting first</div>';
                return;
            }
            
            // Get the list of models enabled in this specific setting
            let enabledModelsInSetting = [];
            try {
                enabledModelsInSetting = JSON.parse(modelsJson || '[]');
                if (!Array.isArray(enabledModelsInSetting)) enabledModelsInSetting = [];
                console.log("Enabled models in setting:", enabledModelsInSetting);
            } catch (e) {
                console.error("Error parsing enabled models data attribute for setting ID " + selectedOption.value + ":", e);
                enabledModelsInSetting = [];
            }
            
            // Get all models available for this provider type from the central map
            const allModelsForProvider = providerModelsMap[providerType] || [];
            console.log("All models for provider:", allModelsForProvider);
            
            if (allModelsForProvider.length === 0) {
                // Add a placeholder if no models are defined at all for this provider type
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'No models listed for this provider type';
                aiModelSelect.appendChild(defaultOption);
                aiModelSelect.disabled = true;
            } else {
                // Check if the old selected model is actually valid for THIS setting's enabled models
                let modelToSelect = null;
                if (oldAiModel && enabledModelsInSetting.includes(oldAiModel)) {
                    // Prioritize old value if it's valid for this setting
                    modelToSelect = oldAiModel;
                } else if (enabledModelsInSetting.length > 0) {
                    // Otherwise, select the first model enabled in this setting
                    modelToSelect = enabledModelsInSetting[0];
                }
                console.log("Model to select:", modelToSelect);
                
                // Iterate through ALL models for the provider type
                allModelsForProvider.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model; // Display model name
                    // Pre-select the determined model
                    if (modelToSelect && model === modelToSelect) {
                        option.selected = true;
                    }
                    aiModelSelect.appendChild(option);
                    console.log("Added model option:", model);
                });
                
                // Try to select the first model that IS enabled in this setting
                if (!aiModelSelect.value && enabledModelsInSetting.length > 0) {
                    const firstEnabledModel = enabledModelsInSetting[0];
                    // Ensure the first enabled model exists in the dropdown before selecting
                    if (allModelsForProvider.includes(firstEnabledModel)) {
                        aiModelSelect.value = firstEnabledModel;
                    }
                }
                
                aiModelSelect.disabled = false;
                console.log("AI model dropdown enabled with value:", aiModelSelect.value);
                
                // Trigger prompt update AFTER models are populated
                updatePromptsDropdown();
                
                // If using Select2, refresh it
                if ($.fn.select2) {
                    $(aiModelSelect).select2('destroy').select2({ width: '100%' });
                }
            }
        }
        
        // Function to update prompt dropdowns
        function updatePromptsDropdown() {
            console.log("Updating prompts dropdowns");
            
            const selectedOption = aiSettingSelect.options[aiSettingSelect.selectedIndex];
            const selectedProviderType = selectedOption ? selectedOption.getAttribute('data-provider-type') : null;
            const selectedModel = aiModelSelect.value;
            
            console.log("Selected provider type for prompts:", selectedProviderType);
            console.log("Selected model for prompts:", selectedModel);
            console.log("Available prompts:", allPrompts);
            
            // Filter prompts based on selected provider and model
            const filteredPrompts = allPrompts.filter(prompt =>
                (!prompt.provider || prompt.provider === selectedProviderType) &&
                (!prompt.model || prompt.model === selectedModel)
            );
            
            console.log("Filtered prompts:", filteredPrompts);
            
            // Clear existing prompt containers
            const promptContainers = document.getElementById('prompt-containers');
            promptContainers.innerHTML = '';
            
            if (filteredPrompts.length > 0) {
                // Group prompts by feature
                const promptsByFeature = {};
                filteredPrompts.forEach(prompt => {
                    if (!promptsByFeature[prompt.feature]) {
                        promptsByFeature[prompt.feature] = [];
                    }
                    promptsByFeature[prompt.feature].push(prompt);
                });
                
                // Get old selected prompt IDs as an array
                let oldSelectedPromptIds = [];
                try {
                    // Try to parse the old prompt IDs if they exist
                    if (oldAiPromptId) {
                        oldSelectedPromptIds = oldAiPromptId.split(',').map(id => parseInt(id.trim()));
                    }
                } catch (e) {
                    console.error("Error parsing old prompt IDs:", e);
                    oldSelectedPromptIds = [];
                }
                
                console.log("Old selected prompt IDs:", oldSelectedPromptIds);
                
                // Create a separate dropdown for each feature
                for (const [feature, prompts] of Object.entries(promptsByFeature)) {
                    // Create container for this feature
                    const featureContainer = document.createElement('div');
                    featureContainer.className = 'mb-3 pb-3 border-b border-gray-100';
                    
                    // Create label
                    const label = document.createElement('label');
                    label.className = 'block text-sm font-medium text-gray-700 mb-1';
                    label.textContent = feature.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    featureContainer.appendChild(label);
                    
                    // Create select element
                    const select = document.createElement('select');
                    select.className = 'prompt-select block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
                    select.name = `ai_prompt_id[${feature}]`;
                    select.id = `ai_prompt_id_${feature}`;
                    
                    // Add default option
                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = 'Use Default/Generic Prompt';
                    select.appendChild(defaultOption);
                    
                    // Find if there's a default prompt for this feature
                    const defaultPrompt = prompts.find(p => p.is_default);
                    
                    // Add options for each prompt in this feature
                    prompts.forEach(prompt => {
                        const option = document.createElement('option');
                        option.value = prompt.id;
                        option.textContent = prompt.name + (prompt.is_default ? ' (Default)' : '');
                        
                        // Check if this prompt was previously selected
                        if (oldSelectedPromptIds.includes(parseInt(prompt.id))) {
                            option.selected = true;
                        } else if (!oldSelectedPromptIds.length && defaultPrompt && defaultPrompt.id === prompt.id) {
                            // If no previous selection and this is the default prompt, select it
                            option.selected = true;
                        }
                        
                        select.appendChild(option);
                    });
                    
                    featureContainer.appendChild(select);
                    promptContainers.appendChild(featureContainer);
                }
                
                console.log("AI prompt dropdowns created");
                
                // Initialize Select2 on the new dropdowns
                if ($.fn.select2) {
                    $('.prompt-select').each(function() {
                        $(this).select2({
                            width: '100%',
                            placeholder: "Select a prompt",
                            allowClear: true
                        });
                    });
                }
            } else {
                // No prompts available
                promptContainers.innerHTML = '<div class="text-sm text-gray-500 py-2">No prompts available for the selected AI setting and model</div>';
                console.log("No prompts available");
            }
        }
        
        // Initialize on page load
        if (aiSettingSelect) {
            console.log("Setting up event listeners");
            
            // Update when AI setting changes
            aiSettingSelect.addEventListener('change', function() {
                console.log("AI setting changed to:", this.value);
                updateModelOptions();
            });
            
            // Update prompts when model changes
            aiModelSelect.addEventListener('change', function() {
                console.log("AI model changed to:", this.value);
                updatePromptsDropdown();
            });
            
            // Initial population on page load
            console.log("Initial population on page load");
            console.log("Old AI setting ID:", oldAiSettingId);
            console.log("Current AI setting value:", aiSettingSelect.value);
            
            // If there was an old setting selected, trigger change to populate models/prompts
            if (oldAiSettingId && aiSettingSelect.value === oldAiSettingId) {
                console.log("Triggering change event for old setting");
                aiSettingSelect.dispatchEvent(new Event('change'));
            } else {
                // Otherwise, just populate models for the default selection (if any)
                console.log("Calling updateModelOptions directly");
                updateModelOptions();
            }
            
            // Force an update after a short delay to ensure Select2 is initialized
            setTimeout(function() {
                console.log("Delayed update after 500ms");
                updateModelOptions();
            }, 500);
        }
        
        // Add validation for AI processing
        const useAiCheckbox = document.getElementById('use_ai');
        const requirementsDocumentInput = document.getElementById('requirements_document');
        const claapTranscriptFileInput = document.getElementById('claap_transcript_file');
        const claapTranscriptInput = document.getElementById('claap_transcript');
        const aiValidationError = document.getElementById('ai_validation_error');
        
        // Function to validate AI inputs
        function validateAiInputs() {
            if (useAiCheckbox.checked) {
                // Check if at least one of the three inputs has a value
                const hasRequirementsDocument = requirementsDocumentInput.files && requirementsDocumentInput.files.length > 0;
                const hasClaapTranscriptFile = claapTranscriptFileInput.files && claapTranscriptFileInput.files.length > 0;
                const hasClaapTranscript = claapTranscriptInput.value.trim() !== '';
                
                if (!hasRequirementsDocument && !hasClaapTranscriptFile && !hasClaapTranscript) {
                    aiValidationError.classList.remove('hidden');
                    return false;
                } else {
                    aiValidationError.classList.add('hidden');
                    return true;
                }
            } else {
                // If AI is not being used, no validation needed
                aiValidationError.classList.add('hidden');
                return true;
            }
        }
        
        // Add event listener to the checkbox
        if (useAiCheckbox) {
            useAiCheckbox.addEventListener('change', validateAiInputs);
            
            // Also validate when the inputs change
            requirementsDocumentInput.addEventListener('change', validateAiInputs);
            claapTranscriptFileInput.addEventListener('change', validateAiInputs);
            claapTranscriptInput.addEventListener('input', validateAiInputs);
            
            // Initial validation
            validateAiInputs();
        }
    });
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        console.log("Initializing Select2 dropdowns");
        
        // Initialize Select2 on relevant dropdowns
        $('#ai_setting_id').select2({ width: '100%' });
        $('#ai_model').select2({ width: '100%' });
        
        // Initialize Select2 on any existing prompt dropdowns
        $('.prompt-select').each(function() {
            $(this).select2({
                width: '100%',
                placeholder: "Select a prompt",
                allowClear: true
            });
        });
        
        // Add a change handler for Select2 to ensure native change events are triggered
        $('#ai_setting_id').on('select2:select', function (e) {
            console.log("Select2 selection changed, triggering native change event");
            this.dispatchEvent(new Event('change'));
        });
        
        $('#ai_model').on('select2:select', function (e) {
            console.log("Select2 model selection changed, triggering native change event");
            this.dispatchEvent(new Event('change'));
        });
        
        // Add a mutation observer to initialize Select2 on dynamically created prompt dropdowns
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                    // Check if any of the added nodes are or contain prompt-select elements
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // ELEMENT_NODE
                            const promptSelects = node.classList && node.classList.contains('prompt-select') ?
                                [node] : node.querySelectorAll('.prompt-select');
                            
                            if (promptSelects.length > 0) {
                                promptSelects.forEach(function(select) {
                                    if (!$(select).data('select2')) {
                                        $(select).select2({
                                            width: '100%',
                                            placeholder: "Select a prompt",
                                            allowClear: true
                                        });
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });
        
        // Start observing the prompt containers for changes
        observer.observe(document.getElementById('prompt-containers'), {
            childList: true,
            subtree: true
        });
        // Add form submission validation
        $('form').on('submit', function(e) {
            // If we're on step 1 and AI is checked, validate the inputs
            if ($('#step-1').is(':visible') && $('#use_ai').is(':checked')) {
                // Check if at least one of the three inputs has a value
                const hasRequirementsDocument = $('#requirements_document').get(0).files && $('#requirements_document').get(0).files.length > 0;
                const hasClaapTranscriptFile = $('#claap_transcript_file').get(0).files && $('#claap_transcript_file').get(0).files.length > 0;
                const hasClaapTranscript = $('#claap_transcript').val().trim() !== '';
                
                if (!hasRequirementsDocument && !hasClaapTranscriptFile && !hasClaapTranscript) {
                    // Show the error message
                    $('#ai_validation_error').removeClass('hidden');
                    
                    // Scroll to the error message
                    $('html, body').animate({
                        scrollTop: $('#ai_validation_error').offset().top - 100
                    }, 200);
                    
                    // Prevent form submission
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
</script>
@endpush