<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add Candidates to Project: ') }} {{ $project->title }}
            </h2>
            <div>
                {{-- Assuming a route exists to view candidates for a project --}}
                <a href="{{ route('projects.candidates.index', $project) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Candidates
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <x-container>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Upload CVs</h3>
                    
                    <form action="{{ route('projects.candidates.store', $project) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="cv_files" :value="__('Select CV Files (PDF, DOCX)')" />
                            <input id="cv_files" name="cv_files[]" type="file" multiple 
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100 mt-1" 
                                   accept=".pdf,.doc,.docx" required>
                            <x-input-error :messages="$errors->get('cv_files')" class="mt-2" />
                            <x-input-error :messages="$errors->get('cv_files.*')" class="mt-2" /> {{-- Show errors for individual files --}}
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Upload and Process') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </x-container>
    </div>
</x-app-layout>