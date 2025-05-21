<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-blue-100 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-2">Active Projects</h3>
                            <p class="text-3xl font-bold">{{ $activeProjects }}</p>
                        </div>
                        <div class="bg-green-100 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-2">Completed Projects</h3>
                            <p class="text-3xl font-bold">{{ $completedProjects }}</p>
                        </div>
                        <div class="bg-purple-100 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-semibold mb-2">Total Projects</h3>
                            <p class="text-3xl font-bold">{{ $activeProjects + $completedProjects }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Recent Projects</h3>
                            <a href="{{ route('projects.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                Create New Project
                            </a>
                        </div>
                        
                        @if($projects->count() > 0)
                            <div class="bg-white shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Title
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Department
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Created
                                            </th>
                                            <th scope="col" class="relative px-6 py-3">
                                                <span class="sr-only">Actions</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($projects as $project)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $project->title }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-500">
                                                        {{ $project->department ?? 'N/A' }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 
                                                           ($project->status === 'completed' ? 'bg-blue-100 text-blue-800' : 
                                                           'bg-yellow-100 text-yellow-800') }}">
                                                        {{ ucfirst($project->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $project->created_at->format('M d, Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                        View
                                                    </a>
                                                    <a href="{{ route('projects.edit', $project) }}" class="text-blue-600 hover:text-blue-900">
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                                <p class="text-yellow-700">No projects found. Create your first project to get started!</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="{{ route('projects.index') }}" class="bg-white p-4 rounded-lg shadow border border-gray-200 hover:bg-gray-50">
                                <div class="font-medium text-blue-600">All Projects</div>
                                <p class="text-sm text-gray-500">View and manage all your recruitment projects</p>
                            </a>
                            <a href="{{ route('projects.create') }}" class="bg-white p-4 rounded-lg shadow border border-gray-200 hover:bg-gray-50">
                                <div class="font-medium text-green-600">New Project</div>
                                <p class="text-sm text-gray-500">Start a new recruitment project</p>
                            </a>
                            <a href="{{ route('job-descriptions.index') }}" class="bg-white p-4 rounded-lg shadow border border-gray-200 hover:bg-gray-50">
                                <div class="font-medium text-purple-600">Job Descriptions</div>
                                <p class="text-sm text-gray-500">Manage all job descriptions</p>
                            </a>
                            <a href="{{ route('ai-settings.index') }}" class="bg-white p-4 rounded-lg shadow border border-gray-200 hover:bg-gray-50">
                                <div class="font-medium text-indigo-600">AI Settings</div>
                                <p class="text-sm text-gray-500">Configure AI providers and prompts</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>