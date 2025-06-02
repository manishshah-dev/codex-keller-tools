<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Workable Jobs') }}
            </h2>
            <a href="{{ route('workable-jobs.fetch') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Fetch Jobs</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 text-left">Title</th>
                                <th class="py-2 px-4 text-left">Department</th>
                                <th class="py-2 px-4 text-left">Country</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobs as $job)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 border-b">{{ $job->title }}</td>
                                    <td class="py-2 px-4 border-b">{{ $job->department }}</td>
                                    <td class="py-2 px-4 border-b">{{ $job->country }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $jobs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
