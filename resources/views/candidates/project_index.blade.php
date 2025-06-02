<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Candidates for') }}: {{ $project->title }}
                </h2>
                <p class="text-gray-600 mt-1">
                    <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                        Back to Project
                    </a>
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('projects.analyzer', $project) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                    CV Analyzer
                </a>
                <a href="{{ route('projects.candidates.create', $project) }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    Add Candidate
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- AI Analysis Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">CV Analysis</h3>
                    <p class="mb-4 text-sm text-gray-600">Select an AI setting and model to analyze all candidates for this project based on the defined requirements.</p>
                    
                    <form action="{{ route('projects.candidates.analyzeAll', $project) }}" method="POST" class="space-y-4" id="analyze-all-form"> 
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="analyze_ai_setting_id" :value="__('AI Setting')" />
                                <select id="analyze_ai_setting_id" name="ai_setting_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @if(count($aiSettings) > 0)
                                        <option value="">Select AI Setting</option>
                                        @foreach($aiSettings as $setting)
                                            <option value="{{ $setting->id }}"
                                                    data-provider-type="{{ $setting->provider }}"
                                                    data-models='@json($setting->models ?? [])'
                                                    {{ old('ai_setting_id') == $setting->id ? 'selected' : '' }}>
                                                {{ $setting->name }} ({{ ucfirst($setting->provider) }})
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="">No CV Analyzer settings found</option>
                                    @endif
                                </select>
                                <x-input-error :messages="$errors->get('ai_setting_id')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="analyze_ai_model" :value="__('AI Model')" />
                                <select id="analyze_ai_model" name="ai_model" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required disabled>
                                    <option value="">Select AI Setting first</option>
                                    {{-- Models populated by JS --}}
                                </select>
                                <x-input-error :messages="$errors->get('ai_model')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="analyze_ai_prompt_id" :value="__('AI Prompt (Optional)')" />
                                <select id="analyze_ai_prompt_id" name="ai_prompt_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" disabled>
                                    <option value="">Use Default/Generic Prompt</option>
                                     {{-- Prompts populated by JS --}}
                                </select>
                                <x-input-error :messages="$errors->get('ai_prompt_id')" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button type="submit">
                                {{ __('Analyze All Candidates') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- End AI Analysis Section --}}

            <!-- Import Tools -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Import Candidates</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Workable Import -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium mb-2">Import from Workable</h4>
                            <p class="text-sm text-gray-600 mb-4">Import candidates directly from your Workable account.</p>

                            <form method="GET" action="{{ route('projects.candidates.index', $project) }}" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="workable_department" :value="__('Department')" />
                                        <select id="workable_department" name="department" class="select2 block mt-1 w-full">
                                            <option value="">{{ __('Select Department') }}</option>
                                            @php
                                                $departments = collect($workableJobs ?? [])->pluck('department')->filter()->unique();
                                            @endphp
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="workable_job" :value="__('Job')" />
                                        <select id="workable_job" name="job" class="select2 block mt-1 w-full">
                                            <option value="">{{ __('Select Job') }}</option>
                                            {{-- Options populated by JS based on department --}}
                                        </select>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-center space-x-2">
                                        <input type="checkbox" id="filter_email" name="filter_email" value="1" @checked(request('filter_email'))>
                                        <x-input-label for="filter_email" value="Email" />
                                        <input id="filter_email_value" type="email" name="email" value="{{ request('email') }}" class="block w-full border-gray-300 rounded-md" />
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input type="checkbox" id="filter_created_after" name="filter_created_after" value="1" @checked(request('filter_created_after'))>
                                        <x-input-label for="filter_created_after" value="{{ __('Created After') }}" />
                                        <input id="filter_created_after_value" type="datetime-local" name="created_after" value="{{ request('created_after') }}" class="block w-full border-gray-300 rounded-md" />
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <x-primary-button type="submit">{{ __('Load Candidates') }}</x-primary-button>
                                </div>
                            </form>

                            <form action="{{ route('projects.candidates.import-workable', $project) }}" method="POST" class="space-y-4 mt-6">
                                @csrf
                                <div>
                                    <x-input-label for="workable_candidates" :value="__('Select Candidates')" />
                                    <select id="workable_candidates" name="workable_candidates[]" multiple class="select2 block mt-1 w-full">
                                        @foreach($workableCandidates ?? [] as $candidate)
                                            @php
                                                $city = $candidate['address'] ?? null;
                                                $country = $candidate['country'] ?? null;
                                                $job = $candidate['job']['title'] ?? 'Unknown Job';
                                            @endphp
                                            <option value="{{ $candidate['id'] }}">
                                                {{ $candidate['name'] }} - {{ $job }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('workable_candidates')" class="mt-2" />
                                </div>
                                <div class="flex justify-end">
                                    <x-primary-button>{{ __('Import Candidates') }}</x-primary-button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Batch Upload -->
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium mb-2">Batch Upload Resumes</h4>
                            <p class="text-sm text-gray-600 mb-4">Upload multiple resumes at once.</p>
                            
                            <form action="{{ route('projects.candidates.batch-upload', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <x-input-label for="resumes" :value="__('Resumes (PDF, DOC, DOCX)')" />
                                    <input id="resumes" type="file" name="resumes[]" multiple accept=".pdf,.doc,.docx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required />
                                    <x-input-error :messages="$errors->get('resumes')" class="mt-2" />
                                </div>
                                
                                <div class="flex justify-end">
                                    <x-primary-button>
                                        {{ __('Upload Resumes') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requirements Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Current Requirements</h3>
                        <a href="{{ route('projects.analyzer', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            Manage Requirements
                        </a>
                    </div>
                    
                    @if(count($requirements) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($requirements as $requirement)
                                <div class="px-3 py-1 rounded-full text-sm font-medium {{ $requirement->type_badge_class }}">
                                    {{ $requirement->name }}
                                    @if($requirement->is_required)
                                        <span class="ml-1 text-xs bg-red-100 text-red-800 px-1 rounded">Required</span>
                                    @endif
                                    <span class="ml-1 text-xs">{{ $requirement->weight_percentage }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 p-4 rounded text-center">
                            <p>No requirements defined. Use the CV Analyzer to add requirements.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Candidates List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">                        
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold">Candidates ({{ $candidates->total() }})</h3>
                        </div>

                        @if($candidates->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-2 px-4 border-b text-left">Name</th>
                                            <th class="py-2 px-4 border-b text-left">Current Position</th>
                                            <th class="py-2 px-4 border-b text-left">Location</th>
                                            <th class="py-2 px-4 border-b text-left">Match Score</th>
                                            <th class="py-2 px-4 border-b text-left">Status</th>
                                            <th class="py-2 px-4 border-b text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($candidates as $candidate)
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-2 px-4 border-b">
                                                    <a href="{{ route('projects.candidates.show', [$candidate->project, $candidate]) }}" class="text-indigo-600 hover:text-indigo-900">
                                                        {{ $candidate->full_name }}
                                                    </a>
                                                </td>
                                            <td class="py-2 px-4 border-b">{{ $candidate->current_position ?? 'N/A' }}</td>
                                            <td class="py-2 px-4 border-b">{{ $candidate->location ?? 'N/A' }}</td>
                                            <td class="py-2 px-4 border-b">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $candidate->match_score_percentage }}"></div>
                                                </div>
                                                <span class="text-xs">{{ $candidate->match_score_percentage }}</span>
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $candidate->status_badge_class }}">
                                                    {{ ucfirst($candidate->status) }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('projects.candidates.show', [$candidate->project, $candidate]) }}" class="text-blue-500 hover:text-blue-700"> {{-- Use shallow route --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </a>
                                                    <a href="{{ route('projects.candidates.edit', [$candidate->project, $candidate]) }}" class="text-yellow-500 hover:text-yellow-700"> {{-- Use shallow route --}}
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        </svg>
                                                    </a>
                                                    <form action="{{ route('projects.candidates.destroy', [$candidate->project, $candidate]) }}" method="POST" class="inline"> {{-- Use shallow route --}}
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this candidate?')">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $candidates->links() }}
                        </div>
                    @else
                        <div class="bg-gray-50 p-4 rounded text-center">
                            <p>No candidates found. Add candidates using the options above.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div> {{-- Close py-12 --}}

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {        
        // AI Settings
        const settingSelect = document.getElementById('analyze_ai_setting_id');
        const modelSelect = document.getElementById('analyze_ai_model');
        const promptSelect = document.getElementById('analyze_ai_prompt_id');
        const allPrompts = @json($prompts ?? []); // Get all CV Analyzer prompts
        const providerModelsMap = @json($providerModels ?? []); // Get the full model map
        // Note: No 'old()' values needed here as it's not a form submission with validation errors typically

        function updateModelsDropdown() {
            const selectedOption = settingSelect.options[settingSelect.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                modelSelect.innerHTML = '<option value="">Select AI Setting first</option>';
                $(modelSelect).prop('disabled', true).trigger('change');
                updatePromptsDropdown();
                return;
            }
            const selectedProviderType = selectedOption.dataset.providerType;
            let enabledModelsInSetting = [];
            try {
                 const modelsData = selectedOption.dataset.models || '[]';
                 enabledModelsInSetting = JSON.parse(modelsData);
                 if (!Array.isArray(enabledModelsInSetting)) enabledModelsInSetting = [];
            } catch (e) {
                 enabledModelsInSetting = [];
            }
            const allModelsForProvider = providerModelsMap[selectedProviderType] || [];

            modelSelect.innerHTML = ''; // Clear existing options
            
            let hasValidModels = false;
            if (allModelsForProvider.length > 0 && enabledModelsInSetting.length > 0) {
                 enabledModelsInSetting.forEach(model => {
                     if (allModelsForProvider.includes(model)) {
                         const option = document.createElement('option');
                         option.value = model;
                         option.textContent = model;
                         modelSelect.appendChild(option);
                         hasValidModels = true;
                     }
                 });
            }
            if (!hasValidModels) {
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'No enabled models found';
                modelSelect.appendChild(defaultOption);
                 $(modelSelect).prop('disabled', true); // Disable first
            } else {
                 // Use jQuery prop to enable
                 $(modelSelect).prop('disabled', false);
                 // Select the first model by default
                 if (modelSelect.options.length > 0) {
                    modelSelect.selectedIndex = 0;
                 }
            }
            // Trigger change after potential state change inside the if/else block
            $(modelSelect).trigger('change');
            updatePromptsDropdown(); // Update prompts when models change
        }

        function updatePromptsDropdown() {
            const selectedOption = settingSelect.options[settingSelect.selectedIndex];
            const selectedProviderType = selectedOption ? selectedOption.dataset.providerType : null;
            const selectedModel = modelSelect.value;
            
            const filteredPrompts = allPrompts.filter(prompt =>
                (!prompt.provider || prompt.provider === selectedProviderType) && // Fixed &&
                (!prompt.model || prompt.model === selectedModel)
            );

            promptSelect.innerHTML = '<option value="">Use Default/Generic Prompt</option>';

            if (filteredPrompts.length > 0) {
                filteredPrompts.forEach(prompt => {
                    const option = document.createElement('option');
                    option.value = prompt.id;
                    option.textContent = prompt.name + (prompt.is_default ? ' (Default)' : '');
                    // Select default prompt if available
                    if (prompt.is_default) {
                         option.selected = true;
                    }
                    promptSelect.appendChild(option);
                });
                 $(promptSelect).prop('disabled', $(modelSelect).prop('disabled')).trigger('change');
            } else {
                 $(promptSelect).prop('disabled', $(modelSelect).prop('disabled')).trigger('change');
            }
             // No need to re-init Select2 here
        }

        if (settingSelect) {
            // Use jQuery's .on() for Select2 compatibility
            $('#analyze_ai_setting_id').on('change', updateModelsDropdown);
            // Use jQuery's .on() for Select2 compatibility
            $('#analyze_ai_model').on('change', updatePromptsDropdown);

            // Initial population
            updateModelsDropdown();
        } else {
             // Handle case where no settings are available
             modelSelect.innerHTML = '<option value="">No AI settings configured</option>';
             $(modelSelect).prop('disabled', true);
             promptSelect.innerHTML = '<option value="">No AI settings configured</option>';
             $(promptSelect).prop('disabled', true);
        }

        // Initialize all Select2 instances here, after potential initial population
        $('#analyze_ai_setting_id').select2({ width: '100%' });
        $('#analyze_ai_model').select2({ width: '100%' });
        $('#analyze_ai_prompt_id').select2({ width: '100%' });
        $('#workable_department').select2({ width: '100%' });
        $('#workable_job').select2({ width: '100%' });
        $('#workable_candidates').select2({ width: '100%' });

        const jobs = @json($workableJobs ?? []);
        function updateJobsDropdown() {
            const dept = $('#workable_department').val();
            const jobSelect = $('#workable_job');
            jobSelect.empty();
            jobSelect.append(new Option('Select Job', ''));
            jobs.filter(j => !dept || j.department === dept).forEach(j => {
                const city = j.location?.city ? j.location.city + ',' : '';
                const country = j.location?.country ?? '';
                const text = `${j.title} (${city} ${country})`;
                const option = new Option(text.trim(), j.shortcode, false, j.shortcode === '{{ request('job') }}');
                jobSelect.append(option);
            });
            jobSelect.trigger('change');
        }

        $('#workable_department').on('change', updateJobsDropdown);
        updateJobsDropdown();

        function toggleFilters() {
            $('#filter_email_value').prop('disabled', !$('#filter_email').prop('checked'));
            $('#filter_created_after_value').prop('disabled', !$('#filter_created_after').prop('checked'));
        }

        $('#filter_email').on('change', toggleFilters);
        $('#filter_created_after').on('change', toggleFilters);
        toggleFilters();

    });
</script>
@endpush
</x-app-layout>