<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Profile Submissions
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">{{ $profile->title }}</h1>
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if($submissions->isEmpty())
                        <p>No submissions yet.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sent By</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($submissions as $submission)
                                    <tr>
                                        <td class="px-4 py-2">{{ $submission->client_email }}</td>
                                        <td class="px-4 py-2">{{ $submission->subject }}</td>
                                        <td class="px-4 py-2">{{ $submission->user->name }}</td>
                                        <td class="px-4 py-2">{{ $submission->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>