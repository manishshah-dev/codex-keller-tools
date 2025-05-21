<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->title }}
            </h2>
            <div>
                <a href="{{ route('projects.edit', $project) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded mr-2">
                    Edit Project
                </a>
                <a href="{{ route('projects.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Projects
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

            <!-- Basic Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Department</p>
                            <p class="font-medium">{{ $project->department ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Location</p>
                            <p class="font-medium">{{ $project->location ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $project->status === 'active' ? 'bg-green-100 text-green-800' :
                                       ($project->status === 'completed' ? 'bg-blue-100 text-blue-800' :
                                       'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Created</p>
                            <p class="font-medium">{{ $project->created_at->format('M d, Y') }}</p>
                        </div>
                        @if($project->ai_processing_status)
                        <div>
                            <p class="text-sm text-gray-600">AI Processing Status</p>
                            <p>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $project->ai_processing_status === 'completed' ? 'bg-green-100 text-green-800' :
                                        ($project->ai_processing_status === 'processing' ? 'bg-blue-100 text-blue-800' :
                                        (str_contains($project->ai_processing_status, 'failed') ? 'bg-red-100 text-red-800' :
                                        ($project->ai_processing_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                        'bg-yellow-100 text-yellow-800'))) }}">
                                    {{ ucfirst($project->ai_processing_status) }}
                                </span>
                            </p>
                        </div>
                        @endif
                    </div>
                    
                    @if($project->description)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Description</p>
                            <p class="mt-1">{{ $project->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Intake Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Intake Form</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Job Title</p>
                            <p class="font-medium">{{ $project->job_title ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Experience Level</p>
                            <p class="font-medium">{{ $project->experience_level ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Employment Type</p>
                            <p class="font-medium">{{ $project->employment_type ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Education Requirements</p>
                            <p class="font-medium">{{ $project->education_requirements ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Salary Range</p>
                            <p class="font-medium">{{ $project->salary_range ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($project->required_skills)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Required Skills</p>
                            <p class="mt-1">{{ $project->required_skills }}</p>
                        </div>
                    @endif

                    @if($project->preferred_skills)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Preferred Skills</p>
                            <p class="mt-1">{{ $project->preferred_skills }}</p>
                        </div>
                    @endif

                    @if($project->additional_notes)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Additional Notes</p>
                            <p class="mt-1">{{ $project->additional_notes }}</p>
                        </div>
                    @endif

                    @if($project->claap_transcript)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Claap Transcript</p>
                            <p class="mt-1">{{ substr($project->claap_transcript, 0, 1000) }}...</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Company Research -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Company Research</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Company Name</p>
                            <p class="font-medium">{{ $project->company_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Founding Date</p>
                            <p class="font-medium">{{ $project->founding_date ? $project->founding_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Company Size</p>
                            <p class="font-medium">{{ $project->company_size ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Annual Turnover</p>
                            <p class="font-medium">{{ $project->turnover ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <p class="text-sm text-gray-600">LinkedIn URL</p>
                            <p class="font-medium">
                                @if($project->linkedin_url)
                                    <a href="{{ $project->linkedin_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $project->linkedin_url }}</a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Website URL</p>
                            <p class="font-medium">
                                @if($project->website_url)
                                    <a href="{{ $project->website_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $project->website_url }}</a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($project->competitors)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Competitors</p>
                            <p class="mt-1">{{ $project->competitors }}</p>
                        </div>
                    @endif

                    @if($project->industry_details)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Industry Details</p>
                            <p class="mt-1">{{ $project->industry_details }}</p>
                        </div>
                    @endif

                    @if($project->typical_clients)
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">Typical Clients</p>
                            <p class="mt-1">{{ $project->typical_clients }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Include the modular sections -->
            @include('projects.sections.job_description')
            @include('projects.sections.salary_comparison')
            @include('projects.sections.search_strings')
            @include('projects.sections.keywords')
            @include('projects.sections.ai_questions')
            @include('projects.sections.cv_analyzer')
        </div>
    </div>
</x-app-layout>
