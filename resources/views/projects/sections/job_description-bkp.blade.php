<!-- Job Description -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 text-gray-900">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Job Description</h3>
            <div class="flex space-x-2">
                <a href="{{ route('job-descriptions.index', ['project_id' => $project->id]) }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                    Manage Job Descriptions
                </a>
                <a href="{{ route('job-descriptions.create', ['project_id' => $project->id]) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Create New
                </a>
            </div>
        </div>
        
        @if($latestJobDescription)
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <h4 class="font-medium text-lg">{{ $latestJobDescription->title }}</h4>
                    <p class="text-sm text-gray-600">
                        Version {{ $latestJobDescription->version }} |
                        <span class="px-2 py-1 rounded-full text-xs font-semibold
                            {{ $latestJobDescription->status === 'published' ? 'bg-green-100 text-green-800' :
                               ($latestJobDescription->status === 'approved' ? 'bg-blue-100 text-blue-800' :
                               ($latestJobDescription->status === 'review' ? 'bg-yellow-100 text-yellow-800' :
                               'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($latestJobDescription->status) }}
                        </span>
                    </p>
                </div>
                <div>
                    <a href="{{ route('job-descriptions.show', $latestJobDescription) }}" class="text-indigo-600 hover:text-indigo-900">
                        View Details
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Industry</p>
                    <p class="font-medium">{{ $latestJobDescription->industry ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Experience Level</p>
                    <p class="font-medium">{{ ucfirst($latestJobDescription->experience_level ?? 'N/A') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Employment Type</p>
                    <p class="font-medium">{{ ucfirst($latestJobDescription->employment_type ?? 'N/A') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Location</p>
                    <p class="font-medium">{{ $latestJobDescription->location ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Compensation Range</p>
                    <p class="font-medium">{{ $latestJobDescription->compensation_range ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-sm text-gray-600">Overview</p>
                <p class="mt-1">{{ $latestJobDescription->overview ?? 'No overview provided.' }}</p>
            </div>

            <div class="mt-4">
                <p class="text-sm text-gray-600">Responsibilities</p>
                <p class="mt-1">{{ $latestJobDescription->responsibilities ?? 'No responsibilities provided.' }}</p>
            </div>

            <div class="mt-4">
                <p class="text-sm text-gray-600">Non-Negotiable Requirements</p>
                <p class="mt-1">{{ $latestJobDescription->requirements_non_negotiable ?? 'No non-negotiable requirements provided.' }}</p>
            </div>

            @if($latestJobDescription->requirements_preferred)
                <div class="mt-4">
                    <p class="text-sm text-gray-600">Preferred Requirements</p>
                    <p class="mt-1">{{ $latestJobDescription->requirements_preferred }}</p>
                </div>
            @endif

            @if($latestJobDescription->benefits)
                <div class="mt-4">
                    <p class="text-sm text-gray-600">Benefits</p>
                    <p class="mt-1">{{ $latestJobDescription->benefits }}</p>
                </div>
            @endif

            @if($latestJobDescription->disclaimer)
                <div class="mt-4">
                    <p class="text-sm text-gray-600">Disclaimer</p>
                    <p class="mt-1">{{ $latestJobDescription->disclaimer }}</p>
                </div>
            @endif

            @if($latestJobDescription->generated_at)
                <div class="mt-4 text-sm text-gray-600">
                    <p>Generated on {{ $latestJobDescription->generated_at->format('M d, Y') }} using {{ $latestJobDescription->ai_provider }} ({{ $latestJobDescription->ai_model }})</p>
                </div>
            @endif
        @else
            <div class="bg-gray-50 p-6 rounded-lg text-center">
                <p class="text-gray-600">No job description has been created for this project yet.</p>
                <a href="{{ route('job-descriptions.create', ['project_id' => $project->id]) }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Create Job Description
                </a>
            </div>
        @endif
    </div>
</div>
