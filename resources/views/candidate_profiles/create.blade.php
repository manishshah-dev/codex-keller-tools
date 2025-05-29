<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Create Profile') }}: {{ $candidate->full_name }}
                </h2>
                <p class="text-gray-600 mt-1">
                    <a href="{{ route('projects.candidates.show', [$project, $candidate]) }}" class="text-indigo-600 hover:text-indigo-900">
                        Back to Candidate
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
                        <h3 class="text-lg font-semibold mb-4">Create Candidate Profile</h3>
                        
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">
                                        You can create a profile manually or use AI to generate content based on the candidate's resume and other data.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
                                    @if($candidate->location)
                                    <div>
                                        <span class="text-sm text-gray-500">Location:</span>
                                        <p>{{ $candidate->location }}</p>
                                    </div>
                                    @endif
                                    @if($candidate->email)
                                    <div>
                                        <span class="text-sm text-gray-500">Email:</span>
                                        <p>{{ $candidate->email }}</p>
                                    </div>
                                    @endif
                                    @if($candidate->phone)
                                    <div>
                                        <span class="text-sm text-gray-500">Phone:</span>
                                        <p>{{ $candidate->phone }}</p>
                                    </div>
                                    @endif
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
                                    @if($project->department)
                                    <div>
                                        <span class="text-sm text-gray-500">Department:</span>
                                        <p>{{ $project->department }}</p>
                                    </div>
                                    @endif
                                    @if($project->location)
                                    <div>
                                        <span class="text-sm text-gray-500">Location:</span>
                                        <p>{{ $project->location }}</p>
                                    </div>
                                    @endif
                                    <div>
                                        <span class="text-sm text-gray-500">Requirements:</span>
                                        <p>{{ $project->activeRequirements()->count() }} defined</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Match Info Card -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="font-medium text-gray-700 mb-2">Match Information</h4>
                                <div class="space-y-2">
                                    <div>
                                        <span class="text-sm text-gray-500">Match Score:</span>
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $candidate->match_score_percentage }}"></div>
                                            </div>
                                            <span>{{ $candidate->match_score_percentage }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Status:</span>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $candidate->status_badge_class }}">
                                            {{ ucfirst($candidate->status) }}
                                        </span>
                                    </div>
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
                        </div>

                        <div class="mt-8">
                            <div class="md:grid md:grid-cols-2 md:gap-6">
                                <div class="md:col-span-1">
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900">Manual Creation</h3>
                                        <p class="mt-1 text-sm text-gray-600">
                                            Create a profile manually by entering basic information. You can add headings and content later.
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-5 md:mt-0 md:col-span-1">
                                    <form action="{{ route('projects.candidates.profiles.store', [$project, $candidate]) }}" method="POST">
                                        @csrf
                                        <div class="shadow overflow-hidden sm:rounded-md">
                                            <div class="px-4 py-5 bg-white sm:p-6">
                                                <div class="grid grid-cols-6 gap-6">
                                                    <div class="col-span-6">
                                                        <label for="title" class="block text-sm font-medium text-gray-700">Profile Title</label>
                                                        <input type="text" name="title" id="title" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('title', $candidate->full_name . ' - ' . $project->title) }}" required>
                                                    </div>

                                                    <div class="col-span-6">
                                                        <label for="summary" class="block text-sm font-medium text-gray-700">Summary (Optional)</label>
                                                        <textarea name="summary" id="summary" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('summary') }}</textarea>
                                                        <p class="mt-1 text-sm text-gray-500">A brief summary of the candidate's qualifications and fit for the role.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Create Profile
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:block" aria-hidden="true">
                            <div class="py-5">
                                <div class="border-t border-gray-200"></div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <div class="md:grid md:grid-cols-2 md:gap-6">
                                <div class="md:col-span-1">
                                    <div class="px-4 sm:px-0">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900">AI-Generated Profile</h3>
                                        <p class="mt-1 text-sm text-gray-600">
                                            Use AI to generate a complete profile based on the candidate's resume and job requirements.
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-5 md:mt-0 md:col-span-1">
                                    <form action="{{ route('projects.candidates.profiles.store', [$project, $candidate]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="generate_content" value="1">
                                        <input type="hidden" name="summary" value="">
                                        <div class="shadow overflow-hidden sm:rounded-md">
                                            <div class="px-4 py-5 bg-white sm:p-6">
                                                <div class="grid grid-cols-6 gap-6">
                                                    <div class="col-span-6">
                                                        <label for="ai_title" class="block text-sm font-medium text-gray-700">Profile Title</label>
                                                        <input type="text" name="title" id="ai_title" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('title', $candidate->full_name . ' - ' . $project->title) }}" required>
                                                    </div>

                                                    <div class="col-span-6 sm:col-span-3">
                                                        <label for="ai_setting_id" class="block text-sm font-medium text-gray-700">AI Setting</label>
                                                        <select id="ai_setting_id" name="ai_setting_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                            @foreach($aiSettings as $setting)
                                                                <option value="{{ $setting->id }}">{{ ucfirst($setting->provider) }} ({{ $setting->name }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-span-6 sm:col-span-3">
                                                        <label for="ai_model" class="block text-sm font-medium text-gray-700">AI Model</label>
                                                        <select id="ai_model" name="ai_model" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                            <!-- Will be populated by JavaScript -->
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Create & Generate Content
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Setting models data from PHP
        const settingModels = @json($aiSettings->mapWithKeys(function($setting) {
            return [$setting->id => $setting->models ?? []];
        }));
        
        document.addEventListener('DOMContentLoaded', function() {
            const aiSettingId = document.getElementById('ai_setting_id');
            const aiModel = document.getElementById('ai_model');
            
            // Function to update model dropdown based on selected setting
            function updateModelDropdown() {
                const settingId = aiSettingId.value;
                const models = settingModels[settingId] || [];
                
                // Clear current options
                aiModel.innerHTML = '';
                
                // Add new options
                if (models.length > 0) {
                    models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model;
                        option.textContent = model;
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
            
            // Update models when setting changes
            aiSettingId.addEventListener('change', updateModelDropdown);
            
            // Initialize model dropdown
            updateModelDropdown();
        });
    </script>
</x-app-layout>