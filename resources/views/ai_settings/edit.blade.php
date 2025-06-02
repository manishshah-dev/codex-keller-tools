<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit AI Provider') }}: {{ $aiSetting->name }}
            </h2>
            <div>
                <a href="{{ route('ai-settings.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to AI Settings
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('ai-settings.update', $aiSetting) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="provider" :value="__('Provider')" />
                                <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm">
                                    {{ $providers[$aiSetting->provider] ?? $aiSetting->provider }}
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Provider cannot be changed after creation</p>
                            </div>
                            
                            <div>
                                <x-input-label for="name" :value="__('Display Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $aiSetting->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="api_key" :value="__('API Key')" />
                            <x-text-input id="api_key" class="block mt-1 w-full" type="password" name="api_key" :value="old('api_key', $aiSetting->api_key)" required />
                            <x-input-error :messages="$errors->get('api_key')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="organization_id" :value="__('Organization ID (Optional)')" />
                            <x-text-input id="organization_id" class="block mt-1 w-full" type="text" name="organization_id" :value="old('organization_id', $aiSetting->organization_id)" />
                            <x-input-error :messages="$errors->get('organization_id')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Required for some providers like OpenAI</p>
                        </div>
                        
                        <div>
                            <x-input-label for="models" :value="__('Available Models')" />
                            {{-- Changed from div of checkboxes to a multi-select dropdown --}}
                            <select id="models" name="models[]" multiple="multiple" class="select2 block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                {{-- Options will be populated by JavaScript --}}
                            </select>
                            <x-input-error :messages="$errors->get('models')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Select the models enabled for this setting (fetched from OpenRouter).</p>
                        </div>
                        
                        
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input id="is_active" type="checkbox" name="is_active" value="1" {{ $aiSetting->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 text-sm text-gray-600">Active</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input id="is_default" type="checkbox" name="is_default" value="1" {{ $aiSetting->is_default ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="is_default" class="ml-2 text-sm text-gray-600">Set as Default Provider</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Update AI Provider') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Hidden div to store PHP data for JavaScript --}}
<div id="ai-settings-data"
     data-selected-models="{{ json_encode(old('models', $aiSetting->models ?? [])) }}"
     style="display: none;">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modelsSelect = document.getElementById('models'); // Target the select element
        const settingsDataElement = document.getElementById('ai-settings-data');
        const currentProvider = '{{ $aiSetting->provider }}'; // Get the current provider type
        const allModelsMap = {!! json_encode($providerModels ?? []) !!}; // Use correct variable name from controller

        // Read selected models data from attribute and parse JSON safely
        let selectedModels = []; // Models enabled in this specific setting
        try {
            selectedModels = JSON.parse(settingsDataElement.dataset.selectedModels || '[]');
            if (!Array.isArray(selectedModels)) selectedModels = [];
        } catch (e) {
            console.error("Error parsing selectedModels data attribute:", e);
            selectedModels = [];
        }

        // Function to populate models dropdown
        function populateModelsDropdown() { // Renamed function definition
            const modelsForProvider = allModelsMap[currentProvider] || []; // Get all models for this setting's provider type
            
            modelsSelect.innerHTML = ''; // Clear existing options
            
            if (modelsForProvider.length === 0) {
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'No models found for this provider type';
                defaultOption.disabled = true;
                modelsSelect.appendChild(defaultOption);
                // Initialize Select2 even if empty
                $(modelsSelect).select2({ width: '100%', placeholder: 'No models found' }); // Removed theme
                return;
            }

            modelsForProvider.forEach(model => { // Iterate over all models for this provider type
                const isSelected = selectedModels.includes(model); // Check against models enabled in this specific setting
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model; // Display model name as-is
                if (isSelected) {
                    option.selected = true; // Pre-select if enabled in this setting
                }
                modelsSelect.appendChild(option);
            });

            // Initialize or update Select2 after populating
            $(modelsSelect).select2({ width: '100%', placeholder: 'Select enabled models...' }); // Removed theme
        }
        
        // Initial population on page load
        populateModelsDropdown(); // Call renamed function
    });
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        // Provider dropdown is disabled, but initialize models dropdown
        // Select2 for models is initialized within populateModelsDropdown()
    });
</script>
@endpush