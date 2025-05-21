<!-- Job Description Section - Displays job description data from the projects table -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 text-gray-900">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Job Description (Client)</h3>
            <div class="flex space-x-2">
                <span class="px-2 py-1 rounded-full text-xs font-semibold
                    {{ $project->jd_status === 'published' ? 'bg-green-100 text-green-800' :
                       ($project->jd_status === 'approved' ? 'bg-blue-100 text-blue-800' :
                       ($project->jd_status === 'review' ? 'bg-yellow-100 text-yellow-800' :
                       'bg-gray-100 text-gray-800')) }}">
                    <!-- Status: {{ ucfirst($project->jd_status ?? 'draft') }} -->
                </span>
                <!-- Optional: Add edit button for these fields if needed -->
                <!-- <a href="{{-- route('projects.edit', $project) --}}#job-description-section" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Edit JD Data</a> -->
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <p class="text-sm text-gray-600">Job Title</p>
                <p class="font-medium">{{ $project->job_title ?? $project->title }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Compensation Range</p>
                <p class="font-medium">{{ $project->compensation_range ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Employment Type</p>
                <p class="font-medium">{{ $project->employment_type ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <p class="text-sm text-gray-600">Experience Level</p>
                <p class="font-medium">{{ $project->experience_level ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Education Requirements</p>
                <p class="font-medium">{{ $project->education_requirements ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Location</p>
                <p class="font-medium">{{ $project->location ?? 'N/A' }}</p>
            </div>
        </div>

        @if($project->overview)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Overview</p>
                <p class="mt-1 whitespace-pre-wrap">{{ $project->overview }}</p>
            </div>
        @endif

        @if($project->required_skills)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Required Skills</p>
                <p class="mt-1 whitespace-pre-wrap">{{ $project->required_skills }}</p>
            </div>
        @endif

        @if($project->preferred_skills)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Preferred Skills</p>
                <p class="mt-1 whitespace-pre-wrap">{{ $project->preferred_skills }}</p>
            </div>
        @endif

        @if($project->responsibilities)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Responsibilities</p>
                <p class="mt-1 whitespace-pre-wrap">{{ $project->responsibilities }}</p>
            </div>
        @endif

        @if($project->requirements_non_negotiable)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Non-Negotiable Requirements</p>
                <p class="mt-1 whitespace-pre-wrap">{{ $project->requirements_non_negotiable }}</p>
            </div>
        @endif

        @if($project->requirements_preferred)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Preferred Requirements</p>
                <p class="mt-1 whitespace-pre-wrap">{{ $project->requirements_preferred }}</p>
            </div>
        @endif

        @if($project->benefits)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Benefits</p>
                <p class="mt-1 whitespace-pre-wrap">{{ $project->benefits }}</p>
            </div>
        @endif

        @if($project->disclaimer)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Disclaimer</p>
                <p class="mt-1 whitespace-pre-wrap">{{ $project->disclaimer }}</p>
            </div>
        @endif

        @if($project->jd_file_path)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Job Description File</p>
                <p class="mt-1">
                    <a href="{{ asset('storage/' . $project->jd_file_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                        <span class="inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            View Job Description File
                        </span>
                    </a>
                </p>
            </div>
        @endif

        @if(!$project->overview && !$project->responsibilities && !$project->requirements_non_negotiable &&
            !$project->required_skills && !$project->preferred_skills && !$project->benefits && !$project->disclaimer)
             <div class="bg-gray-50 p-6 rounded-lg text-center">
                <p class="text-gray-600">No job description details found in the project data.</p>
                <a href="{{ route('projects.edit', $project) }}#job-description-section" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Add Job Description Details</a>
            </div>
        @endif

        <hr class="my-6">

        <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h4 class="text-md font-semibold">Job Description Generator</h4>
                    <p class="text-sm text-gray-600 mt-1">Create and manage alternative versions of this job description</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('job-descriptions.index', ['project_id' => $project->id]) }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded text-sm">
                        View All Job Descriptions
                    </a>
                    <a href="{{ route('job-descriptions.create', ['project_id' => $project->id]) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                        Generate New Job Description
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>