<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Trash') }}
            </h2>
            @if(Auth::user()->is_admin)
            <div>
                <form action="{{ route('trash.purge') }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete all trash items older than 30 days? This action cannot be undone.')">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                        Purge Old Items (>30 days)
                    </button>
                </form>
            </div>
            @endif
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">
                    <strong>Note:</strong> Items in trash will be automatically and permanently deleted after 30 days.
                    You can restore items before that time using the "Restore" button.
                </span>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <button class="tab-button whitespace-nowrap py-4 px-6 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600 focus:outline-none" data-tab="projects">
                    Projects ({{ count($deletedProjects) }})
                </button>
                <button class="tab-button whitespace-nowrap py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none" data-tab="job-descriptions">
                    Job Descriptions ({{ count($deletedJobDescriptions) }})
                </button>
                <button class="tab-button whitespace-nowrap py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none" data-tab="candidates">
                    Candidates ({{ count($deletedCandidates) }})
                </button>
                <button class="tab-button whitespace-nowrap py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none" data-tab="templates">
                    JD Templates ({{ count($deletedTemplates) }})
                </button>
                <button class="tab-button whitespace-nowrap py-4 px-6 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none" data-tab="questions">
                    Qualifying Questions ({{ count($deletedQuestions) }})
                </button>
            </nav>
        </div>

        <!-- Projects Tab -->
        <div id="projects-tab" class="tab-content p-6">
            @if($deletedProjects->isEmpty())
                <p class="text-gray-500 text-center py-4">No deleted projects found.</p>
            @else
                <form action="{{ route('trash.projects.bulk-destroy') }}" method="POST">
                    @csrf
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Deleted Projects</h3>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm"
                                onclick="return confirm('Are you sure you want to permanently delete all selected projects? This action cannot be undone.')">
                            Delete Selected
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left">
                                        <input type="checkbox" id="select-all-projects" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($deletedProjects as $project)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <input type="checkbox" name="selected_projects[]" value="{{ $project->id }}" class="project-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $project->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $project->department ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $project->deleted_at->format('M d, Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <form action="{{ route('trash.projects.restore', $project->id) }}" method="POST" class="inline restore-form">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">Restore</button>
                                                </form>
                                                <form action="{{ route('trash.projects.destroy', $project->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to permanently delete this project? This action cannot be undone.')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>

        <!-- Job Descriptions Tab -->
        <div id="job-descriptions-tab" class="tab-content p-6 hidden">
            @if($deletedJobDescriptions->isEmpty())
                <p class="text-gray-500 text-center py-4">No deleted job descriptions found.</p>
            @else
                <form action="{{ route('trash.job-descriptions.bulk-destroy') }}" method="POST">
                    @csrf
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Deleted Job Descriptions</h3>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm"
                                onclick="return confirm('Are you sure you want to permanently delete all selected job descriptions? This action cannot be undone.')">
                            Delete Selected
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3">
                                        <input type="checkbox" id="select-all-job-descriptions" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($deletedJobDescriptions as $jobDescription)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <input type="checkbox" name="selected_job_descriptions[]" value="{{ $jobDescription->id }}" class="job-description-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $jobDescription->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($jobDescription->project)
                                                {{ $jobDescription->project->title }}
                                                @if($jobDescription->project->trashed())
                                                    <span class="text-red-500">(deleted)</span>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $jobDescription->deleted_at->format('M d, Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <form action="{{ route('trash.job-descriptions.restore', $jobDescription->id) }}" method="POST" class="inline restore-form">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">Restore</button>
                                                </form>
                                                <form action="{{ route('trash.job-descriptions.destroy', $jobDescription->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to permanently delete this job description? This action cannot be undone.')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>

        <!-- Candidates Tab -->
        <div id="candidates-tab" class="tab-content p-6 hidden">
            @if($deletedCandidates->isEmpty())
                <p class="text-gray-500 text-center py-4">No deleted candidates found.</p>
            @else
                <form action="{{ route('trash.candidates.bulk-destroy') }}" method="POST">
                    @csrf
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Deleted Candidates</h3>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm"
                                onclick="return confirm('Are you sure you want to permanently delete all selected candidates? This action cannot be undone.')">
                            Delete Selected
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3">
                                        <input type="checkbox" id="select-all-candidates" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($deletedCandidates as $candidate)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <input type="checkbox" name="selected_candidates[]" value="{{ $candidate->id }}" class="candidate-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $candidate->first_name }} {{ $candidate->last_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($candidate->project)
                                                {{ $candidate->project->title }}
                                                @if($candidate->project->trashed())
                                                    <span class="text-red-500">(deleted)</span>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $candidate->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $candidate->deleted_at->format('M d, Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <form action="{{ route('trash.candidates.restore', $candidate->id) }}" method="POST" class="inline restore-form">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">Restore</button>
                                                </form>
                                                <form action="{{ route('trash.candidates.destroy', $candidate->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to permanently delete this candidate? This action cannot be undone.')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>

        <!-- JD Templates Tab -->
        <div id="templates-tab" class="tab-content p-6 hidden">
            @if($deletedTemplates->isEmpty())
                <p class="text-gray-500 text-center py-4">No deleted job description templates found.</p>
            @else
                <form action="{{ route('trash.templates.bulk-destroy') }}" method="POST">
                    @csrf
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Deleted Job Description Templates</h3>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm"
                                onclick="return confirm('Are you sure you want to permanently delete all selected templates? This action cannot be undone.')">
                            Delete Selected
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3">
                                        <input type="checkbox" id="select-all-templates" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Industry</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($deletedTemplates as $template)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <input type="checkbox" name="selected_templates[]" value="{{ $template->id }}" class="template-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $template->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->industry ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->deleted_at->format('M d, Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <form action="{{ route('trash.templates.restore', $template->id) }}" method="POST" class="inline restore-form">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">Restore</button>
                                                </form>
                                                <form action="{{ route('trash.templates.destroy', $template->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to permanently delete this template? This action cannot be undone.')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>

        <!-- Qualifying Questions Tab -->
        <div id="questions-tab" class="tab-content p-6 hidden">
            @if($deletedQuestions->isEmpty())
                <p class="text-gray-500 text-center py-4">No deleted qualifying questions found.</p>
            @else
                <form action="{{ route('trash.questions.bulk-destroy') }}" method="POST">
                    @csrf
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold">Deleted Qualifying Questions</h3>
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm"
                                onclick="return confirm('Are you sure you want to permanently delete all selected questions? This action cannot be undone.')">
                            Delete Selected
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3">
                                        <input type="checkbox" id="select-all-questions" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Question</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($deletedQuestions as $question)
                                    <tr>
                                        <td class="px-4 py-4">
                                            <input type="checkbox" name="selected_questions[]" value="{{ $question->id }}" class="question-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $question->question }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $question->type ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $question->deleted_at->format('M d, Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-3">
                                                <form action="{{ route('trash.questions.restore', $question->id) }}" method="POST" class="inline restore-form">
                                                    @csrf
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">Restore</button>
                                                </form>
                                                <form action="{{ route('trash.questions.destroy', $question->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to permanently delete this question? This action cannot be undone.')">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Add CSRF token meta tag if it doesn't exist -->
@if(!request()->header('x-inertia'))
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        // Tab switching functionality
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-indigo-500', 'text-indigo-600');
                    btn.classList.add('border-transparent', 'text-gray-500');
                });
                
                // Add active class to clicked button
                button.classList.remove('border-transparent', 'text-gray-500');
                button.classList.add('border-indigo-500', 'text-indigo-600');
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Show the selected tab content
                const tabId = button.getAttribute('data-tab');
                document.getElementById(`${tabId}-tab`).classList.remove('hidden');
            });
        });
        
        // Select All checkboxes functionality
        function setupSelectAll(selectAllId, checkboxClass) {
            const selectAllCheckbox = document.getElementById(selectAllId);
            if (!selectAllCheckbox) return;
            
            const checkboxes = document.querySelectorAll(`.${checkboxClass}`);
            
            // When "Select All" checkbox is clicked
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
            
            // When individual checkboxes are clicked
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // If any checkbox is unchecked, uncheck "Select All"
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    }
                    // If all checkboxes are checked, check "Select All"
                    else {
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        selectAllCheckbox.checked = allChecked;
                    }
                });
            });
        }
        
        // Setup Select All for each tab
        setupSelectAll('select-all-projects', 'project-checkbox');
        setupSelectAll('select-all-job-descriptions', 'job-description-checkbox');
        setupSelectAll('select-all-candidates', 'candidate-checkbox');
        setupSelectAll('select-all-templates', 'template-checkbox');
        setupSelectAll('select-all-questions', 'question-checkbox');
        
        // Check and fix any restore/delete button issues
        function checkAndFixRestoreForms() {
            // Find all action cells (the last column in each row)
            const actionCells = document.querySelectorAll('td:last-child');
            
            actionCells.forEach(cell => {
                const flexContainer = cell.querySelector('.flex.space-x-3');
                if (!flexContainer) return;
                
                // Get the row and item ID
                const row = cell.closest('tr');
                const checkbox = row.querySelector('input[type="checkbox"]');
                
                if (!checkbox) return;
                
                const itemId = checkbox.value;
                const itemType = checkbox.name.replace('selected_', '').replace('[]', '');
                
                // Check if we have both restore and delete buttons
                const restoreButton = cell.querySelector('button.text-indigo-600.hover\\:text-indigo-900');
                const deleteButton = cell.querySelector('button.text-red-600.hover\\:text-red-900');
                
                // Create route paths based on the item type
                let restorePath = '';
                let deletePath = '';
                
                if (itemType === 'projects') {
                    restorePath = `/trash/projects/${itemId}/restore`;
                    deletePath = `/trash/projects/${itemId}`;
                } else if (itemType === 'job_descriptions') {
                    restorePath = `/trash/job-descriptions/${itemId}/restore`;
                    deletePath = `/trash/job-descriptions/${itemId}`;
                } else if (itemType === 'candidates') {
                    restorePath = `/trash/candidates/${itemId}/restore`;
                    deletePath = `/trash/candidates/${itemId}`;
                } else if (itemType === 'templates') {
                    restorePath = `/trash/templates/${itemId}/restore`;
                    deletePath = `/trash/templates/${itemId}`;
                } else if (itemType === 'questions') {
                    restorePath = `/trash/questions/${itemId}/restore`;
                    deletePath = `/trash/questions/${itemId}`;
                }
                
                // Clear the flex container to rebuild it properly
                while (flexContainer.firstChild) {
                    flexContainer.removeChild(flexContainer.firstChild);
                }
                
                // Create and add the restore form
                const restoreForm = document.createElement('form');
                restoreForm.action = restorePath;
                restoreForm.method = 'POST';
                restoreForm.className = 'inline restore-form';
                
                // Create CSRF token input for restore form
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const restoreCsrfInput = document.createElement('input');
                restoreCsrfInput.type = 'hidden';
                restoreCsrfInput.name = '_token';
                restoreCsrfInput.value = csrfToken;
                restoreForm.appendChild(restoreCsrfInput);
                
                // Create or use existing restore button
                let newRestoreButton;
                if (restoreButton) {
                    newRestoreButton = restoreButton.cloneNode(true);
                } else {
                    newRestoreButton = document.createElement('button');
                    newRestoreButton.type = 'submit';
                    newRestoreButton.className = 'text-indigo-600 hover:text-indigo-900';
                    newRestoreButton.textContent = 'Restore';
                }
                restoreForm.appendChild(newRestoreButton);
                
                // Add the restore form to the container
                flexContainer.appendChild(restoreForm);
                
                // Create and add the delete form
                const deleteForm = document.createElement('form');
                deleteForm.action = deletePath;
                deleteForm.method = 'POST';
                deleteForm.className = 'inline';
                
                // Create CSRF token input for delete form
                const deleteCsrfInput = document.createElement('input');
                deleteCsrfInput.type = 'hidden';
                deleteCsrfInput.name = '_token';
                deleteCsrfInput.value = csrfToken;
                deleteForm.appendChild(deleteCsrfInput);
                
                // Create method input for delete form
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                deleteForm.appendChild(methodInput);
                
                // Create or use existing delete button
                let newDeleteButton;
                if (deleteButton) {
                    newDeleteButton = deleteButton.cloneNode(true);
                } else {
                    newDeleteButton = document.createElement('button');
                    newDeleteButton.type = 'submit';
                    newDeleteButton.className = 'text-red-600 hover:text-red-900';
                    newDeleteButton.textContent = 'Delete';
                    newDeleteButton.setAttribute('onclick', "return confirm('Are you sure you want to permanently delete this item? This action cannot be undone.')");
                }
                deleteForm.appendChild(newDeleteButton);
                
                // Add the delete form to the container
                flexContainer.appendChild(deleteForm);
                
            });
        }
        
        // Run the fix on page load
        checkAndFixRestoreForms();
    });
</script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>