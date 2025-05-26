<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Submit Profile to Client</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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
                    
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <ul class="list-disc pl-5 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('projects.candidates.profiles.submissions.store', [$project, $candidate, $profile]) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Client Email</label>
                            <input type="email" name="client_email" required class="mt-1 block w-full border-gray-300 rounded-md" />
                            <x-input-error :messages="$errors->get('client_email')" class="mt-2" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Client Name</label>
                            <input type="text" name="client_name" class="mt-1 block w-full border-gray-300 rounded-md" />
                            <x-input-error :messages="$errors->get('client_name')" class="mt-2" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Subject</label>
                            <input type="text" name="subject" class="mt-1 block w-full border-gray-300 rounded-md" />
                            <small class="block text-xs text-gray-500">Note: The default subject line is "Candidate Profile Submission: {candidate_name}".</small>
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea name="message" rows="4" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                            <small class="block text-xs text-gray-500">Note: Candidate complete profile & their resume will be attached to the email.</small>
                            <small class="block text-xs text-gray-500">Add any additional information you would like to include to the top of the email.</small>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>
                        <div class="flex justify-end">
                            <a href="{{ route('projects.candidates.profiles.show', [$project, $candidate, $profile]) }}" class="px-4 py-2 mr-3 bg-gray-200 rounded-md">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>