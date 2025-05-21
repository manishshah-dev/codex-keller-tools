<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Job Description Templates') }}
            </h2>
            <div>
                <a href="{{ route('job-description-templates.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Create New Template
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
                    <h3 class="text-lg font-semibold mb-4">Templates</h3>
                    
                    @if(count($templates) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($templates as $template)
                                        <tr>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $template->name }}</div>
                                            </td>
                                            <td class="py-4 px-4">
                                                <div class="text-sm text-gray-500">{{ Str::limit($template->description, 100) }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('job-description-templates.show', $template) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                    <a href="{{ route('job-description-templates.edit', $template) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                    <form action="{{ route('job-description-templates.duplicate', $template) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-green-600 hover:text-green-900">Duplicate</button>
                                                    </form>
                                                    <form action="{{ route('job-description-templates.destroy', $template) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
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
                            <p class="text-gray-600">No templates created yet.</p>
                            <a href="{{ route('job-description-templates.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                Create Your First Template
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </x-container>
    </div>
</x-app-layout>