<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $jobDescription->title }}
                </h2>
                <p class="text-gray-600 mt-1">
                    Project: <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">{{ $project->title }}</a>
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('job-descriptions.edit', $jobDescription) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Edit Job Description
                </a>
                <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Project
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

            <!-- Job Description Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $jobDescription->title }}</h3>
                            <p class="text-sm text-gray-600">
                                Project: <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">{{ $project->title }}</a> |
                                Version {{ $jobDescription->version }} |
                                Status: <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $jobDescription->status === 'published' ? 'bg-green-100 text-green-800' :
                                       ($jobDescription->status === 'approved' ? 'bg-blue-100 text-blue-800' :
                                       ($jobDescription->status === 'review' ? 'bg-yellow-100 text-yellow-800' :
                                       'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($jobDescription->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded flex items-center">
                                    <span>Actions</span>
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                    <div class="py-1">
                                        <form action="{{ route('job-descriptions.create_version', $jobDescription) }}" method="POST" class="block">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Create New Version</button>
                                        </form>
                                        <a href="{{ route('job-descriptions.export', ['jobDescription' => $jobDescription, 'format' => 'pdf']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as PDF</a>
                                        <a href="{{ route('job-descriptions.export', ['jobDescription' => $jobDescription, 'format' => 'docx']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as Word</a>
                                        <a href="{{ route('job-descriptions.export', ['jobDescription' => $jobDescription, 'format' => 'txt']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Export as Text</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Industry</p>
                            <p class="font-medium">{{ $jobDescription->industry ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Experience Level</p>
                            <p class="font-medium">{{ $jobDescription->experience_level ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Employment Type</p>
                            <p class="font-medium">{{ $jobDescription->employment_type ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Location</p>
                            <p class="font-medium">{{ $jobDescription->location ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Compensation Range</p>
                            <p class="font-medium">{{ $jobDescription->compensation_range ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($jobDescription->generated_at)
                    <div class="mt-2 text-sm text-gray-600">
                        <p>Generated on {{ $jobDescription->generated_at->format('M d, Y') }} using {{ $jobDescription->ai_provider }} ({{ $jobDescription->ai_model }})</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Job Description Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Overview</h3>
                        <div class="prose max-w-none">
                            {!! nl2br(e($jobDescription->overview ?? 'No overview provided.')) !!}
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Responsibilities</h3>
                        <div class="prose max-w-none">
                            {!! nl2br(e($jobDescription->responsibilities ?? 'No responsibilities provided.')) !!}
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Requirements (Non-Negotiable)</h3>
                        <div class="prose max-w-none">
                            {!! nl2br(e($jobDescription->requirements_non_negotiable ?? 'No non-negotiable requirements provided.')) !!}
                        </div>
                    </div>

                    @if($jobDescription->requirements_preferred)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Requirements (Preferred)</h3>
                        <div class="prose max-w-none">
                            {!! nl2br(e($jobDescription->requirements_preferred)) !!}
                        </div>
                    </div>
                    @endif

                    @if($jobDescription->benefits)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Benefits</h3>
                        <div class="prose max-w-none">
                            {!! nl2br(e($jobDescription->benefits)) !!}
                        </div>
                    </div>
                    @endif

                    @if($jobDescription->disclaimer)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Disclaimer</h3>
                        <div class="prose max-w-none">
                            {!! nl2br(e($jobDescription->disclaimer)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Qualifying Questions -->
            @if(count($qualifyingQuestions) > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Qualifying Questions</h3>
                    
                    <div class="space-y-4">
                        @foreach($qualifyingQuestions as $question)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium">{{ $question->question }}</h4>
                                    @if($question->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ $question->description }}</p>
                                    @endif
                                </div>
                                <div>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $question->is_knockout ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $question->is_knockout ? 'Knockout' : 'Screening' }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">Type: {{ ucfirst($question->type) }}</p>
                                
                                @if($question->isMultipleChoice() && $question->options)
                                <div class="mt-2">
                                    <p class="text-sm font-medium">Options:</p>
                                    <ul class="list-disc list-inside text-sm ml-2">
                                        @foreach($question->getOptionsArray() as $option)
                                        <li>{{ $option }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif
                                
                                @if($question->is_knockout && $question->correct_answer)
                                <div class="mt-2">
                                    <p class="text-sm font-medium">Correct Answer: <span class="text-green-600">{{ $question->correct_answer }}</span></p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>