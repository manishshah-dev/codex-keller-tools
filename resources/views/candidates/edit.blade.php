<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Edit Candidate') }}: {{ $candidate->full_name }}
                </h2>
                <p class="text-gray-600 mt-1">
                    Project: <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">{{ $project->title }}</a>
                </p>
            </div>
            <div>
                <a href="{{ route('projects.candidates.show', [$project, $candidate]) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Candidate
                </a>
            </div>
        </div>
    </x-slot>

    <div >
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
                    <form action="{{ route('projects.candidates.update', [$project, $candidate]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Basic Information</h3>
                                
                                <div>
                                    <x-input-label for="first_name" :value="__('First Name')" />
                                    <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name', $candidate->first_name)" required />
                                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="last_name" :value="__('Last Name')" />
                                    <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name', $candidate->last_name)" required />
                                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $candidate->email)" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="phone" :value="__('Phone')" />
                                    <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $candidate->phone)" />
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="location" :value="__('Location')" />
                                    <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $candidate->location)" />
                                    <x-input-error :messages="$errors->get('location')" class="mt-2" />
                                </div>
                            </div>
                            
                            <!-- Professional Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Professional Information</h3>
                                
                                <div>
                                    <x-input-label for="current_company" :value="__('Current Company')" />
                                    <x-text-input id="current_company" class="block mt-1 w-full" type="text" name="current_company" :value="old('current_company', $candidate->current_company)" />
                                    <x-input-error :messages="$errors->get('current_company')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="current_position" :value="__('Current Position')" />
                                    <x-text-input id="current_position" class="block mt-1 w-full" type="text" name="current_position" :value="old('current_position', $candidate->current_position)" />
                                    <x-input-error :messages="$errors->get('current_position')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="linkedin_url" :value="__('LinkedIn URL')" />
                                    <x-text-input id="linkedin_url" class="block mt-1 w-full" type="url" name="linkedin_url" :value="old('linkedin_url', $candidate->linkedin_url)" placeholder="https://www.linkedin.com/in/username" />
                                    <x-input-error :messages="$errors->get('linkedin_url')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="resume" :value="__('Resume (PDF, DOC, DOCX)')" />
                                    <input id="resume" type="file" name="resume" accept=".pdf,.doc,.docx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                    <p class="text-xs text-gray-500 mt-1">Leave empty to keep the current resume.</p>
                                    @if($candidate->resume_path)
                                        <p class="text-xs text-gray-500 mt-1">
                                            Current resume: <a href="{{ $candidate->resume_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </p>
                                    @endif
                                    <x-input-error :messages="$errors->get('resume')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="status" :value="__('Status')" />
                                    <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="new" {{ $candidate->status === 'new' ? 'selected' : '' }}>New</option>
                                        <option value="contacted" {{ $candidate->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                        <option value="interviewing" {{ $candidate->status === 'interviewing' ? 'selected' : '' }}>Interviewing</option>
                                        <option value="offered" {{ $candidate->status === 'offered' ? 'selected' : '' }}>Offered</option>
                                        <option value="hired" {{ $candidate->status === 'hired' ? 'selected' : '' }}>Hired</option>
                                        <option value="rejected" {{ $candidate->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="withdrawn" {{ $candidate->status === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $candidate->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Candidate') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>