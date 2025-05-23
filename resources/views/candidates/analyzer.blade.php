<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('CV Analyzer') }}: {{ $project->title }}
                </h2>
                <p class="text-gray-600 mt-1">
                    <a href="{{ route('projects.candidates.index', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                        Back to Candidates
                    </a>
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Requirements -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Requirements</h3>
                                <button id="add-requirement-btn" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    + Add Requirement
                                </button>
                            </div>
                            
                            <div id="add-requirement-form" class="hidden mb-4 p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium mb-2">Add New Requirement</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label for="requirement-name" class="block text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" id="requirement-name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="requirement-type" class="block text-sm font-medium text-gray-700">Type</label>
                                        <select id="requirement-type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="skill">Skill</option>
                                            <option value="experience">Experience</option>
                                            <option value="education">Education</option>
                                            <option value="certification">Certification</option>
                                            <option value="language">Language</option>
                                            <option value="location">Location</option>
                                            <option value="industry">Industry</option>
                                            <option value="tool">Tool</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="requirement-weight" class="block text-sm font-medium text-gray-700">Weight (0-100%)</label>
                                        <input type="range" id="requirement-weight" min="0" max="100" value="50" class="mt-1 block w-full">
                                        <span id="weight-display" class="text-xs text-gray-500">50%</span>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="requirement-required" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="requirement-required" class="ml-2 block text-sm text-gray-900">Required</label>
                                    </div>
                                    <div class="flex justify-end space-x-2">
                                        <button id="cancel-requirement-btn" class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                            Cancel
                                        </button>
                                        <button id="save-requirement-btn" class="px-3 py-1 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="requirements-list" class="space-y-2">
                                @if(count($requirements) > 0)
                                    @foreach($requirements as $requirement)
                                        <div class="p-3 border border-gray-200 rounded-lg requirement-item" data-id="{{ $requirement->id }}">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <span class="font-medium">{{ $requirement->name }}</span>
                                                    <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ $requirement->type_badge_class }}">
                                                        {{ ucfirst($requirement->type) }}
                                                    </span>
                                                    @if($requirement->is_required)
                                                        <span class="ml-1 bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded-full">Required</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-sm mr-2">{{ $requirement->weight_percentage }}</span>
                                                    <button class="text-red-500 hover:text-red-700 remove-requirement-btn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        <p>No requirements defined yet.</p>
                                        <p class="text-sm">Add requirements manually or use the chat interface.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Candidates -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-4">Top Candidates</h3>
                            
                            @if(count($candidates) > 0)
                                <div class="space-y-3">
                                    @foreach($candidates->take(5) as $candidate)
                                        <div class="p-3 border border-gray-200 rounded-lg candidate-item" data-id="{{ $candidate->id }}">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <a href="{{ route('projects.candidates.show', [$project, $candidate]) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                                                        {{ $candidate->full_name }}
                                                    </a>
                                                    <p class="text-sm text-gray-600">{{ $candidate->current_position ?? 'N/A' }}</p>
                                                </div>
                                                <div>
                                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $candidate->status_badge_class }}">
                                                        {{ ucfirst($candidate->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <div class="flex justify-between items-center text-xs text-gray-500 mb-1">
                                                    <span>Match Score</span>
                                                    <span>{{ $candidate->match_score_percentage }}</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $candidate->match_score_percentage }}"></div>
                                                </div>
                                            </div>
                                            <div class="mt-2 flex justify-end">
                                                <button class="text-sm text-indigo-600 hover:text-indigo-900 analyze-candidate-btn" data-id="{{ $candidate->id }}">
                                                    Analyze
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <a href="{{ route('projects.candidates.index', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                        View All Candidates
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4 text-gray-500">
                                    <p>No candidates available.</p>
                                    <a href="{{ route('projects.candidates.create', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                        Add Candidates
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Chat Interface -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">CV Analyzer Chat</h3>
                                <div>
                                    <select id="ai-provider" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        @foreach($aiSettings as $setting)
                                            <option value="{{ $setting->provider }}">{{ ucfirst($setting->provider) }} ({{ $setting->name }})</option>
                                        @endforeach
                                    </select>
                                    <select id="ai-model" class="ml-2 text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <!-- Will be populated by JavaScript -->
                                    </select>
                                </div>
                            </div>
                            
                            <div id="chat-container" class="border border-gray-200 rounded-lg h-96 flex flex-col">
                                <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4">
                                    <div class="bg-blue-100 text-blue-800 p-3 rounded-lg max-w-3/4 ml-auto">
                                        <p>Hello! I'm your CV Analyzer assistant. I can help you:</p>
                                        <ul class="list-disc list-inside text-sm mt-2">
                                            <li>Add or remove requirements</li>
                                            <li>Analyze candidates against requirements</li>
                                            <li>Provide insights on candidate matches</li>
                                            <li>Answer questions about the recruitment process</li>
                                        </ul>
                                        <p class="mt-2">What would you like to do today?</p>
                                    </div>
                                    <div class="bg-gray-100 text-gray-800 p-3 rounded-lg max-w-3/4 mr-auto">
                                        <p>Try asking:</p>
                                        <ul class="list-disc list-inside text-sm mt-2">
                                            <li>"Add a requirement for 5+ years of JavaScript experience"</li>
                                            <li>"Remove the location requirement"</li>
                                            <li>"Which candidates have experience with React?"</li>
                                            <li>"Analyze John Smith's resume"</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="border-t border-gray-200 p-4">
                                    <form id="chat-form" class="flex space-x-2">
                                        <input type="hidden" id="candidate-id" value="">
                                        <input type="text" id="chat-input" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Type your message...">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            Send
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="mt-4 text-sm text-gray-500">
                                <p>Tips:</p>
                                <ul class="list-disc list-inside">
                                    <li>Be specific when adding requirements (e.g., "Add a requirement for React with 3 years of experience")</li>
                                    <li>You can specify if a requirement is mandatory (e.g., "Add a required skill: JavaScript")</li>
                                    <li>Adjust weights by mentioning importance (e.g., "Add Python experience with high importance")</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include marked.js for Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    
    <script>
        // Provider models data from PHP
        const providerModels = @json($providerModels);
        
        document.addEventListener('DOMContentLoaded', function() {
            // Requirements form toggle
            const addRequirementBtn = document.getElementById('add-requirement-btn');
            const addRequirementForm = document.getElementById('add-requirement-form');
            const cancelRequirementBtn = document.getElementById('cancel-requirement-btn');
            
            addRequirementBtn.addEventListener('click', function() {
                addRequirementForm.classList.toggle('hidden');
            });
            
            cancelRequirementBtn.addEventListener('click', function() {
                addRequirementForm.classList.add('hidden');
            });
            
            // Weight slider
            const weightSlider = document.getElementById('requirement-weight');
            const weightDisplay = document.getElementById('weight-display');
            
            weightSlider.addEventListener('input', function() {
                weightDisplay.textContent = this.value + '%';
            });
            
            // Chat functionality
            const chatForm = document.getElementById('chat-form');
            const chatInput = document.getElementById('chat-input');
            const chatMessages = document.getElementById('chat-messages');
            const aiProvider = document.getElementById('ai-provider');
            const aiModel = document.getElementById('ai-model');
            const candidateId = document.getElementById('candidate-id');
            
            // Function to update model dropdown based on selected provider
            function updateModelDropdown() {
                const provider = aiProvider.value;
                const models = providerModels[provider] || [];
                
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
            
            // Update models when provider changes
            aiProvider.addEventListener('change', updateModelDropdown);
            
            // Initialize model dropdown
            updateModelDropdown();
            
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const message = chatInput.value.trim();
                if (!message) return;
                
                // Add user message to chat
                addMessageToChat(message, true);
                
                // Clear input
                chatInput.value = '';
                
                // Send to server
                sendChatMessage(message);
            });
            
            // Analyze candidate buttons
            const analyzeCandidateBtns = document.querySelectorAll('.analyze-candidate-btn');
            analyzeCandidateBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    candidateId.value = id;
                    
                    // Add system message
                    const candidateName = this.closest('.candidate-item').querySelector('a').textContent.trim();
                    addMessageToChat(`Now analyzing candidate: ${candidateName}`, false);
                    
                    // Automatically send a message to analyze the candidate
                    sendChatMessage(`Analyze candidate ${candidateName}'s resume and provide a summary of their qualifications.`);
                });
            });
            
            // Remove requirement buttons
            const removeRequirementBtns = document.querySelectorAll('.remove-requirement-btn');
            removeRequirementBtns.forEach(btn => {
                attachRemoveRequirementListener(btn);
            });
            
            // Save requirement button
            const saveRequirementBtn = document.getElementById('save-requirement-btn');
            saveRequirementBtn.addEventListener('click', function() {
                const name = document.getElementById('requirement-name').value.trim();
                const type = document.getElementById('requirement-type').value;
                const weight = document.getElementById('requirement-weight').value / 100; // Convert to decimal (0-1)
                const isRequired = document.getElementById('requirement-required').checked;
                
                if (!name) {
                    alert('Please enter a requirement name');
                    return;
                }
                
                // Show loading state
                saveRequirementBtn.disabled = true;
                saveRequirementBtn.textContent = 'Saving...';
                
                // Save to database via AJAX
                fetch('{{ route('projects.requirements.store', $project) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name: name,
                        type: type,
                        weight: weight,
                        is_required: isRequired,
                        description: 'Added via CV Analyzer',
                        source: 'chat'
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button state
                    saveRequirementBtn.disabled = false;
                    saveRequirementBtn.textContent = 'Save';
                    
                    if (data.success) {
                        // Construct chat message
                        let message = `Add a ${type} requirement: ${name} with weight ${weight * 100}%`;
                        if (isRequired) {
                            message += ' (required)';
                        }
                        
                        // Send chat message
                        sendChatMessage(message);
                        
                        // Hide form and reset
                        addRequirementForm.classList.add('hidden');
                        document.getElementById('requirement-name').value = '';
                        document.getElementById('requirement-weight').value = 50;
                        weightDisplay.textContent = '50%';
                        document.getElementById('requirement-required').checked = false;
                        
                        // Add the new requirement to the list
                        addRequirementToList(data.requirement);
                    } else {
                        alert('Error saving requirement: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    saveRequirementBtn.disabled = false;
                    saveRequirementBtn.textContent = 'Save';
                    alert('Failed to save requirement. Please try again.');
                });
            });
            
            // Function to add a new requirement to the list
            function addRequirementToList(requirement) {
                const requirementsList = document.getElementById('requirements-list');
                
                // Check if the "no requirements" message exists and remove it
                const noRequirementsMsg = requirementsList.querySelector('.text-center.py-4.text-gray-500');
                if (noRequirementsMsg) {
                    noRequirementsMsg.remove();
                }
                
                // Create the new requirement element
                const requirementItem = document.createElement('div');
                requirementItem.className = 'p-3 border border-gray-200 rounded-lg requirement-item';
                requirementItem.setAttribute('data-id', requirement.id);
                
                // Get the badge class based on type
                const typeBadgeClass = getTypeBadgeClass(requirement.type);
                
                // Calculate weight percentage
                const weightPercentage = Math.round(requirement.weight * 100) + '%';
                
                // Create the inner HTML
                requirementItem.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-medium">${requirement.name}</span>
                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs ${typeBadgeClass}">
                                ${requirement.type.charAt(0).toUpperCase() + requirement.type.slice(1)}
                            </span>
                            ${requirement.is_required ?
                                '<span class="ml-1 bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded-full">Required</span>' :
                                ''}
                        </div>
                        <div class="flex items-center">
                            <span class="text-sm mr-2">${weightPercentage}</span>
                            <button class="text-red-500 hover:text-red-700 remove-requirement-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
                
                // Add the new requirement to the list
                requirementsList.prepend(requirementItem);
                
                // Add event listener to the remove button
                const removeBtn = requirementItem.querySelector('.remove-requirement-btn');
                attachRemoveRequirementListener(removeBtn);
            }

            const requirementDestroyUrl = '{{ route('projects.requirements.destroy', [$project, 0]) }}';

            function attachRemoveRequirementListener(button) {
                button.addEventListener('click', function() {
                    const requirementItem = this.closest('.requirement-item');
                    const id = requirementItem.getAttribute('data-id');
                    const name = requirementItem.querySelector('.font-medium').textContent.trim();

                    if (confirm(`Are you sure you want to remove the requirement: ${name}?`)) {
                        fetch(requirementDestroyUrl.replace('/0', '/' + id), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                requirementItem.remove();

                                const requirementsList = document.getElementById('requirements-list');
                                if (requirementsList.children.length === 0) {
                                    const emptyMessage = document.createElement('div');
                                    emptyMessage.className = 'text-center py-4 text-gray-500';
                                    emptyMessage.innerHTML = `
                                        <p>No requirements defined yet.</p>
                                        <p class="text-sm">Add requirements manually or use the chat interface.</p>
                                    `;
                                    requirementsList.appendChild(emptyMessage);
                                }

                                sendChatMessage(`Remove requirement ${name}`);
                            } else {
                                alert('Error removing requirement: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to remove requirement. Please try again.');
                        });
                    }
                });
            }
            
            // Helper function to get badge class based on requirement type
            function getTypeBadgeClass(type) {
                const badgeClasses = {
                    'skill': 'bg-blue-100 text-blue-800',
                    'experience': 'bg-green-100 text-green-800',
                    'education': 'bg-purple-100 text-purple-800',
                    'certification': 'bg-yellow-100 text-yellow-800',
                    'language': 'bg-pink-100 text-pink-800',
                    'location': 'bg-indigo-100 text-indigo-800',
                    'industry': 'bg-red-100 text-red-800',
                    'tool': 'bg-orange-100 text-orange-800'
                };
                
                return badgeClasses[type] || 'bg-gray-100 text-gray-800';
            }
            
            function addMessageToChat(message, isUser) {
                const messageDiv = document.createElement('div');
                messageDiv.className = isUser
                    ? 'bg-blue-100 text-blue-800 p-3 rounded-lg max-w-3/4 ml-auto'
                    : 'bg-gray-100 text-gray-800 p-3 rounded-lg max-w-3/4 mr-auto';
                
                if (isUser) {
                    // For user messages, use plain text
                    messageDiv.textContent = message;
                } else {
                    // For AI messages, render Markdown
                    // Set options for marked to ensure proper rendering and security
                    marked.setOptions({
                        breaks: true, // Add line breaks
                        gfm: true,    // Use GitHub Flavored Markdown
                        sanitize: true // Sanitize HTML (for security)
                    });
                    
                    // Parse markdown and set as HTML
                    messageDiv.innerHTML = marked.parse(message);
                }
                
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
            
            function sendChatMessage(message) {
                // Show loading indicator (special case - not user message but also not markdown)
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'bg-gray-100 text-gray-800 p-3 rounded-lg max-w-3/4 mr-auto';
                loadingDiv.textContent = 'Thinking...';
                chatMessages.appendChild(loadingDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // Prepare data
                const data = {
                    message: message,
                    ai_provider: aiProvider.value,
                    ai_model: aiModel.value,
                };
                
                if (candidateId.value) {
                    data.candidate_id = candidateId.value;
                }
                
                // Send to server
                fetch('{{ route('projects.analyzer.chat', $project) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(data => {
                    // Remove loading indicator
                    if (chatMessages.lastChild && chatMessages.lastChild.textContent === 'Thinking...') {
                        chatMessages.removeChild(chatMessages.lastChild);
                    }
                    
                    if (data.success) {
                        // Add AI response
                        addMessageToChat(data.ai_message.message, false);
                        
                        // Handle requirements changes
                        if (data.requirements_added && data.requirements_added.length > 0) {
                            // Add each new requirement to the list
                            data.requirements_added.forEach(requirement => {
                                addRequirementToList(requirement);
                            });
                        }
                        
                        if (data.requirements_removed && data.requirements_removed.length > 0) {
                            // Remove each requirement from the list
                            data.requirements_removed.forEach(requirementId => {
                                const requirementItem = document.querySelector(`.requirement-item[data-id="${requirementId}"]`);
                                if (requirementItem) {
                                    requirementItem.remove();
                                }
                            });
                            
                            // If no requirements left, show the empty message
                            const requirementsList = document.getElementById('requirements-list');
                            if (requirementsList.children.length === 0) {
                                const emptyMessage = document.createElement('div');
                                emptyMessage.className = 'text-center py-4 text-gray-500';
                                emptyMessage.innerHTML = `
                                    <p>No requirements defined yet.</p>
                                    <p class="text-sm">Add requirements manually or use the chat interface.</p>
                                `;
                                requirementsList.appendChild(emptyMessage);
                            }
                        }
                    } else {
                        addMessageToChat('Error: ' + data.message, false);
                    }
                })
                .catch(error => {
                    // Remove loading indicator
                    if (chatMessages.lastChild && chatMessages.lastChild.textContent === 'Thinking...') {
                        chatMessages.removeChild(chatMessages.lastChild);
                    }
                    
                    // Show error
                    addMessageToChat('An error occurred while processing your request. Please try again.', false);
                    console.error('Error:', error);
                });
            }
        });
    </script>
</x-app-layout>
