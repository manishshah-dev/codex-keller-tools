<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('AI Prompts') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('ai-settings.prompts.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Create New Prompt
                </a>
                <a href="{{ route('ai-settings.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to AI Settings
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Filter Prompts</h3>
                    
                    <form action="{{ route('ai-settings.prompts') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="feature" :value="__('Feature')" />
                                <select id="feature" name="feature" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Features</option>
                                    @foreach($features as $feature)
                                        <option value="{{ $feature }}" {{ request('feature') == $feature ? 'selected' : '' }}>{{ $feature }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <x-input-label for="provider" :value="__('Provider')" />
                                <select id="provider" name="provider" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Providers</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider }}" {{ request('provider') == $provider ? 'selected' : '' }}>{{ $provider }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <x-primary-button>
                                    {{ __('Filter') }}
                                </x-primary-button>
                                
                                @if(request('feature') || request('provider'))
                                    <a href="{{ route('ai-settings.prompts') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Clear') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">AI Prompts</h3>
                    
                    @if(count($prompts) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feature</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($prompts as $prompt)
                                        <tr>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $prompt->name }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $prompt->feature }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $prompt->provider ?? 'Any' }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $prompt->model ?? 'Any' }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $prompt->is_default ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $prompt->is_default ? 'Default' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $prompt->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('ai-settings.prompts.edit', $prompt) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                    <form action="{{ route('ai-settings.prompts.destroy', $prompt) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this prompt?')">Delete</button>
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
                            <p class="text-gray-600">No AI prompts found.</p>
                            <a href="{{ route('ai-settings.prompts.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                Create Your First Prompt
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>