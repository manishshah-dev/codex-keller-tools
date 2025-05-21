<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('AI Usage Logs') }}
            </h2>
            <div>
                <a href="{{ route('ai-settings.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to AI Settings
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Filter Logs</h3>
                    
                    <form action="{{ route('ai-settings.usage-logs') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="provider" :value="__('Provider')" />
                                <select id="provider" name="provider" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Providers</option>
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider }}" {{ request('provider') == $provider ? 'selected' : '' }}>{{ $provider }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
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
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All</option>
                                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="failure" {{ request('status') == 'failure' ? 'selected' : '' }}>Failure</option>
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <x-primary-button>
                                    {{ __('Filter') }}
                                </x-primary-button>
                                
                                @if(request('provider') || request('feature') || request('status') || request('start_date') || request('end_date'))
                                    <a href="{{ route('ai-settings.usage-logs') }}" class="ml-3 inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Clear') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="request('start_date')" />
                            </div>
                            
                            <div>
                                <x-input-label for="end_date" :value="__('End Date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="request('end_date')" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Usage Logs</h3>
                    
                    @if(count($logs) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feature</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tokens</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($logs as $log)
                                        <tr>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $log->created_at->format('M d, Y H:i:s') }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $log->provider }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $log->model }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $log->feature }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $log->success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $log->success ? 'Success' : 'Failed' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($log->tokens_used) }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">${{ number_format($log->cost, 4) }}</div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $logs->links() }}
                        </div>
                    @else
                        <div class="bg-gray-50 p-6 rounded-lg text-center">
                            <p class="text-gray-600">No usage logs found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>