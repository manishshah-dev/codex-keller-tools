<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Job Description
            </h2>
            <div>
                <a href="{{ route('job-descriptions.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Job Descriptions
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <x-container> {{-- Use the container component --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Removed mb-6 and wrapper div from AI section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Start a single container for both sections --}}
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">AI-Powered Job Description Generation</h3>
                        <p class="mb-4">Generate a complete job description using AI based on the project information.</p>
                        
                        <form action="{{ route('job-descriptions.generate') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <x-input-label for="project_id" :value="__('Project')" />
                                <select id="project_id" name="project_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="ai_setting_id" :value="__('AI Setting')" />
                                    <select id="ai_setting_id" name="ai_setting_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        @if(count($aiProviders) > 0)
                                            <option value="">Select AI Setting</option>
                                            @foreach($aiProviders as $setting)
                                                {{-- Store models and provider type in data attributes --}}
                                                {{-- Removed duplicate option tag --}}
                                                <option value="{{ $setting->id }}"
                                                        data-provider-type="{{ $setting->provider }}"
                                                        data-models="{{ json_encode($setting->models ?? []) }}"
                                                        {{ old('ai_setting_id') == $setting->id ? 'selected' : '' }}> {{-- Add selected logic --}}
                                                    {{ $setting->name }} ({{ ucfirst($setting->provider) }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">No AI providers configured</option>
                                        @endif
                                    </select>
                                    
                                    @if(count($aiProviders) == 0)
                                    <p class="text-sm text-red-600 mt-1">
                                        No AI providers with job description capability found.
                                        <a href="{{ route('ai-settings.create') }}" class="text-indigo-600 hover:text-indigo-900">Add an AI provider</a>
                                    </p>
                                    @endif
                                    
                                    <x-input-error :messages="$errors->get('ai_setting_id')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="ai_model" :value="__('AI Model')" />
                                    {{-- Note: JavaScript will handle populating and selecting the old value for ai_model --}}
                                    <select id="ai_model" name="ai_model" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" disabled>
                                        <option value="">Select AI Setting first</option>
                                        <!-- Models will be populated by JavaScript -->
                                    </select>
                                    <x-input-error :messages="$errors->get('ai_model')" class="mt-2" />
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label for="ai_prompt_id" :value="__('AI Prompt (Optional)')" />
                                {{-- Note: JavaScript will handle populating and selecting the old value for ai_prompt_id --}}
                                <select id="ai_prompt_id" name="ai_prompt_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" disabled>
                                    <option value="">Select AI Setting first</option>
                                    {{-- Options populated by JavaScript --}}
                                </select>
                                <x-input-error :messages="$errors->get('ai_prompt_id')" class="mt-2" />
                                <p class="text-sm text-gray-500 mt-1">Select a pre-defined prompt from AI Settings, or leave blank to use the default.</p>
                            </div>
                            
                            <div class="flex items-center justify-end mt-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" @if(count($aiProviders) == 0) disabled @endif>
                                    {{ __('Generate Job Description') }}
                                </button>
                                
                                @if(count($aiProviders) == 0)
                                <span class="ml-3 text-sm text-gray-600">
                                    Add an AI provider to enable generation
                                </span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mt-10 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 ">
                        <h3 class="text-lg font-semibold mb-4">Manual Job Description Creation</h3>
                        
                        <form action="{{ route('job-descriptions.store') }}" method="POST" class="space-y-4">
                            @csrf
                            
                            <div>
                                <x-input-label for="project_id" :value="__('Project')" />
                                <select id="project_id" name="project_id" class="manual_project_id block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('project_id')" class="mt-2" />
                            </div>

                            {{-- Add Template Selector --}}
                            <div>
                                <x-input-label for="template_id" :value="__('Use Template (Optional)')" />
                                <select id="template_id" name="template_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Start from Scratch</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('template_id')" class="mt-2" />
                                <p class="text-sm text-gray-500 mt-1">Selecting a template will pre-fill the fields below.</p>
                            </div>
                            
                            <div>
                                <x-input-label for="title" :value="__('Job Title')" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="industry" :value="__('Industry')" />
                                    <x-text-input id="industry" class="block mt-1 w-full" type="text" name="industry" :value="old('industry')" />
                                    <x-input-error :messages="$errors->get('industry')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="experience_level" :value="__('Experience Level')" />
                                    <select id="experience_level" name="experience_level" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Experience Level</option>
                                        <option value="entry" {{ old('experience_level') === 'entry' ? 'selected' : '' }}>Entry Level</option>
                                        <option value="mid" {{ old('experience_level') === 'mid' ? 'selected' : '' }}>Mid Level</option>
                                        <option value="senior" {{ old('experience_level') === 'senior' ? 'selected' : '' }}>Senior Level</option>
                                        <option value="executive" {{ old('experience_level') === 'executive' ? 'selected' : '' }}>Executive</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="employment_type" :value="__('Employment Type')" />
                                    <select id="employment_type" name="employment_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">Select Employment Type</option>
                                        <option value="full-time" {{ old('employment_type') === 'full-time' ? 'selected' : '' }}>Full-time</option>
                                        <option value="part-time" {{ old('employment_type') === 'part-time' ? 'selected' : '' }}>Part-time</option>
                                        <option value="contract" {{ old('employment_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                                        <option value="temporary" {{ old('employment_type') === 'temporary' ? 'selected' : '' }}>Temporary</option>
                                        <option value="internship" {{ old('employment_type') === 'internship' ? 'selected' : '' }}>Internship</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('employment_type')" class="mt-2" />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="location" :value="__('Location')" />
                                    <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location')" />
                                    <x-input-error :messages="$errors->get('location')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="compensation_range" :value="__('Compensation Range')" />
                                    <x-text-input id="compensation_range" class="block mt-1 w-full" type="text" name="compensation_range" :value="old('compensation_range')" />
                                    <x-input-error :messages="$errors->get('compensation_range')" class="mt-2" />
                                </div>
                            </div>
                            
                            <div>
                                <x-input-label for="overview" :value="__('Overview')" />
                                <textarea id="overview" name="overview" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('overview') }}</textarea>
                                <x-input-error :messages="$errors->get('overview')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="responsibilities" :value="__('Responsibilities')" />
                                <textarea id="responsibilities" name="responsibilities" rows="6" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('responsibilities') }}</textarea>
                                <x-input-error :messages="$errors->get('responsibilities')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="requirements_non_negotiable" :value="__('Requirements (Non-Negotiable)')" />
                                <textarea id="requirements_non_negotiable" name="requirements_non_negotiable" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('requirements_non_negotiable') }}</textarea>
                                <x-input-error :messages="$errors->get('requirements_non_negotiable')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="requirements_preferred" :value="__('Requirements (Preferred)')" />
                                <textarea id="requirements_preferred" name="requirements_preferred" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('requirements_preferred') }}</textarea>
                                <x-input-error :messages="$errors->get('requirements_preferred')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="benefits" :value="__('Benefits')" />
                                <textarea id="benefits" name="benefits" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('benefits') }}</textarea>
                                <x-input-error :messages="$errors->get('benefits')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="disclaimer" :value="__('Disclaimer')" />
                                <textarea id="disclaimer" name="disclaimer" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('disclaimer') }}</textarea>
                                <x-input-error :messages="$errors->get('disclaimer')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="review" {{ old('status') === 'review' ? 'selected' : '' }}>In Review</option>
                                    <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                            
                            <!-- Qualifying Questions Section -->
                            <div class="border-t border-gray-200 pt-6 mt-6">
                                <h4 class="text-lg font-semibold mb-4">Qualifying Questions</h4>
                                <p class="mb-4">Add screening questions to help identify suitable candidates.</p>
                                
                                <div id="qualifying-questions-container">
                                    <!-- Initial question template -->
                                    <div class="question-item border border-gray-200 rounded-lg p-4 mb-4">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                            <div class="md:col-span-2">
                                                <x-input-label for="questions[0][question]" :value="__('Question')" />
                                                <x-text-input id="questions[0][question]" class="block mt-1 w-full" type="text" name="questions[0][question]" />
                                            </div>
                                            <div>
                                                <x-input-label for="questions[0][type]" :value="__('Question Type')" />
                                                <select id="questions[0][type]" name="questions[0][type]" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm question-type">
                                                    <option value="multiple_choice">Multiple Choice</option>
                                                    <option value="yes_no">Yes/No</option>
                                                    <option value="text">Text</option>
                                                    <option value="numeric">Numeric</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <x-input-label for="questions[0][description]" :value="__('Description (Optional)')" />
                                            <x-text-input id="questions[0][description]" class="block mt-1 w-full" type="text" name="questions[0][description]" placeholder="Additional context or instructions for the question" />
                                        </div>
                                        
                                        <div class="options-container mb-4">
                                            <x-input-label :value="__('Options (for Multiple Choice)')" />
                                            <div class="option-list space-y-2">
                                                <div class="flex items-center">
                                                    <x-text-input class="block mt-1 w-full" type="text" name="questions[0][options][]" placeholder="Option 1" />
                                                    <button type="button" class="ml-2 text-red-500 remove-option" title="Remove Option">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="button" class="mt-2 text-sm text-indigo-600 add-option">+ Add Option</button>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <div class="flex items-center">
                                                    <input id="questions[0][required]" name="questions[0][required]" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                                                    <label for="questions[0][required]" class="ml-2 block text-sm text-gray-900">Required Question</label>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex items-center">
                                                    <input id="questions[0][is_knockout]" name="questions[0][is_knockout]" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 knockout-checkbox">
                                                    <label for="questions[0][is_knockout]" class="ml-2 block text-sm text-gray-900">Knockout Question</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="correct-answer-container mt-4 hidden">
                                            <x-input-label for="questions[0][correct_answer]" :value="__('Correct Answer (for Knockout Questions)')" />
                                            <x-text-input id="questions[0][correct_answer]" class="block mt-1 w-full" type="text" name="questions[0][correct_answer]" />
                                        </div>
                                        
                                        <div class="flex justify-end mt-4">
                                            <button type="button" class="text-red-500 remove-question">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                Remove Question
                                            </button>
                                        </div>
                                    </div>
                                </x-container> {{-- Close the container component --}}
                                {{-- End of Manual section's inner p-6 div --}}
                    
                                </div> {{-- Add the closing div for the single bg-white container --}}
                                
                                <button type="button" id="add-question" class="mt-2 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    + Add Question
                                </button>
                            </div>

                            <div class="flex items-center justify-end mt-8">
                                <x-primary-button>
                                    {{ __('Create Job Description') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                    </div> {{-- End of p-6 for Manual section --}}
                </div> {{-- End of the single bg-white container --}}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add Question Button
            const addQuestionBtn = document.getElementById('add-question');
            const questionsContainer = document.getElementById('qualifying-questions-container');
            let questionCount = 1; // Start with 1 since we already have question[0]

            // Add Question
            addQuestionBtn.addEventListener('click', function() {
                const questionTemplate = document.querySelector('.question-item').cloneNode(true);
                
                // Update all IDs and names with the new index
                const inputs = questionTemplate.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/\[0\]/g, `[${questionCount}]`);
                    }
                    if (input.id) {
                        input.id = input.id.replace(/\[0\]/g, `[${questionCount}]`);
                    }
                });
                
                // Clear values
                const textInputs = questionTemplate.querySelectorAll('input[type="text"]');
                textInputs.forEach(input => {
                    input.value = '';
                });
                
                // Reset checkboxes
                const requiredCheckbox = questionTemplate.querySelector('input[name^="questions"][name$="[required]"]');
                if (requiredCheckbox) requiredCheckbox.checked = true;
                
                const knockoutCheckbox = questionTemplate.querySelector('input[name^="questions"][name$="[is_knockout]"]');
                if (knockoutCheckbox) knockoutCheckbox.checked = false;
                
                // Hide correct answer field
                const correctAnswerContainer = questionTemplate.querySelector('.correct-answer-container');
                if (correctAnswerContainer) correctAnswerContainer.classList.add('hidden');
                
                // Clear options except the first one
                const optionList = questionTemplate.querySelector('.option-list');
                const firstOption = optionList.querySelector('div');
                while (optionList.children.length > 1) {
                    optionList.removeChild(optionList.lastChild);
                }
                const firstOptionInput = firstOption.querySelector('input');
                if (firstOptionInput) firstOptionInput.value = '';
                
                // Add the new question to the container
                questionsContainer.appendChild(questionTemplate);
                questionCount++;
                
                // Re-attach event listeners
                attachEventListeners();
            });

            // Initial event listeners
            attachEventListeners();

            function attachEventListeners() {
                // Remove Question
                const removeQuestionBtns = document.querySelectorAll('.remove-question');
                removeQuestionBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        // Don't remove if it's the only question
                        if (document.querySelectorAll('.question-item').length > 1) {
                            this.closest('.question-item').remove();
                        }
                    });
                });

                // Add Option
                const addOptionBtns = document.querySelectorAll('.add-option');
                addOptionBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const optionList = this.previousElementSibling;
                        const newOption = optionList.querySelector('div').cloneNode(true);
                        const input = newOption.querySelector('input');
                        input.value = '';
                        
                        // Update the name to ensure it's part of the same array
                        const questionIndex = input.name.match(/questions\[(\d+)\]/)[1];
                        input.name = `questions[${questionIndex}][options][]`;
                        
                        optionList.appendChild(newOption);
                        
                        // Add remove option event listener
                        const removeBtn = newOption.querySelector('.remove-option');
                        removeBtn.addEventListener('click', function() {
                            if (optionList.children.length > 1) {
                                this.closest('div').remove();
                            }
                        });
                    });
                });

                // Remove Option
                const removeOptionBtns = document.querySelectorAll('.remove-option');
                removeOptionBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const optionList = this.closest('.option-list');
                        if (optionList.children.length > 1) {
                            this.closest('div').remove();
                        }
                    });
                });

                // Question Type Change
                const questionTypes = document.querySelectorAll('.question-type');
                questionTypes.forEach(select => {
                    select.addEventListener('change', function() {
                        const questionItem = this.closest('.question-item');
                        const optionsContainer = questionItem.querySelector('.options-container');
                        
                        if (this.value === 'multiple_choice') {
                            optionsContainer.classList.remove('hidden');
                        } else {
                            optionsContainer.classList.add('hidden');
                        }
                    });
                });

                // Knockout Checkbox
                const knockoutCheckboxes = document.querySelectorAll('.knockout-checkbox');
                knockoutCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const questionItem = this.closest('.question-item');
                        const correctAnswerContainer = questionItem.querySelector('.correct-answer-container');
                        
                        if (this.checked) {
                            correctAnswerContainer.classList.remove('hidden');
                        } else {
                            correctAnswerContainer.classList.add('hidden');
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const settingSelect = document.getElementById('ai_setting_id'); // Changed ID
        const modelSelect = document.getElementById('ai_model');
        const promptSelect = document.getElementById('ai_prompt_id');
        const allPrompts = {!! json_encode($prompts ?? []) !!}; // Get all JD prompts
        const providerModelsMap = {!! json_encode($providerModels ?? []) !!}; // Get the full model map
        const oldAiSettingId = '{{ old('ai_setting_id') }}'; // Get old setting ID
        const oldAiModel = '{{ old('ai_model') }}'; // Get old model
        const oldAiPromptId = '{{ old('ai_prompt_id') }}'; // Get old prompt ID

        function updateModelsDropdown() {
            // console.log('Updating models dropdown...');
            const selectedOption = settingSelect.options[settingSelect.selectedIndex];
            // Ensure we have a selected option with a value before proceeding
            if (!selectedOption || !selectedOption.value) {
                modelSelect.innerHTML = '<option value="">Select AI Setting first</option>';
                modelSelect.disabled = true;
                updatePromptsDropdown(); // Also update prompts
                return;
            }
            const selectedProviderType = selectedOption.dataset.providerType;
            // Get the list of models enabled *in this specific setting*
            let enabledModelsInSetting = [];
            try {
                 enabledModelsInSetting = JSON.parse(selectedOption.dataset.models || '[]');
                 if (!Array.isArray(enabledModelsInSetting)) enabledModelsInSetting = [];
            } catch (e) {
                 console.error("Error parsing enabled models data attribute for setting ID " + selectedOption.value + ":", e);
                 enabledModelsInSetting = [];
            }
            // Get *all* models available for this provider type from the central map
            const allModelsForProvider = providerModelsMap[selectedProviderType] || [];
            
            // Clear existing options
            modelSelect.innerHTML = '';
            
            if (allModelsForProvider.length === 0) {
                // Add a placeholder if no models are defined *at all* for this provider type
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'No models listed for this provider type';
                modelSelect.appendChild(defaultOption);
                modelSelect.disabled = true;
            } else {
                // console.log(`Populating ${allModelsForProvider.length} models for provider ${selectedProviderType}...`);
                
                // Check if the old selected model is actually valid for THIS setting's enabled models
                let modelToSelect = null;
                if (oldAiModel && enabledModelsInSetting.includes(oldAiModel)) {
                    // Prioritize old value if it's valid for this setting
                    modelToSelect = oldAiModel;
                } else if (enabledModelsInSetting.length > 0) {
                    // Otherwise, select the first model enabled in this setting
                    modelToSelect = enabledModelsInSetting[0];
                }
                // Iterate through ALL models for the provider type
                allModelsForProvider.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model;
                    option.textContent = model; // Display model name
                    // Pre-select the determined model
                    if (modelToSelect && model === modelToSelect) {
                        option.selected = true;
                    }
                    // Optionally, disable models not enabled in the setting? Or just show them?
                    // For now, show all, pre-select the old value if it was enabled.
                    modelSelect.appendChild(option);
                    // console.log(`Added model option: ${model}`);
                });
                // console.log('Finished populating models. Dropdown should be updated.');

                // Removed redundant fallback logic - handled by modelToSelect determination above
                // try to select the *first* model that IS enabled in this setting.
                if (!modelSelect.value && enabledModelsInSetting.length > 0) {
                    const firstEnabledModel = enabledModelsInSetting[0];
                    // Ensure the first enabled model exists in the dropdown before selecting
                    if (allModelsForProvider.includes(firstEnabledModel)) {
                    // No need to check allModelsForProvider, as we only added enabled models
                    modelSelect.value = firstEnabledModel;
                    }
                }
                modelSelect.disabled = false;
                // Re-initialize Select2 for the model dropdown
                $(modelSelect).select2({ width: '100%' }); // Removed theme
                // Trigger prompt update AFTER models are populated
                updatePromptsDropdown();
            }
        }

        // Add event listener if provider select exists
        // Function to update prompt dropdown (now depends on selected setting)
        function updatePromptsDropdown() {
            const selectedOption = settingSelect.options[settingSelect.selectedIndex];
            const selectedProviderType = selectedOption ? selectedOption.dataset.providerType : null;
            const selectedModel = modelSelect.value;
            
            // Filter prompts based on selected provider and model (or allow if provider/model is null)
            // Filter prompts based on selected provider type and model
            const filteredPrompts = allPrompts.filter(prompt =>
                (!prompt.provider || prompt.provider === selectedProviderType) &&
                (!prompt.model || prompt.model === selectedModel)
            );

            // Clear existing prompt options
            promptSelect.innerHTML = '<option value="">Use Default/Generic Prompt</option>';

            if (filteredPrompts.length > 0) {
                filteredPrompts.forEach(prompt => {
                    const option = document.createElement('option');
                    option.value = prompt.id;
                    option.textContent = prompt.name + (prompt.is_default ? ' (Default)' : '');
                    // Optionally pre-select the default prompt for the provider/model
                    // if (prompt.is_default && (!prompt.provider || prompt.provider === selectedProvider) && (!prompt.model || prompt.model === selectedModel)) {
                    if (prompt.id == oldAiPromptId) { // Select old value if it matches
                        option.selected = true;
                    }
                    // }
                    promptSelect.appendChild(option);
                });
                 promptSelect.disabled = false;
            } else {
                 promptSelect.disabled = !selectedOption || !selectedOption.value || modelSelect.disabled; // Use modelSelect.disabled instead of models.length
                 // Re-initialize Select2 for the prompt dropdown
                 $(promptSelect).select2({ width: '100%' }); // Removed theme
            }
        }

        // Add event listener if setting select exists
        if (settingSelect) {
            settingSelect.addEventListener('change', () => {
                updateModelsDropdown();
                // Prompts are updated when models change, which is triggered by updateModelsDropdown
            });
             modelSelect.addEventListener('change', updatePromptsDropdown); // Update prompts when model changes too

            // Initial population on page load
            // If there was an old setting selected, trigger change to populate models/prompts
            if (oldAiSettingId && settingSelect.value === oldAiSettingId) {
                 settingSelect.dispatchEvent(new Event('change'));
            } else {
                 // Otherwise, just populate models for the default selection (if any)
                 updateModelsDropdown();
            }
            updateModelsDropdown();
        } else {
             // Handle case where no settings are available
             modelSelect.innerHTML = '<option value="">No AI settings configured</option>';
             modelSelect.disabled = true;
             promptSelect.innerHTML = '<option value="">No AI settings configured</option>';
             promptSelect.disabled = true;
        }

        // --- Existing Qualifying Questions Script ---
        // Add Question Button
        const addQuestionBtn = document.getElementById('add-question');
        const questionsContainer = document.getElementById('qualifying-questions-container');
        let questionCount = document.querySelectorAll('.question-item').length; // Count existing items if any

        // Add Question
        if (addQuestionBtn) {
            addQuestionBtn.addEventListener('click', function() {
                const firstQuestionItem = document.querySelector('.question-item');
                if (!firstQuestionItem) return; // Don't add if template is missing

                const questionTemplate = firstQuestionItem.cloneNode(true);
                
                // Update all IDs and names with the new index
                const inputs = questionTemplate.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/\[\d+\]/g, `[${questionCount}]`);
                    }
                    if (input.id) {
                        input.id = input.id.replace(/\[\d+\]/g, `[${questionCount}]`);
                    }
                    // Also update labels' 'for' attribute
                    const label = questionTemplate.querySelector(`label[for='${input.id.replace(`[${questionCount}]`, '[0]')}']`);
                    if (label) {
                        label.htmlFor = input.id;
                    }
                });
                
                // Clear values
                const textInputs = questionTemplate.querySelectorAll('input[type="text"], textarea');
                textInputs.forEach(input => {
                    input.value = '';
                });
                const selects = questionTemplate.querySelectorAll('select');
                 selects.forEach(select => {
                     select.selectedIndex = 0; // Reset selects to first option
                 });

                
                // Reset checkboxes
                const requiredCheckbox = questionTemplate.querySelector('input[name$="[required]"]');
                if (requiredCheckbox) requiredCheckbox.checked = true;
                
                const knockoutCheckbox = questionTemplate.querySelector('input[name$="[is_knockout]"]');
                if (knockoutCheckbox) knockoutCheckbox.checked = false;
                
                // Hide correct answer field and options container initially
                const correctAnswerContainer = questionTemplate.querySelector('.correct-answer-container');
                if (correctAnswerContainer) correctAnswerContainer.classList.add('hidden');
                const optionsContainer = questionTemplate.querySelector('.options-container');
                 if (optionsContainer) optionsContainer.classList.add('hidden'); // Hide options by default unless type is multi-choice

                
                // Clear options except the first one
                const optionList = questionTemplate.querySelector('.option-list');
                if (optionList) {
                    const firstOption = optionList.querySelector('div');
                    while (optionList.children.length > 1) {
                        optionList.removeChild(optionList.lastChild);
                    }
                    const firstOptionInput = firstOption?.querySelector('input');
                    if (firstOptionInput) firstOptionInput.value = '';
                }

                
                // Add the new question to the container
                questionsContainer.appendChild(questionTemplate);
                questionCount++;
                
                // Re-attach event listeners for the new question item
                attachEventListenersToItem(questionTemplate);
            });
        }

        // Initial event listeners for existing questions
        document.querySelectorAll('.question-item').forEach(item => {
            attachEventListenersToItem(item);
        });

        function attachEventListenersToItem(questionItem) {
            // Remove Question
            const removeQuestionBtn = questionItem.querySelector('.remove-question');
            if (removeQuestionBtn) {
                removeQuestionBtn.addEventListener('click', function() {
                    if (document.querySelectorAll('.question-item').length > 1) {
                        this.closest('.question-item').remove();
                    } else {
                        // Optionally clear the fields of the last remaining question instead of removing
                        const inputs = questionItem.querySelectorAll('input[type="text"], textarea');
                        inputs.forEach(input => input.value = '');
                        const selects = questionItem.querySelectorAll('select');
                        selects.forEach(select => select.selectedIndex = 0);
                        const checkboxes = questionItem.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(cb => cb.checked = (cb.name.includes('[required]'))); // Default required to true
                        questionItem.querySelector('.options-container')?.classList.add('hidden');
                        questionItem.querySelector('.correct-answer-container')?.classList.add('hidden');

                    }
                });
            }

            // Add Option
            const addOptionBtn = questionItem.querySelector('.add-option');
            if (addOptionBtn) {
                addOptionBtn.addEventListener('click', function() {
                    const optionList = this.previousElementSibling;
                    const firstOption = optionList.querySelector('div');
                    if (!firstOption) return; // Should not happen if structure is correct

                    const newOption = firstOption.cloneNode(true);
                    const input = newOption.querySelector('input');
                    input.value = '';
                    
                    // Update the name to ensure it's part of the same array
                    const questionIndex = input.name.match(/questions\[(\d+)\]/)[1];
                    input.name = `questions[${questionIndex}][options][]`;
                    
                    optionList.appendChild(newOption);
                    
                    // Add remove option event listener to the new option's button
                    const removeBtn = newOption.querySelector('.remove-option');
                     if(removeBtn) {
                         removeBtn.addEventListener('click', function() {
                             if (optionList.children.length > 1) {
                                 this.closest('.flex.items-center').remove(); // Remove the parent div
                             }
                         });
                     }
                });
            }

            // Remove Option (for existing options)
            const removeOptionBtns = questionItem.querySelectorAll('.remove-option');
            removeOptionBtns.forEach(btn => {
                 // Prevent adding listener twice if it's the first option's button
                 if (!btn.dataset.listenerAttached) {
                     btn.addEventListener('click', function() {
                         const optionList = this.closest('.option-list');
                         if (optionList.children.length > 1) {
                             this.closest('.flex.items-center').remove(); // Remove the parent div
                         }
                     });
                     btn.dataset.listenerAttached = true; // Mark as attached
                 }
            });

            // Question Type Change
            const questionTypeSelect = questionItem.querySelector('.question-type');
            if (questionTypeSelect) {
                questionTypeSelect.addEventListener('change', function() {
                    const optionsContainer = questionItem.querySelector('.options-container');
                    if (optionsContainer) {
                        if (this.value === 'multiple_choice') {
                            optionsContainer.classList.remove('hidden');
                        } else {
                            optionsContainer.classList.add('hidden');
                        }
                    }
                });
                 // Trigger change on load to set initial state
                 questionTypeSelect.dispatchEvent(new Event('change'));
            }

            // Knockout Checkbox
            const knockoutCheckbox = questionItem.querySelector('.knockout-checkbox');
            if (knockoutCheckbox) {
                knockoutCheckbox.addEventListener('change', function() {
                    const correctAnswerContainer = questionItem.querySelector('.correct-answer-container');
                     if (correctAnswerContainer) {
                         correctAnswerContainer.classList.toggle('hidden', !this.checked);
                     }
                });
                 // Trigger change on load
                 knockoutCheckbox.dispatchEvent(new Event('change'));
            }
        }
    });
</script>

{{-- Script for Template Pre-population --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const templateSelect = document.getElementById('template_id');
        const templatesData = {!! json_encode($templatesData ?? []) !!}; // Get template data from controller

        // Map template field names to form input IDs
        const fieldMap = {
            'overview_template': 'overview',
            'responsibilities_template': 'responsibilities',
            'requirements_template': 'requirements_non_negotiable', 
            'benefits_template': 'benefits',
            'disclaimer_template': 'disclaimer',
            'industry': 'industry',
            'job_level': 'experience_level', // Map job_level to experience_level
            'location': 'location',
            'compensation_range': 'compensation_range',
            'employment_type': 'employment_type',
            'education_requirements': 'education_requirements',
            'skills_required': 'skills_required',
            'skills_preferred': 'skills_preferred'
            // Add other mappings if needed
        };

        templateSelect.addEventListener('change', function() {
            const selectedTemplateId = this.value;
            const templateContent = templatesData[selectedTemplateId] || {};

            console.log('Selected template ID:', selectedTemplateId);
            console.log('Template content:', templateContent);
            
            for (const templateField in fieldMap) {
                const formFieldId = fieldMap[templateField];
                const formField = document.getElementById(formFieldId);
                
                if (formField) {
                    // Check if the template field exists in the template content
                    if (selectedTemplateId && templateContent.hasOwnProperty(templateField)) {
                        formField.value = templateContent[templateField] || '';
                        console.log(`Mapped ${templateField} → ${formFieldId}: "${templateContent[templateField]}"`);
                    } else if (selectedTemplateId) {
                        console.warn(`Template field "${templateField}" not found in template data`);
                    } else {
                        formField.value = ''; // Clear if no template selected
                    }
                    
                    // Trigger change event for selects if needed for Select2 update
                    if (formField.tagName === 'SELECT') {
                        $(formField).trigger('change.select2'); // Trigger Select2 update if it's applied
                    }
                } else {
                    console.warn(`Form field with ID "${formFieldId}" not found`);
                }
            }
        });
        
        // Populate from project data
        // Add event listener for project selection
        const projectSelect = document.getElementsByClassName('manual_project_id')[0];
        projectSelect.addEventListener('change', function() {
            const selectedProjectId = this.value;
            console.log('Selected project ID:', selectedProjectId);
            if (!selectedProjectId) return;
            
            // Get project data from the server
            const projectsData = {!! json_encode($projects->keyBy('id')->map(function($project) {
                return [
                    'title' => $project->job_title ?? $project->title,
                    'location' => $project->location,
                    'salary_range' => $project->salary_range
                ];
            })) !!};
            
            const projectData = projectsData[selectedProjectId] || {};
            
            // Populate job title from project
            const titleField = document.getElementById('title');
            if (titleField && projectData.title) {
                titleField.value = projectData.title;
            }
            
            // Populate location from project
            const locationField = document.getElementById('location');
            if (locationField && projectData.location) {
                locationField.value = projectData.location;
            }
            
            // Populate compensation range from project
            const compensationField = document.getElementById('compensation_range');
            if (compensationField && projectData.salary_range) {
                compensationField.value = projectData.salary_range;
            }
        });
        
        // Trigger change on load if a template was selected previously (due to validation error)
        if (templateSelect.value) {
            templateSelect.dispatchEvent(new Event('change'));
        } else if (projectSelect.value) {
            // If no template but project is selected, populate from project
            projectSelect.dispatchEvent(new Event('change'));
        }
    });
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 on relevant dropdowns (including the new template dropdown)
        $('#project_id').select2({ width: '100%' });
        $('#ai_setting_id').select2({ width: '100%' });
        $('#template_id').select2({ width: '100%' }); // Initialize template dropdown
        // Note: ai_model and ai_prompt_id are initialized dynamically after options load
        // Add for manual form selects too
        $('#experience_level').select2({ width: '100%' });
        $('#employment_type').select2({ width: '100%' });
        $('#status').select2({ width: '100%' });

    });
</script>
@endpush