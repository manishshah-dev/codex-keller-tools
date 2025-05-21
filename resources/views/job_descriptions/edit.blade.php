<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Job Description for {{ $project->title }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('job-descriptions.show', $jobDescription) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Cancel
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Job Description Details</h3>
                        <div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                {{ $jobDescription->status === 'published' ? 'bg-green-100 text-green-800' : 
                                   ($jobDescription->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                   ($jobDescription->status === 'review' ? 'bg-yellow-100 text-yellow-800' : 
                                   'bg-gray-100 text-gray-800')) }}">
                                Version {{ $jobDescription->version }} | {{ ucfirst($jobDescription->status) }}
                            </span>
                        </div>
                    </div>
                    
                    <form action="{{ route('job-descriptions.update', $jobDescription) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="job_description">
                        
                        <div>
                            <x-input-label for="title" :value="__('Job Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $jobDescription->title)" required />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="industry" :value="__('Industry')" />
                                <x-text-input id="industry" class="block mt-1 w-full" type="text" name="industry" :value="old('industry', $jobDescription->industry)" />
                                <x-input-error :messages="$errors->get('industry')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="experience_level" :value="__('Experience Level')" />
                                <select id="experience_level" name="experience_level" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Experience Level</option>
                                    <option value="entry" {{ old('experience_level', $jobDescription->experience_level) === 'entry' ? 'selected' : '' }}>Entry Level</option>
                                    <option value="mid" {{ old('experience_level', $jobDescription->experience_level) === 'mid' ? 'selected' : '' }}>Mid Level</option>
                                    <option value="senior" {{ old('experience_level', $jobDescription->experience_level) === 'senior' ? 'selected' : '' }}>Senior Level</option>
                                    <option value="executive" {{ old('experience_level', $jobDescription->experience_level) === 'executive' ? 'selected' : '' }}>Executive</option>
                                </select>
                                <x-input-error :messages="$errors->get('experience_level')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="employment_type" :value="__('Employment Type')" />
                                <select id="employment_type" name="employment_type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Select Employment Type</option>
                                    <option value="full-time" {{ old('employment_type', $jobDescription->employment_type) === 'full-time' ? 'selected' : '' }}>Full-time</option>
                                    <option value="part-time" {{ old('employment_type', $jobDescription->employment_type) === 'part-time' ? 'selected' : '' }}>Part-time</option>
                                    <option value="contract" {{ old('employment_type', $jobDescription->employment_type) === 'contract' ? 'selected' : '' }}>Contract</option>
                                    <option value="temporary" {{ old('employment_type', $jobDescription->employment_type) === 'temporary' ? 'selected' : '' }}>Temporary</option>
                                    <option value="internship" {{ old('employment_type', $jobDescription->employment_type) === 'internship' ? 'selected' : '' }}>Internship</option>
                                </select>
                                <x-input-error :messages="$errors->get('employment_type')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="location" :value="__('Location')" />
                                <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $jobDescription->location)" />
                                <x-input-error :messages="$errors->get('location')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="compensation_range" :value="__('Compensation Range')" />
                                <x-text-input id="compensation_range" class="block mt-1 w-full" type="text" name="compensation_range" :value="old('compensation_range', $jobDescription->compensation_range)" />
                                <x-input-error :messages="$errors->get('compensation_range')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="overview" :value="__('Overview')" />
                            <textarea id="overview" name="overview" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('overview', $jobDescription->overview) }}</textarea>
                            <x-input-error :messages="$errors->get('overview')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="responsibilities" :value="__('Responsibilities')" />
                            <textarea id="responsibilities" name="responsibilities" rows="6" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('responsibilities', $jobDescription->responsibilities) }}</textarea>
                            <x-input-error :messages="$errors->get('responsibilities')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="requirements_non_negotiable" :value="__('Requirements (Non-Negotiable)')" />
                            <textarea id="requirements_non_negotiable" name="requirements_non_negotiable" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('requirements_non_negotiable', $jobDescription->requirements_non_negotiable) }}</textarea>
                            <x-input-error :messages="$errors->get('requirements_non_negotiable')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="requirements_preferred" :value="__('Requirements (Preferred)')" />
                            <textarea id="requirements_preferred" name="requirements_preferred" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('requirements_preferred', $jobDescription->requirements_preferred) }}</textarea>
                            <x-input-error :messages="$errors->get('requirements_preferred')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="benefits" :value="__('Benefits')" />
                            <textarea id="benefits" name="benefits" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('benefits', $jobDescription->benefits) }}</textarea>
                            <x-input-error :messages="$errors->get('benefits')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="disclaimer" :value="__('Disclaimer')" />
                            <textarea id="disclaimer" name="disclaimer" rows="2" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('disclaimer', $jobDescription->disclaimer) }}</textarea>
                            <x-input-error :messages="$errors->get('disclaimer')" class="mt-2" />
                        </div>
                        
                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="draft" {{ old('status', $jobDescription->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="review" {{ old('status', $jobDescription->status) === 'review' ? 'selected' : '' }}>In Review</option>
                                <option value="approved" {{ old('status', $jobDescription->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="published" {{ old('status', $jobDescription->status) === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Job Description') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Qualifying Questions Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Qualifying Questions</h3>
                        <button type="button" id="add-question-btn" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                            Add Question
                        </button>
                    </div>
                    
                    <form action="{{ route('job-descriptions.update', $jobDescription) }}" method="POST" id="questions-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="questions">
                        
                        <div id="questions-container" class="space-y-4">
                            @foreach($qualifyingQuestions as $index => $question)
                            <div class="question-item border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium">Question #{{ $index + 1 }}</h4>
                                    <button type="button" class="remove-question text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Question</label>
                                        <input type="text" name="questions[{{ $index }}][question]" value="{{ $question->question }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Type</label>
                                        <select name="questions[{{ $index }}][type]" class="question-type mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="multiple_choice" {{ $question->type === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                            <option value="yes_no" {{ $question->type === 'yes_no' ? 'selected' : '' }}>Yes/No</option>
                                            <option value="text" {{ $question->type === 'text' ? 'selected' : '' }}>Text</option>
                                            <option value="numeric" {{ $question->type === 'numeric' ? 'selected' : '' }}>Numeric</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                    <input type="text" name="questions[{{ $index }}][description]" value="{{ $question->description }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div class="options-container {{ $question->type !== 'multiple_choice' ? 'hidden' : '' }}">
                                    <label class="block text-sm font-medium text-gray-700">Options (One per line)</label>
                                    <textarea name="questions[{{ $index }}][options_text]" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ is_array($question->options) ? implode("\n", $question->options) : '' }}</textarea>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Category (Optional)</label>
                                        <input type="text" name="questions[{{ $index }}][category]" value="{{ $question->category }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Order</label>
                                        <input type="number" name="questions[{{ $index }}][order]" value="{{ $question->order }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    
                                    <div class="flex items-center mt-6">
                                        <input type="checkbox" name="questions[{{ $index }}][required]" id="required-{{ $index }}" {{ $question->required ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <label for="required-{{ $index }}" class="ml-2 block text-sm text-gray-700">Required</label>
                                    </div>
                                </div>
                                
                                <div class="knockout-container mt-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="questions[{{ $index }}][is_knockout]" id="knockout-{{ $index }}" {{ $question->is_knockout ? 'checked' : '' }} class="knockout-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <label for="knockout-{{ $index }}" class="ml-2 block text-sm text-gray-700">Knockout Question</label>
                                    </div>
                                    
                                    <div class="correct-answer-container mt-2 {{ $question->is_knockout ? '' : 'hidden' }}">
                                        <label class="block text-sm font-medium text-gray-700">Correct Answer</label>
                                        <input type="text" name="questions[{{ $index }}][correct_answer]" value="{{ $question->correct_answer }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                
                                <input type="hidden" name="questions[{{ $index }}][is_ai_generated]" value="{{ $question->is_ai_generated ? '1' : '0' }}">
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Save Questions') }}
                            </x-primary-button>
                        </div>
                    </form>
                    
                    <!-- Question Template (Hidden) -->
                    <template id="question-template">
                        <div class="question-item border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium">New Question</h4>
                                <button type="button" class="remove-question text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Question</label>
                                    <input type="text" name="questions[INDEX][question]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select name="questions[INDEX][type]" class="question-type mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="yes_no">Yes/No</option>
                                        <option value="text">Text</option>
                                        <option value="numeric">Numeric</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                <input type="text" name="questions[INDEX][description]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            
                            <div class="options-container">
                                <label class="block text-sm font-medium text-gray-700">Options (One per line)</label>
                                <textarea name="questions[INDEX][options_text]" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Category (Optional)</label>
                                    <input type="text" name="questions[INDEX][category]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Order</label>
                                    <input type="number" name="questions[INDEX][order]" value="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div class="flex items-center mt-6">
                                    <input type="checkbox" name="questions[INDEX][required]" id="required-INDEX" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <label for="required-INDEX" class="ml-2 block text-sm text-gray-700">Required</label>
                                </div>
                            </div>
                            
                            <div class="knockout-container mt-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="questions[INDEX][is_knockout]" id="knockout-INDEX" class="knockout-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <label for="knockout-INDEX" class="ml-2 block text-sm text-gray-700">Knockout Question</label>
                                </div>
                                
                                <div class="correct-answer-container mt-2 hidden">
                                    <label class="block text-sm font-medium text-gray-700">Correct Answer</label>
                                    <input type="text" name="questions[INDEX][correct_answer]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            
                            <input type="hidden" name="questions[INDEX][is_ai_generated]" value="0">
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const questionsContainer = document.getElementById('questions-container');
            const addQuestionBtn = document.getElementById('add-question-btn');
            const questionTemplate = document.getElementById('question-template');
            
            // Add new question
            addQuestionBtn.addEventListener('click', function() {
                const questionCount = document.querySelectorAll('.question-item').length;
                const newIndex = questionCount;
                
                const clone = document.importNode(questionTemplate.content, true);
                const newItem = clone.querySelector('.question-item');
                
                // Update all input names with the correct index
                newItem.querySelectorAll('[name*="INDEX"]').forEach(input => {
                    input.name = input.name.replace('INDEX', newIndex);
                });
                
                // Update all IDs with the correct index
                newItem.querySelectorAll('[id*="INDEX"]').forEach(element => {
                    const newId = element.id.replace('INDEX', newIndex);
                    element.id = newId;
                    
                    // Update associated labels
                    const label = newItem.querySelector(`label[for="${element.id}"]`);
                    if (label) {
                        label.setAttribute('for', newId);
                    }
                });
                
                questionsContainer.appendChild(newItem);
                setupEventListeners(newItem);
            });
            
            // Setup event listeners for existing questions
            document.querySelectorAll('.question-item').forEach(item => {
                setupEventListeners(item);
            });
            
            function setupEventListeners(item) {
                // Remove question
                const removeBtn = item.querySelector('.remove-question');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        item.remove();
                    });
                }
                
                // Toggle options container based on question type
                const typeSelect = item.querySelector('.question-type');
                const optionsContainer = item.querySelector('.options-container');
                
                if (typeSelect && optionsContainer) {
                    typeSelect.addEventListener('change', function() {
                        if (this.value === 'multiple_choice') {
                            optionsContainer.classList.remove('hidden');
                        } else {
                            optionsContainer.classList.add('hidden');
                        }
                    });
                }
                
                // Toggle correct answer container based on knockout checkbox
                const knockoutCheckbox = item.querySelector('.knockout-checkbox');
                const correctAnswerContainer = item.querySelector('.correct-answer-container');
                
                if (knockoutCheckbox && correctAnswerContainer) {
                    knockoutCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            correctAnswerContainer.classList.remove('hidden');
                        } else {
                            correctAnswerContainer.classList.add('hidden');
                        }
                    });
                }
            }
        });
    </script>
</x-app-layout>