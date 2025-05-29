<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('AI Settings') }}
            </h2>
            <div>
                <a href="{{ route('ai-settings.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Add AI Provider
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

            {{-- AI Usage Statistics section removed --}}
            
            <!-- AI Providers -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">AI Providers</h3>
                    
                    @if(count($aiSettings) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Models</th>
                                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($aiSettings as $aiSetting)
                                        <tr>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $aiSetting->provider }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $aiSetting->name }}</div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $aiSetting->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $aiSetting->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $aiSetting->is_default ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $aiSetting->is_default ? 'Default' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4">
                                                <div class="text-sm text-gray-500">
                                                    @if(count($aiSetting->models) > 0)
                                                        @foreach($aiSetting->models as $model)
                                                            <span class="inline-block bg-gray-100 rounded-full px-2 py-1 text-xs font-semibold text-gray-700 mr-1 mb-1">{{ $model }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-gray-400">No models specified</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('ai-settings.edit', $aiSetting) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                    <button 
                                                        onclick="testConnection('{{ $aiSetting->id }}')" 
                                                        class="text-green-600 hover:text-green-900"
                                                    >
                                                        Test
                                                    </button>
                                                    <form action="{{ route('ai-settings.destroy', $aiSetting) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this AI provider?')">Delete</button>
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
                            <p class="text-gray-600">No AI providers configured yet.</p>
                            <a href="{{ route('ai-settings.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                Add Your First AI Provider
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- AI Prompts -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">AI Prompts</h3>
                        <a href="{{ route('ai-settings.prompts') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                            Manage Prompts
                        </a>
                    </div>
                    
                    <p class="text-gray-600 mb-4">
                        Configure and manage AI prompts for different features of the application.
                        Customize how AI generates content for job descriptions, qualifying questions, and more.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <h4 class="font-medium mb-2">Job Description Generation</h4>
                            <p class="text-sm text-gray-600 mb-4">Customize prompts for generating job descriptions based on project information.</p>
                            <a href="{{ route('ai-settings.prompts') }}?feature=job_description" class="text-indigo-600 hover:text-indigo-900">Configure →</a>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <h4 class="font-medium mb-2">Qualifying Questions</h4>
                            <p class="text-sm text-gray-600 mb-4">Customize prompts for generating screening and qualifying questions.</p>
                            <a href="{{ route('ai-settings.prompts') }}?feature=qualifying_questions" class="text-indigo-600 hover:text-indigo-900">Configure →</a>
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <h4 class="font-medium mb-2">Salary Comparison</h4>
                            <p class="text-sm text-gray-600 mb-4">Customize prompts for generating salary comparison data.</p>
                            <a href="{{ route('ai-settings.prompts') }}?feature=salary_comparison" class="text-indigo-600 hover:text-indigo-900">Configure →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function testConnection(aiSettingId) {
            fetch(`/ai-settings/${aiSettingId}/test-connection`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Connection successful!');
                } else {
                    // Alert with \n 
                    alert('Connection failed! \n' + data.message);
                }
            })
            .catch(error => {
                alert('Error testing connection: ' + error);
            });
        }
    </script>
</x-app-layout>