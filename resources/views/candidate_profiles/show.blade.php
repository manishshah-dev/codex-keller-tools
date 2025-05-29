<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $profile->title }}
                </h2>
                <p class="text-gray-600 mt-1">
                    <a href="{{ route('projects.candidates.show', [$project, $candidate]) }}" class="text-indigo-600 hover:text-indigo-900">
                        Back to Candidate
                    </a>
                    <span class="mx-2">|</span>
                    <a href="{{ route('projects.profiles.index', $project) }}" class="text-indigo-600 hover:text-indigo-900">
                        All Profiles
                    </a>
                </p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('projects.candidates.profiles.export', [$project, $candidate, $profile, 'format' => 'pdf']) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd" />
                    </svg>
                    PDF
                </a>
                <a href="{{ route('projects.candidates.profiles.export', [$project, $candidate, $profile, 'format' => 'docx']) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z" clip-rule="evenodd" />
                    </svg>
                    Word
                </a>

                <a href="{{ route('projects.candidates.profiles.submissions.create', [$project, $candidate, $profile]) }}" class="inline-flex items-center px-3 py-2 border border-indigo-600 text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm9 2H8v2H6v2h2v2h2v-2h2V9h-2V7z" />
                    </svg>
                    Submit to Client
                </a>

                @if(!$profile->is_finalized)
                    <a href="{{ route('projects.candidates.profiles.edit', [$project, $candidate, $profile]) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                        Edit
                    </a>
                @endif
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

                    @if(session('warning'))
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('warning') }}</span>
                        </div>
                    @endif

                    <div class="mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $profile->title }}</h1>
                                <div class="mt-1 flex items-center">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $profile->status_badge_class }} mr-2">
                                        {{ ucfirst($profile->status) }}
                                    </span>
                                    @if($profile->is_finalized)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            Finalized {{ $profile->finalized_at->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                <div>Created: {{ $profile->created_at->format('M d, Y') }}</div>
                                <div>Last Updated: {{ $profile->updated_at->format('M d, Y') }}</div>
                                @if($profile->ai_provider && $profile->ai_model)
                                    <div>Generated with: {{ ucfirst($profile->ai_provider) }} / {{ $profile->ai_model }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 prose max-w-none">
                            <h2 class="text-xl font-semibold border-b pb-2">Summary</h2>
                            <div class="mt-4">
                                {!! nl2br(e($profile->summary)) !!}
                            </div>
                        </div>

                        @if($profile->headings && count($profile->formatted_headings) > 0)
                            <div class="mt-8">
                                <h2 class="text-xl font-semibold border-b pb-2 mb-6">Profile Highlights</h2>
                                @foreach($profile->formatted_headings as $heading)
                                    <div class="mb-6">
                                        <h3 class="text-l font-semibold border-b pb-2">{{ $heading['title'] }}</h2>
                                        <ul class="mt-4 list-disc pl-5 space-y-2">
                                            @foreach($heading['content'] as $bullet)
                                                <li class="text-gray-700">
                                                    {{ $bullet['content'] }}
                                                    @if(isset($bullet['evidence_source']) && $bullet['evidence_source'])
                                                        <span class="text-xs text-gray-500 ml-1">(Source: {{ ucfirst($bullet['evidence_source']) }})</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-8 border-t pt-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-semibold">Candidate Details</h2>
                                <a href="{{ route('candidates.resume.view', $candidate) }}" target="_blank" class="p-2 bg-gray-600 hover:bg-gray-700 text-white rounded">
                                    Download Resume
                                </a>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h3 class="text-lg font-medium">Contact Information</h3>
                                    <dl class="mt-2 space-y-1">
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">Name:</dt>
                                            <dd class="text-sm text-gray-900">{{ $candidate->full_name }}</dd>
                                        </div>
                                        @if($candidate->email)
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">Email:</dt>
                                            <dd class="text-sm text-gray-900">{{ $candidate->email }}</dd>
                                        </div>
                                        @endif
                                        @if($candidate->phone)
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">Phone:</dt>
                                            <dd class="text-sm text-gray-900">{{ $candidate->phone }}</dd>
                                        </div>
                                        @endif
                                        @if($candidate->location)
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">Location:</dt>
                                            <dd class="text-sm text-gray-900">{{ $candidate->location }}</dd>
                                        </div>
                                        @endif
                                        @if($candidate->linkedin_url)
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">LinkedIn:</dt>
                                            <dd class="text-sm text-gray-900">
                                                <a href="{{ $candidate->linkedin_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $candidate->linkedin_url }}
                                                </a>
                                            </dd>
                                        </div>
                                        @endif
                                    </dl>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium">Professional Information</h3>
                                    <dl class="mt-2 space-y-1">
                                        @if($candidate->current_position)
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">Position:</dt>
                                            <dd class="text-sm text-gray-900">{{ $candidate->current_position }}</dd>
                                        </div>
                                        @endif
                                        @if($candidate->current_company)
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">Company:</dt>
                                            <dd class="text-sm text-gray-900">{{ $candidate->current_company }}</dd>
                                        </div>
                                        @endif
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">Status:</dt>
                                            <dd class="text-sm text-gray-900">{{ ucfirst($candidate->status) }}</dd>
                                        </div>
                                        <div class="flex">
                                            <dt class="w-32 text-sm font-medium text-gray-500">Match Score:</dt>
                                            <dd class="text-sm text-gray-900">{{ $candidate->match_score_percentage }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        @if($profile->extracted_data)
                            <div class="mt-8 border-t pt-6">
                                <h2 class="text-xl font-semibold">Resume Insights</h2>
                                <div class="mt-4 space-y-6">
                                    @if(!empty($profile->extracted_data['education']))
                                        <div>
                                            <h3 class="text-lg font-medium">Education</h3>
                                            <ul class="mt-2 space-y-2 list-disc pl-5 text-sm">
                                                @foreach($profile->extracted_data['education'] as $edu)
                                                    <li>
                                                        <span class="font-medium">{{ $edu['degree'] ?? '' }}</span>
                                                        @if(!empty($edu['institution']))
                                                            , {{ $edu['institution'] }}
                                                        @endif
                                                        @if(!empty($edu['date_range']))
                                                            <span class="text-gray-500"> ({{ $edu['date_range'] }})</span>
                                                        @endif
                                                        @if(!empty($edu['highlights']))
                                                            <span class="text-gray-500"> - {{ $edu['highlights'] }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if(!empty($profile->extracted_data['experience']))
                                        <div>
                                            <h3 class="text-lg font-medium">Experience</h3>
                                            <ul class="mt-2 space-y-2 list-disc pl-5 text-sm">
                                                @foreach($profile->extracted_data['experience'] as $exp)
                                                    <li>
                                                        <span class="font-medium">{{ $exp['title'] ?? '' }}</span>
                                                        @if(!empty($exp['company']))
                                                            at {{ $exp['company'] }}
                                                        @endif
                                                        @if(!empty($exp['date_range']))
                                                            <span class="text-gray-500"> ({{ $exp['date_range'] }})</span>
                                                        @endif
                                                        @if(!empty($exp['responsibilities']))
                                                            <ul class="list-disc pl-5 mt-1">
                                                                @foreach($exp['responsibilities'] as $resp)
                                                                    <li>{{ $resp }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                        @if(!empty($exp['achievements']))
                                                            <ul class="list-disc pl-5 mt-1">
                                                                @foreach($exp['achievements'] as $ach)
                                                                    <li>{{ $ach }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if(!empty($profile->extracted_data['skills']))
                                        <div>
                                            <h3 class="text-lg font-medium">Skills</h3>
                                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                @if (is_array($profile->extracted_data['skills']))
                                                    @foreach($profile->extracted_data['skills'] as $category => $skills)
                                                        @if(!empty($skills))
                                                            <div>
                                                                <h4 class="font-semibold capitalize">{{ str_replace('_', ' ', $category) }}</h4>
                                                                <ul class="list-disc pl-5 space-y-1 mt-1">
                                                                    @php
                                                                        $skills = is_array($skills) ? $skills : explode(',', $skills);
                                                                    @endphp    

                                                                    @foreach($skills as $skill)
                                                                        <li>{{ $skill }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <div>
                                                        <h4 class="font-semibold">Skills</h4>
                                                        <ul class="list-disc pl-5 space-y-1 mt-1">
                                                            @foreach($profile->extracted_data['skills'] as $skill)
                                                                <li>{{ $skill }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($profile->extracted_data['additional_info']))
                                        <div>
                                            <h3 class="text-lg font-medium">Additional Information</h3>
                                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                                @foreach($profile->extracted_data['additional_info'] as $category => $items)
                                                    @if(!empty($items))
                                                        <div>
                                                            <h4 class="font-semibold capitalize">{{ str_replace('_', ' ', $category) }}</h4>
                                                            <ul class="list-disc pl-5 space-y-1 mt-1">
                                                                @php
                                                                    $items = is_array($items) ? $items : explode(',', $items);
                                                                @endphp
                                                                @foreach($items as $item)
                                                                    <li>{{ $item }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif


                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>