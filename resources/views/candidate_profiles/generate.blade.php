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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                        Use AI to generate content for your profile. You can generate a full profile, just the headings, or content for existing headings.
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
                                                @foreach($aiSettings as $setting)
                                                    <option value="{{ $setting->id }}" {{ $profile->ai_provider == $setting->provider ? 'selected' : '' }}>
                                                        {{ ucfirst($setting->provider) }} ({{ $setting->name }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="sm:col-span-3">
                                            <label for="ai_model" class="block text-sm font-medium text-gray-700">AI Model</label>
                                            <select id="ai_model" name="ai_model" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <!-- Will be populated by JavaScript -->
                                            </select>
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
                                            
                                            <div class="relative flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="generation_type_headings" name="generation_type" type="radio" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" value="headings">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="generation_type_headings" class="font-medium text-gray-700">Generate Headings Only</label>
                                                    <p class="text-gray-500">Generate only the headings structure. This will replace any existing headings but keep the summary.</p>
                                                </div>
                                            </div>
                                            
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

                                <!-- Custom Headings (shown only when "Generate Headings Only" is selected) -->
                                <div id="custom_headings_section" class="hidden">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Custom Headings</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Optionally specify custom headings instead of using AI-generated ones.
                                    </p>
                                    <div class="mt-4">
                                        <div id="custom_headings_container" class="space-y-2">
                                            @if(count($customHeadings) > 0)
                                                <div class="text-sm text-gray-700 mb-2">Select from existing heading templates:</div>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                    @foreach($customHeadings as $heading)
                                                        <div class="flex items-center">
                                                            <input type="checkbox" id="heading_{{ $heading->id }}" name="custom_headings[]" value="{{ $heading->name }}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                            <label for="heading_{{ $heading->id }}" class="ml-2 block text-sm text-gray-900">
                                                                {{ $heading->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="border-t border-gray-200 my-4"></div>
                                            @endif
                                            
                                            <div class="text-sm text-gray-700 mb-2">Or add custom headings:</div>
                                            <div id="custom_heading_inputs" class="space-y-2">
                                                <div class="flex items-center space-x-2">
                                                    <input type="text" name="custom_headings[]" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Enter custom heading">
                                                    <button type="button" class="remove-heading-btn text-red-600 hover:text-red-800 hidden">
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="button" id="add_heading_btn" class="mt-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <svg class="-ml-0.5 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                                </svg>
                                                Add Heading
                                            </button>
                                        </div>
                                    </div>
                                </div>

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
        
        document.addEventListener('DOMContentLoaded', function() {
            const aiSettingId = document.getElementById('ai_setting_id');
            const aiModel = document.getElementById('ai_model');
            const generationTypeHeadings = document.getElementById('generation_type_headings');
            const customHeadingsSection = document.getElementById('custom_headings_section');
            const addHeadingBtn = document.getElementById('add_heading_btn');
            const customHeadingInputs = document.getElementById('custom_heading_inputs');
            
            // Function to update model dropdown based on selected setting
            function updateModelDropdown() {
                const settingId = aiSettingId.value;
                const models = settingModels[settingId] || [];
                const currentModel = "{{ $profile->ai_model }}";
                
                // Clear current options
                aiModel.innerHTML = '';
                
                // Add new options
                if (models.length > 0) {
                    models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model;
                        option.textContent = model;
                        if (model === currentModel) {
                            option.selected = true;
                        }
                        aiModel.appendChild(option);
                    });
                } else {
                    // Add a default option if no models are available
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No models available';
                    aiModel.appendChild(option);
                }
            }
            
            // Function to toggle custom headings section
            function toggleCustomHeadingsSection() {
                if (generationTypeHeadings.checked) {
                    customHeadingsSection.classList.remove('hidden');
                } else {
                    customHeadingsSection.classList.add('hidden');
                }
            }
            
            // Function to add a new custom heading input
            function addCustomHeadingInput() {
                const headingInputs = customHeadingInputs.querySelectorAll('input[type="text"]');
                const lastInput = headingInputs[headingInputs.length - 1];
                
                // Create a new input row
                const newInputRow = document.createElement('div');
                newInputRow.className = 'flex items-center space-x-2';
                newInputRow.innerHTML = `
                    <input type="text" name="custom_headings[]" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Enter custom heading">
                    <button type="button" class="remove-heading-btn text-red-600 hover:text-red-800">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                `;
                
                customHeadingInputs.appendChild(newInputRow);
                
                // Show remove button for the first input if we now have multiple inputs
                if (headingInputs.length === 1) {
                    const firstRemoveBtn = customHeadingInputs.querySelector('.remove-heading-btn');
                    firstRemoveBtn.classList.remove('hidden');
                }
                
                // Add event listener to the new remove button
                const newRemoveBtn = newInputRow.querySelector('.remove-heading-btn');
                newRemoveBtn.addEventListener('click', function() {
                    newInputRow.remove();
                    
                    // Hide remove button for the first input if we now have only one input
                    const remainingInputs = customHeadingInputs.querySelectorAll('input[type="text"]');
                    if (remainingInputs.length === 1) {
                        const firstRemoveBtn = customHeadingInputs.querySelector('.remove-heading-btn');
                        firstRemoveBtn.classList.add('hidden');
                    }
                });
            }
            
            // Update models when setting changes
            aiSettingId.addEventListener('change', updateModelDropdown);
            
            // Toggle custom headings section when generation type changes
            document.querySelectorAll('input[name="generation_type"]').forEach(function(radio) {
                radio.addEventListener('change', toggleCustomHeadingsSection);
            });
            
            // Add custom heading input when button is clicked
            addHeadingBtn.addEventListener('click', addCustomHeadingInput);
            
            // Add event listener to existing remove buttons
            document.querySelectorAll('.remove-heading-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    btn.closest('.flex').remove();
                    
                    // Hide remove button for the first input if we now have only one input
                    const remainingInputs = customHeadingInputs.querySelectorAll('input[type="text"]');
                    if (remainingInputs.length === 1) {
                        const firstRemoveBtn = customHeadingInputs.querySelector('.remove-heading-btn');
                        firstRemoveBtn.classList.add('hidden');
                    }
                });
            });
            
            // Initialize model dropdown
            updateModelDropdown();
            
            // Initialize custom headings section
            toggleCustomHeadingsSection();
        });
    </script>
</x-app-layout>