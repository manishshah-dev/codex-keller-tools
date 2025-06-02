<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Workable Jobs') }}
            </h2>
            <div class="flex space-x-2">
                <form action="{{ route('workable-jobs.fetch') }}" method="POST">
                    @csrf
                    <x-primary-button>{{ __('Import Jobs') }}</x-primary-button>
                </form>
                <form action="{{ route('workable-jobs.import-candidates') }}" method="POST">
                    @csrf
                    <input type="hidden" name="jobs" id="selected-jobs" />
                    <x-primary-button>{{ __('Import Candidates') }}</x-primary-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
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
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="py-2 px-4 text-left">Select</th>
                                    <th class="py-2 px-4 text-left">Title</th>
                                    <th class="py-2 px-4 text-left">Shortcode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobs as $job)
                                    <tr>
                                        <td class="py-2 px-4">
                                            <input type="checkbox" class="job-checkbox" value="{{ $job->id }}">
                                        </td>
                                        <td class="py-2 px-4">{{ $job->title }}</td>
                                        <td class="py-2 px-4">{{ $job->shortcode }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p>No jobs imported.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form[action="{{ route('workable-jobs.import-candidates') }}"]');
            form.addEventListener('submit', e => {
                const selected = Array.from(document.querySelectorAll('.job-checkbox:checked')).map(cb => cb.value);
                document.getElementById('selected-jobs').value = JSON.stringify(selected);
            });
        });
    </script>
</x-app-layout>
