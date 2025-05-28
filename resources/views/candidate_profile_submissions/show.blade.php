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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Candidate</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($submissions as $submission)
                                    <tr>
                                        <td class="px-4 py-2">{{ $submission->client_email }}</td>
                                        <td class="px-4 py-2">{{ $submission->subject }}</td>
                                        <td class="px-4 py-2">{{ $submission->user->name }}</td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('projects.candidates.profiles.show', [$submission->project_id, $submission->candidate_id, $submission->candidate_profile_id]) }}" class="text-blue-600 hover:underline">
                                                {{ $submission->candidate->full_name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="local-datetime" data-datetime="{{ $submission->created_at->toIso8601String() }}">
                                                {{ $submission->created_at->format('M d, Y H:i') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $submissions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
    document.querySelectorAll('.local-datetime').forEach(function(el) {
        const utcDate = el.getAttribute('data-datetime');
        if (utcDate) {
            const localDate = new Date(utcDate);
            el.innerHTML = localDate.toLocaleString();
        }
    });
</script>
</x-app-layout>