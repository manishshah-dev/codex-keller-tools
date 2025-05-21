<!-- CV Analyzer Section -->
@if($project->id)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 text-gray-900">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">CV Analyzer</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('projects.candidates.index', $project) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        View Candidates
                    </a>
                    <a href="{{ route('projects.analyzer', $project) }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                        Open CV Analyzer
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium">Candidates</h4>
                        <span class="text-sm text-gray-500">{{ $project->candidates()->count() }}</span>
                    </div>
                    <p class="text-sm text-gray-600">Upload resumes or import from Workable to analyze candidates against job requirements.</p>
                    <div class="mt-4">
                        <a href="{{ route('projects.candidates.create', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            + Add Candidate
                        </a>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium">Requirements</h4>
                        <span class="text-sm text-gray-500">{{ $project->activeRequirements()->count() }}</span>
                    </div>
                    <p class="text-sm text-gray-600">Define and manage job requirements to match candidates effectively.</p>
                    <div class="mt-4">
                        <a href="{{ route('projects.analyzer', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            Manage Requirements
                        </a>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium">AI Chat</h4>
                    </div>
                    <p class="text-sm text-gray-600">Use the interactive chat to refine requirements and analyze candidates.</p>
                    <div class="mt-4">
                        <a href="{{ route('projects.analyzer', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            Start Chat
                        </a>
                    </div>
                </div>
            </div>
            
            @if($project->candidates()->count() > 0)
                <div class="mt-6">
                    <h4 class="font-medium mb-3">Top Candidates</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left">Name</th>
                                    <th class="py-2 px-4 border-b text-left">Current Position</th>
                                    <th class="py-2 px-4 border-b text-left">Match Score</th>
                                    <th class="py-2 px-4 border-b text-left">Status</th>
                                    <th class="py-2 px-4 border-b text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->candidates()->orderByScore()->take(3)->get() as $candidate)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b">
                                            <a href="{{ route('projects.candidates.show', [$project, $candidate]) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{ $candidate->full_name }}
                                            </a>
                                        </td>
                                        <td class="py-2 px-4 border-b">{{ $candidate->current_position ?? 'N/A' }}</td>
                                        <td class="py-2 px-4 border-b">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $candidate->match_score_percentage }}"></div>
                                            </div>
                                            <span class="text-xs">{{ $candidate->match_score_percentage }}</span>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $candidate->status_badge_class }}">
                                                {{ ucfirst($candidate->status) }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 border-b">
                                            <a href="{{ route('projects.candidates.show', [$project, $candidate]) }}" class="text-blue-500 hover:text-blue-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('projects.candidates.index', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            View All Candidates
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif