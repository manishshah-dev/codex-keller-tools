<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Template Details') }}: {{ $template->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('job-description-templates.edit', $template) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Edit Template
                </a>
                <a href="{{ route('job-description-templates.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Templates
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <x-container>
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Template Information</h3>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $template->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Description</h4>
                                <p class="text-gray-700">{{ $template->description ?: 'No description provided' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Industry</h4>
                                <p class="text-gray-700">{{ $template->industry }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Job Level</h4>
                                <p class="text-gray-700">
                                    @if($template->job_level === 'entry')
                                        Entry Level
                                    @elseif($template->job_level === 'mid')
                                        Mid Level
                                    @elseif($template->job_level === 'senior')
                                        Senior Level
                                    @elseif($template->job_level === 'executive')
                                        Executive
                                    @else
                                        {{ $template->job_level }}
                                    @endif
                                </p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Created</h4>
                                <p class="text-gray-700">{{ $template->created_at->format('F j, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold mb-4">Template Content</h3>

                        <div class="space-y-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Overview Template</h4>
                                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                                    <p class="text-gray-700 whitespace-pre-line">{{ $template->overview_template ?: 'No overview template provided' }}</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Responsibilities Template</h4>
                                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                                    <p class="text-gray-700 whitespace-pre-line">{{ $template->responsibilities_template ?: 'No responsibilities template provided' }}</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Requirements Template</h4>
                                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                                    <p class="text-gray-700 whitespace-pre-line">{{ $template->requirements_template ?: 'No requirements template provided' }}</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Benefits Template</h4>
                                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                                    <p class="text-gray-700 whitespace-pre-line">{{ $template->benefits_template ?: 'No benefits template provided' }}</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Disclaimer Template</h4>
                                <div class="bg-gray-50 p-4 rounded border border-gray-200">
                                    <p class="text-gray-700 whitespace-pre-line">{{ $template->disclaimer_template ?: 'No disclaimer template provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-between items-center border-t border-gray-200 pt-6">
                        <div class="flex space-x-4">
                            <form action="{{ route('job-description-templates.duplicate', $template) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                    Duplicate Template
                                </button>
                            </form>
                            
                            <form action="{{ route('job-description-templates.destroy', $template) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded" onclick="return confirm('Are you sure you want to delete this template?')">
                                    Delete Template
                                </button>
                            </form>
                        </div>
                        
                        <a href="{{ route('job-description-templates.index') }}" class="text-white bg-gray-500 hover:bg-gray-600 px-4 py-2 rounded">
                            Back to Templates
                        </a>
                    </div>
                </div>
            </div>
        </x-container>
    </div>
</x-app-layout>