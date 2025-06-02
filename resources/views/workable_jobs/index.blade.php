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
                    @if($jobs->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-2 px-4 text-left">Title</th>
                                        <th class="py-2 px-4 text-left">Shortcode</th>
                                        <th class="py-2 px-4 text-left">Department</th>
                                        <th class="py-2 px-4 text-left">Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jobs as $job)
                                        <tr>
                                            <td class="py-2 px-4">{{ $job->full_title ?? $job->title }}</td>
                                            <td class="py-2 px-4">{{ $job->shortcode }}</td>
                                            <td class="py-2 px-4">{{ $job->department }}</td>
                                            <td class="py-2 px-4">{{ optional($job->workable_created_at)->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $jobs->links() }}
                        </div>
                    @else
                        <p class="text-gray-600">No jobs found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
