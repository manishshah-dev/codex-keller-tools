<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('All Candidates') }}
            </h2>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Candidates</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('candidates.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                                Refresh
                            </a>
                        </div>
                    </div>

                    @if(count($candidates) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-2 px-4 border-b text-left">Name</th>
                                        <th class="py-2 px-4 border-b text-left">Project</th>
                                        <th class="py-2 px-4 border-b text-left">Current Position</th>
                                        <th class="py-2 px-4 border-b text-left">Location</th>
                                        <th class="py-2 px-4 border-b text-left">Match Score</th>
                                        <th class="py-2 px-4 border-b text-left">Status</th>
                                        <th class="py-2 px-4 border-b text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($candidates as $candidate)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-4 border-b">
                                                <a href="{{ route('projects.candidates.show', [$candidate->project, $candidate]) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $candidate->full_name }}
                                                </a>
                                            </td>
                                            <td class="py-2 px-4 border-b">
                                                <a href="{{ route('projects.show', $candidate->project) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    {{ $candidate->project->title }}
                                                </a>
                                            </td>
                                            <td class="py-2 px-4 border-b">{{ $candidate->current_position ?? 'N/A' }}</td>
                                            <td class="py-2 px-4 border-b">{{ $candidate->location ?? 'N/A' }}</td>
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
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('projects.candidates.show', [$candidate->project, $candidate]) }}" class="text-blue-500 hover:text-blue-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </a>
                                                    <a href="{{ route('projects.candidates.edit', [$candidate->project, $candidate]) }}" class="text-yellow-500 hover:text-yellow-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                        </svg>
                                                    </a>
                                                    <form action="{{ route('projects.candidates.destroy', [$candidate->project, $candidate]) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this candidate?')">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $candidates->links() }}
                        </div>
                    @else
                        <div class="bg-gray-50 p-4 rounded text-center">
                            <p>No candidates found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>