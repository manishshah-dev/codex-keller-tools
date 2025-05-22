<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Edit Profile') }}: {{ $profile->title }}
                </h2>
                <p class="text-gray-600 mt-1">
                    <a href="{{ route('projects.candidates.profiles.show', [$project, $candidate, $profile]) }}" class="text-indigo-600 hover:text-indigo-900">
                        Back to Profile
                    </a>
                </p>
            </div>
            <div>
                <a href="{{ route('projects.candidates.profiles.show-generate', [$project, $candidate, $profile]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                    </svg>
                    Generate Content
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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


                    <form action="{{ route('projects.candidates.profiles.update', [$project, $candidate, $profile]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-8">
                            <!-- Basic Information -->
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Basic Information</h3>
                                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                    <div class="sm:col-span-6">
                                        <label for="title" class="block text-sm font-medium text-gray-700">Profile Title</label>
                                        <div class="mt-1">
                                            <input type="text" name="title" id="title" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('title', $profile->title) }}" required>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-6">
                                        <label for="summary" class="block text-sm font-medium text-gray-700">Summary</label>
                                        <div class="mt-1">
                                            <textarea id="summary" name="summary" rows="5" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('summary', $profile->summary) }}</textarea>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">A brief summary of the candidate's qualifications and fit for the role.</p>
                                    </div>
                                </div>
                            </div>

                            
                            <!-- Resume Insights -->
                            <div>
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Resume Insights</h3>
                                <div class="mt-4 space-y-6" id="resume-insights">
                                    <div>
                                        <h4 class="font-medium">Contact Info</h4>
                                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                                <input type="text" name="extracted_data[contact_info][name]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $profile->extracted_data['contact_info']['name'] ?? '' }}">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                                <input type="text" name="extracted_data[contact_info][email]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $profile->extracted_data['contact_info']['email'] ?? '' }}">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                                <input type="text" name="extracted_data[contact_info][phone]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $profile->extracted_data['contact_info']['phone'] ?? '' }}">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Location</label>
                                                <input type="text" name="extracted_data[contact_info][location]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $profile->extracted_data['contact_info']['location'] ?? '' }}">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">LinkedIn</label>
                                                <input type="text" name="extracted_data[contact_info][linkedin]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $profile->extracted_data['contact_info']['linkedin'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-medium">Education</h4>
                                            <button type="button" id="add-education-btn" class="text-sm text-indigo-700">Add</button>
                                        </div>
                                        <div id="education-container" class="mt-2 space-y-4">
                                            @php($education = $profile->extracted_data['education'] ?? [])
                                            @forelse($education as $index => $edu)
                                                <div class="education-item border rounded-md p-4">
                                                    <div class="flex justify-between">
                                                        <h5 class="font-medium">Entry {{ $index + 1 }}</h5>
                                                        <button type="button" class="remove-education-btn text-red-600 text-sm">Remove</button>
                                                    </div>
                                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                        <input type="text" name="extracted_data[education][{{ $index }}][degree]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Degree" value="{{ $edu['degree'] ?? '' }}">
                                                        <input type="text" name="extracted_data[education][{{ $index }}][institution]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Institution" value="{{ $edu['institution'] ?? '' }}">
                                                        <input type="text" name="extracted_data[education][{{ $index }}][date_range]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Date Range" value="{{ $edu['date_range'] ?? '' }}">
                                                        <textarea name="extracted_data[education][{{ $index }}][highlights]" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Highlights (one per line)">{{ isset($edu['highlights']) ? (is_array($edu['highlights']) ? implode("\n", $edu['highlights']) : $edu['highlights']) : '' }}</textarea>
                                                    </div>
                                                </div>
                                            @empty
                                            @endforelse
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-medium">Experience</h4>
                                            <button type="button" id="add-experience-btn" class="text-sm text-indigo-700">Add</button>
                                        </div>
                                        <div id="experience-container" class="mt-2 space-y-4">
                                            @php($experience = $profile->extracted_data['experience'] ?? [])
                                            @forelse($experience as $index => $exp)
                                                <div class="experience-item border rounded-md p-4">
                                                    <div class="flex justify-between">
                                                        <h5 class="font-medium">Entry {{ $index + 1 }}</h5>
                                                        <button type="button" class="remove-experience-btn text-red-600 text-sm">Remove</button>
                                                    </div>
                                                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                        <input type="text" name="extracted_data[experience][{{ $index }}][title]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Job Title" value="{{ $exp['title'] ?? '' }}">
                                                        <input type="text" name="extracted_data[experience][{{ $index }}][company]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Company" value="{{ $exp['company'] ?? '' }}">
                                                        <input type="text" name="extracted_data[experience][{{ $index }}][date_range]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Date Range" value="{{ $exp['date_range'] ?? '' }}">
                                                        <textarea name="extracted_data[experience][{{ $index }}][responsibilities]" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Responsibilities (one per line)">{{ isset($exp['responsibilities']) ? (is_array($exp['responsibilities']) ? implode("\n", $exp['responsibilities']) : $exp['responsibilities']) : '' }}</textarea>
                                                        <textarea name="extracted_data[experience][{{ $index }}][achievements]" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Achievements (one per line)">{{ isset($exp['achievements']) ? (is_array($exp['achievements']) ? implode("\n", $exp['achievements']) : $exp['achievements']) : '' }}</textarea>
                                                    </div>
                                                </div>
                                            @empty
                                            @endforelse
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-medium">Skills</h4>
                                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <input type="text" name="extracted_data[skills][technical]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Technical (comma separated)" value="{{ isset($profile->extracted_data['skills']['technical']) ? (is_array($profile->extracted_data['skills']['technical']) ? implode(',', $profile->extracted_data['skills']['technical']) : $profile->extracted_data['skills']['technical']) : '' }}">
                                            <input type="text" name="extracted_data[skills][soft]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Soft Skills" value="{{ isset($profile->extracted_data['skills']['soft']) ? (is_array($profile->extracted_data['skills']['soft']) ? implode(',', $profile->extracted_data['skills']['soft']) : $profile->extracted_data['skills']['soft']) : '' }}">
                                            <input type="text" name="extracted_data[skills][languages]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Languages" value="{{ isset($profile->extracted_data['skills']['languages']) ? (is_array($profile->extracted_data['skills']['languages']) ? implode(',', $profile->extracted_data['skills']['languages']) : $profile->extracted_data['skills']['languages']) : '' }}">
                                            <input type="text" name="extracted_data[skills][certifications]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Certifications" value="{{ isset($profile->extracted_data['skills']['certifications']) ? (is_array($profile->extracted_data['skills']['certifications']) ? implode(',', $profile->extracted_data['skills']['certifications']) : $profile->extracted_data['skills']['certifications']) : '' }}">
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-medium">Additional Info</h4>
                                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <input type="text" name="extracted_data[additional_info][interests]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Interests" value="{{ isset($profile->extracted_data['additional_info']['interests']) ? (is_array($profile->extracted_data['additional_info']['interests']) ? implode(',', $profile->extracted_data['additional_info']['interests']) : $profile->extracted_data['additional_info']['interests']) : '' }}">
                                            <input type="text" name="extracted_data[additional_info][volunteer_work]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Volunteer Work" value="{{ isset($profile->extracted_data['additional_info']['volunteer_work']) ? (is_array($profile->extracted_data['additional_info']['volunteer_work']) ? implode(',', $profile->extracted_data['additional_info']['volunteer_work']) : $profile->extracted_data['additional_info']['volunteer_work']) : '' }}">
                                            <input type="text" name="extracted_data[additional_info][publications]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Publications" value="{{ isset($profile->extracted_data['additional_info']['publications']) ? (is_array($profile->extracted_data['additional_info']['publications']) ? implode(',', $profile->extracted_data['additional_info']['publications']) : $profile->extracted_data['additional_info']['publications']) : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Headings -->
                            <div>
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Profile Headings</h3>
                                    <button type="button" id="add-heading-btn" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                        Add Heading
                                    </button>
                                </div>
                                
                                <div class="mt-4">
                                    <div id="headings-container">
                                        @if($profile->headings && count($profile->formatted_headings) > 0)
                                            @foreach($profile->formatted_headings as $index => $heading)
                                                <div class="heading-item border rounded-md p-4 mb-4">
                                                    <div class="flex justify-between items-center mb-3">
                                                        <h4 class="text-md font-medium">Heading {{ $index + 1 }}</h4>
                                                        <button type="button" class="remove-heading-btn text-red-600 hover:text-red-800">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="space-y-3">
                                                        <div>
                                                            <label class="block text-sm font-medium text-gray-700">Heading Title</label>
                                                            <input type="text" name="headings[{{ $index }}][title]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $heading['title'] }}" required>
                                                            <input type="hidden" name="headings[{{ $index }}][order]" value="{{ $heading['order'] }}">
                                                        </div>
                                                        
                                                        <div class="bullet-points-container">
                                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bullet Points</label>
                                                            @foreach($heading['content'] as $bulletIndex => $bullet)
                                                                <div class="bullet-point-item flex items-start space-x-2 mb-2">
                                                                    <div class="flex-grow">
                                                                        <input type="text" name="headings[{{ $index }}][content][{{ $bulletIndex }}][content]" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $bullet['content'] }}" required>
                                                                        @if(isset($bullet['evidence_source']))
                                                                            <input type="hidden" name="headings[{{ $index }}][content][{{ $bulletIndex }}][evidence_source]" value="{{ $bullet['evidence_source'] }}">
                                                                        @endif
                                                                    </div>
                                                                    <button type="button" class="remove-bullet-btn flex-shrink-0 text-red-600 hover:text-red-800">
                                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        
                                                        <button type="button" class="add-bullet-btn mt-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <svg class="-ml-0.5 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                                            </svg>
                                                            Add Bullet Point
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-6 bg-gray-50 rounded-md">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900">No headings</h3>
                                                <p class="mt-1 text-sm text-gray-500">Get started by adding a heading or generating content.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Finalization -->
                            <div class="pt-5 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="relative flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="finalize" name="finalize" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" value="1">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="finalize" class="font-medium text-gray-700">Finalize Profile</label>
                                                <p class="text-gray-500">Mark this profile as finalized and ready for submission to client.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('projects.candidates.profiles.show', [$project, $candidate, $profile]) }}" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Cancel
                                        </a>
                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Save Profile
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates for JavaScript -->
    <template id="heading-template">
        <div class="heading-item border rounded-md p-4 mb-4">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-md font-medium">New Heading</h4>
                <button type="button" class="remove-heading-btn text-red-600 hover:text-red-800">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Heading Title</label>
                    <input type="text" name="headings[INDEX][title]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                    <input type="hidden" name="headings[INDEX][order]" value="ORDER">
                </div>
                
                <div class="bullet-points-container">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bullet Points</label>
                    <div class="bullet-point-item flex items-start space-x-2 mb-2">
                        <div class="flex-grow">
                            <input type="text" name="headings[INDEX][content][0][content]" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                        <button type="button" class="remove-bullet-btn flex-shrink-0 text-red-600 hover:text-red-800">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="button" class="add-bullet-btn mt-2 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-0.5 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add Bullet Point
                </button>
            </div>
        </div>
    </template>

    <template id="bullet-point-template">
        <div class="bullet-point-item flex items-start space-x-2 mb-2">
            <div class="flex-grow">
                <input type="text" name="headings[INDEX][content][BULLET_INDEX][content]" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
            </div>
            <button type="button" class="remove-bullet-btn flex-shrink-0 text-red-600 hover:text-red-800">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </template>

        <template id="education-template">
        <div class="education-item border rounded-md p-4">
            <div class="flex justify-between">
                <h5 class="font-medium">New Entry</h5>
                <button type="button" class="remove-education-btn text-red-600 text-sm">Remove</button>
            </div>
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <input type="text" name="extracted_data[education][INDEX][degree]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Degree">
                <input type="text" name="extracted_data[education][INDEX][institution]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Institution">
                <input type="text" name="extracted_data[education][INDEX][date_range]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Date Range">
                <textarea name="extracted_data[education][INDEX][highlights]" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Highlights (one per line)"></textarea>
            </div>
        </div>
    </template>

    <template id="experience-template">
        <div class="experience-item border rounded-md p-4">
            <div class="flex justify-between">
                <h5 class="font-medium">New Entry</h5>
                <button type="button" class="remove-experience-btn text-red-600 text-sm">Remove</button>
            </div>
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <input type="text" name="extracted_data[experience][INDEX][title]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Job Title">
                <input type="text" name="extracted_data[experience][INDEX][company]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Company">
                <input type="text" name="extracted_data[experience][INDEX][date_range]" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Date Range">
                <textarea name="extracted_data[experience][INDEX][responsibilities]" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Responsibilities (one per line)"></textarea>
                <textarea name="extracted_data[experience][INDEX][achievements]" rows="2" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Achievements (one per line)"></textarea>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const headingsContainer = document.getElementById('headings-container');
            const addHeadingBtn = document.getElementById('add-heading-btn');
            const headingTemplate = document.getElementById('heading-template').innerHTML;
            const bulletPointTemplate = document.getElementById('bullet-point-template').innerHTML;
            
            // Add new heading
            addHeadingBtn.addEventListener('click', function() {
                const headingItems = document.querySelectorAll('.heading-item');
                const newIndex = headingItems.length;
                const newOrder = headingItems.length;
                
                let newHeading = headingTemplate
                    .replace(/INDEX/g, newIndex)
                    .replace(/ORDER/g, newOrder);
                
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newHeading;
                const newHeadingElement = tempDiv.firstElementChild;
                
                headingsContainer.appendChild(newHeadingElement);
                
                // Add event listeners to the new heading
                addHeadingEventListeners(newHeadingElement, newIndex);
            });
            
            // Add event listeners to existing headings
            document.querySelectorAll('.heading-item').forEach(function(heading, index) {
                addHeadingEventListeners(heading, index);
            });
            
            function addHeadingEventListeners(heading, headingIndex) {
                // Remove heading button
                heading.querySelector('.remove-heading-btn').addEventListener('click', function() {
                    if (confirm('Are you sure you want to remove this heading?')) {
                        heading.remove();
                        updateHeadingIndices();
                    }
                });
                
                // Add bullet point button
                heading.querySelector('.add-bullet-btn').addEventListener('click', function() {
                    const bulletPointsContainer = heading.querySelector('.bullet-points-container');
                    const bulletPoints = bulletPointsContainer.querySelectorAll('.bullet-point-item');
                    const newBulletIndex = bulletPoints.length;
                    
                    let newBulletPoint = bulletPointTemplate
                        .replace(/INDEX/g, headingIndex)
                        .replace(/BULLET_INDEX/g, newBulletIndex);
                    
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newBulletPoint;
                    const newBulletElement = tempDiv.firstElementChild;
                    
                    // Insert before the "Add Bullet Point" button
                    bulletPointsContainer.appendChild(newBulletElement);
                    
                    // Add event listener to the new bullet point
                    addBulletPointEventListeners(newBulletElement);
                });
                
                // Add event listeners to existing bullet points
                heading.querySelectorAll('.bullet-point-item').forEach(function(bulletPoint) {
                    addBulletPointEventListeners(bulletPoint);
                });
            }
            
            function addBulletPointEventListeners(bulletPoint) {
                // Remove bullet point button
                bulletPoint.querySelector('.remove-bullet-btn').addEventListener('click', function() {
                    const bulletPoints = bulletPoint.parentElement.querySelectorAll('.bullet-point-item');
                    if (bulletPoints.length > 1 || confirm('Are you sure you want to remove the last bullet point?')) {
                        bulletPoint.remove();
                        updateBulletPointIndices(bulletPoint.closest('.heading-item'));
                    }
                });
            }
            
            function updateHeadingIndices() {
                document.querySelectorAll('.heading-item').forEach(function(heading, index) {
                    heading.querySelectorAll('input[name^="headings["]').forEach(function(input) {
                        const name = input.getAttribute('name');
                        const newName = name.replace(/headings\[\d+\]/, `headings[${index}]`);
                        input.setAttribute('name', newName);
                    });
                    
                    // Update order input
                    const orderInput = heading.querySelector('input[name$="[order]"]');
                    if (orderInput) {
                        orderInput.value = index;
                    }
                });
            }
            
            function updateBulletPointIndices(heading) {
                const bulletPoints = heading.querySelectorAll('.bullet-point-item');
                const headingIndex = Array.from(document.querySelectorAll('.heading-item')).indexOf(heading);
                
                bulletPoints.forEach(function(bulletPoint, index) {
                    const input = bulletPoint.querySelector('input[name^="headings["]');
                    const name = input.getAttribute('name');
                    const newName = name.replace(/headings\[\d+\]\[content\]\[\d+\]/, `headings[${headingIndex}][content][${index}]`);
                    input.setAttribute('name', newName);
                });
            }
        });
    </script>
</x-app-layout>