<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add AI Provider') }}
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
                    <form action="{{ route('ai-settings.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="provider" :value="__('Provider')" />
                                <select id="provider" name="provider" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach($providers as $key => $value)
                                        <option value="{{ $key }}" {{ old('provider') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('provider')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="name" :value="__('Display Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="api_key" :value="__('API Key')" />
                            <x-text-input id="api_key" class="block mt-1 w-full" type="password" name="api_key" :value="old('api_key')" required />
                            <x-input-error :messages="$errors->get('api_key')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="organization_id" :value="__('Organization ID (Optional)')" />
                            <x-text-input id="organization_id" class="block mt-1 w-full" type="text" name="organization_id" :value="old('organization_id')" />
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
                            <p class="text-sm text-gray-500 mt-1">Select the models this provider supports (fetched from OpenRouter).</p>
                        </div>
                        
                        
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 text-sm text-gray-600">Active</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input id="is_default" type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="is_default" class="ml-2 text-sm text-gray-600">Set as Default Provider</label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Add AI Provider') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modelsSelect = document.getElementById('models'); // Target the select element
        // Get all models and old selections
        // Use $providerModels passed from controller which is the map
        const allModelsMap = {!! json_encode($providerModels ?? []) !!}; 
        const oldModels = {!! json_encode(old('models', [])) !!}; 
        console.log('All Models Map:', allModelsMap); // Log the map
        console.log('Old Models:', oldModels); // Log old selections

        // Function to populate models dropdown based on selected provider
        function populateModelsDropdown(selectedProvider = null) {
            modelsSelect.innerHTML = ''; // Clear existing options
            
            if (!selectedProvider) {
                // No provider selected, show placeholder
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Please select a provider first';
                defaultOption.disabled = true;
                modelsSelect.appendChild(defaultOption);
                $(modelsSelect).select2({ width: '100%', placeholder: 'Please select a provider first' });
                return;
            }
            
            // Get models for the selected provider only
            const providerModels = allModelsMap[selectedProvider] || [];
            
            if (providerModels.length === 0) {
                console.log('No models found for provider:', selectedProvider);
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'No models available for this provider';
                defaultOption.disabled = true;
                modelsSelect.appendChild(defaultOption);
                $(modelsSelect).select2({ width: '100%', placeholder: 'No models available' });
                return;
            }

            // Add models for the selected provider
            providerModels.forEach(model => {
                const isSelected = oldModels.includes(model);
                const option = document.createElement('option');
                option.value = model; // Use just the model name as value
                option.textContent = model; // Display model name
                if (isSelected) {
                    option.selected = true;
                }
                modelsSelect.appendChild(option);
            });
            
            // Initialize or update Select2 after populating
            $(modelsSelect).select2({ width: '100%', placeholder: 'Select models...' });
        }

        // Listen for provider selection changes
        const providerSelect = document.getElementById('provider');
        providerSelect.addEventListener('change', function() {
            const selectedProvider = this.value;
            populateModelsDropdown(selectedProvider);
        });

        // Initial population on page load
        const initialProvider = providerSelect.value;
        populateModelsDropdown(initialProvider);
    });
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#provider').select2({ width: '100%' }); // Removed theme
        // Select2 for models is initialized within populateModelsDropdown()
    });
</script>
@endpush