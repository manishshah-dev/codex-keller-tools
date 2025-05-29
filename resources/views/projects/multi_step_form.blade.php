<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ isset($project) ? __('Edit Project') : __('Create New Project') }}
        </h2>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form id="multi-step-form" method="POST" action="{{ isset($project) ? route('projects.update', $project) : route('projects.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if(isset($project))
                            @method('PUT')
                        @endif

                        <!-- Step Navigation -->
                        @include('projects.steps.navigation')

                        <!-- Step 1: Basic Information -->
                        @include('projects.steps.step1')

                        <!-- Step 2: Intake Form -->
                        @include('projects.steps.step2')

                        <!-- Step 3: Company Research -->
                        @include('projects.steps.step3')

                        <!-- Step 4: Job Description -->
                        @include('projects.steps.step4')

                        <!-- Step 5: Additional Info -->
                        @include('projects.steps.step5')

                        <!-- Navigation Buttons -->
                        @include('projects.steps.buttons')
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize multi-step form functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Any multi-step form specific JavaScript can go here
        });
    </script>
    @endpush
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const settingSelect = document.getElementById('ai_setting_id');
        const modelSelect = document.getElementById('ai_model');
        const providerModelsMap = {!! json_encode($providerModels ?? []) !!};
        const oldAiSettingId = '{{ old('ai_setting_id', $project->ai_setting_id ?? '') }}';
        const oldAiModel = '{{ old('ai_model', $project->ai_model ?? '') }}';

        function updateModelsDropdown() {
            const selectedOption = settingSelect.options[settingSelect.selectedIndex];
            
            // Ensure we have a selected option with a value before proceeding
            if (!selectedOption || !selectedOption.value) {
                modelSelect.innerHTML = '<option value="">Select AI Setting first</option>';
                modelSelect.disabled = true;
                return;
            }
            
            const selectedProviderType = selectedOption.dataset.providerType;
            
            // Get the list of models enabled in this specific setting
            let enabledModelsInSetting = [];
            try {
                enabledModelsInSetting = JSON.parse(selectedOption.dataset.models || '[]');
                if (!Array.isArray(enabledModelsInSetting)) enabledModelsInSetting = [];
            } catch (e) {
                console.error("Error parsing enabled models data attribute for setting ID " + selectedOption.value + ":", e);
                enabledModelsInSetting = [];
            }
            
            // Get all models available for this provider type
            const allModelsForProvider = providerModelsMap[selectedProviderType] || [];
            
            // Clear existing options
            modelSelect.innerHTML = '';
            
            if (allModelsForProvider.length === 0) {
                // Add a placeholder if no models are defined
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'No models listed for this provider type';
                modelSelect.appendChild(defaultOption);
                modelSelect.disabled = true;
            } else {
                // Add default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Select Model';
                modelSelect.appendChild(defaultOption);
                
                // Check if the old selected model is valid for this setting
                let modelToSelect = null;
                if (oldAiModel && enabledModelsInSetting.includes(oldAiModel)) {
                    // Prioritize old value if it's valid for this setting
                    modelToSelect = oldAiModel;
                } else if (enabledModelsInSetting.length > 0) {
                    // Otherwise, select the first model enabled in this setting
                    modelToSelect = enabledModelsInSetting[0];
                }
                
                // Add models
                allModelsForProvider.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model;
                    
                    // Pre-select the determined model
                    if (modelToSelect && model === modelToSelect) {
                        option.selected = true;
                    }
                    
                    modelSelect.appendChild(option);
                });
                
                modelSelect.disabled = false;
                
                // Re-initialize Select2 if available
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $(modelSelect).select2({ width: '100%' });
                }
            }
        }
        
        // Add event listener if provider select exists
        if (settingSelect) {
            settingSelect.addEventListener('change', updateModelsDropdown);
            
            // Initial population on page load
            if (oldAiSettingId && settingSelect.value === oldAiSettingId) {
                settingSelect.dispatchEvent(new Event('change'));
            } else {
                updateModelsDropdown();
            }
        }
    });
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 on relevant dropdowns
        $('#ai_setting_id').select2({ width: '100%' });
        // Note: ai_model is initialized dynamically after options load
    });
</script>
@endpush
