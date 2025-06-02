<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Workable Jobs') }}
            </h2>
            <form action="{{ route('workable-jobs.fetch') }}" method="POST">
                @csrf
                <x-primary-button>{{ __('Fetch Jobs') }}</x-primary-button>
            </form>
        </div>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto">
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($jobs->isEmpty())
                        <p class="text-gray-600">No jobs found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-2 px-4 text-left">Title</th>
                                        <th class="py-2 px-4 text-left">Department</th>
                                        <th class="py-2 px-4 text-left">Location</th>
                                        <th class="py-2 px-4 text-left">State</th>
                                        <th class="py-2 px-4 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jobs as $job)
                                        <tr>
                                            <td class="py-2 px-4">{{ $job->title }}</td>
                                            <td class="py-2 px-4">{{ $job->department }}</td>
                                            <td class="py-2 px-4">{{ $job->location }}</td>
                                            <td class="py-2 px-4">{{ $job->state }}</td>
                                            <td class="py-2 px-4">
                                                @if($job->url)
                                                    <a href="{{ $job->url }}" target="_blank" class="text-blue-600 hover:text-blue-900">View</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
