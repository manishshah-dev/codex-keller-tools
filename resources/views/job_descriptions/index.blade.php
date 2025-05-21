<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Job Descriptions') }}
            </h2>
            <div>
                <a href="{{ route('job-descriptions.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Create New Job Description
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('info') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold mb-2">All Job Descriptions</h3>
                        <p class="text-gray-600">Manage all job descriptions across projects</p>
                    </div>

                    @if(count($jobDescriptions) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($jobDescriptions as $jobDescription)
                                        <tr>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $jobDescription->title }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $jobDescription?->project?->title }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $jobDescription->version }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $jobDescription->status === 'published' ? 'bg-green-100 text-green-800' :
                                                       ($jobDescription->status === 'approved' ? 'bg-blue-100 text-blue-800' :
                                                       ($jobDescription->status === 'review' ? 'bg-yellow-100 text-yellow-800' :
                                                       'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucfirst($jobDescription->status) }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $jobDescription->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('job-descriptions.show', $jobDescription) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                    <a href="{{ route('job-descriptions.edit', $jobDescription) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                    <form action="{{ route('job-descriptions.destroy', $jobDescription) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this job description?')">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $jobDescriptions->links() }}
                        </div>
                    @else
                        <div class="bg-gray-50 p-6 rounded-lg text-center">
                            <p class="text-gray-600">No job descriptions found for this project.</p>
                            <a href="{{ route('job-descriptions.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                Create Your First Job Description
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Job Description Templates Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Job Description Templates</h3>
                        <a href="{{ route('job-description-templates.create') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                            Create Template
                        </a>
                    </div>
                    
                    @if(count($templates) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Industry</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Level</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($templates as $template)
                                        <tr>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $template->name }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $template->industry }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ ucfirst($template->job_level) }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $template->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('job-description-templates.show', $template) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                    <a href="{{ route('job-description-templates.edit', $template) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                    <form action="{{ route('job-description-templates.destroy', $template) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this template?')">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-gray-50 p-6 rounded-lg text-center">
                            <p class="text-gray-600">No job description templates found.</p>
                            <a href="{{ route('job-description-templates.create') }}" class="mt-4 inline-block bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                                Create Your First Template
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>