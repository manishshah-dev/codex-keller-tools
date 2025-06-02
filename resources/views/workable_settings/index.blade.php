<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Workable Settings') }}
            </h2>
            <div>
                <a href="{{ route('workable-settings.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Add Workable Account
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Accounts</h3>
                    @if(count($settings) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-2 px-4 text-left">Name</th>
                                        <th class="py-2 px-4 text-left">Subdomain</th>
                                        <th class="py-2 px-4 text-left">Active</th>
                                        <th class="py-2 px-4 text-left">Default</th>
                                        <th class="py-2 px-4 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($settings as $setting)
                                        <tr>
                                            <td class="py-2 px-4">{{ $setting->name }}</td>
                                            <td class="py-2 px-4">{{ $setting->subdomain }}</td>
                                            <td class="py-2 px-4">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $setting->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $setting->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $setting->is_default ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $setting->is_default ? 'Default' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('workable-settings.edit', $setting) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                    <form action="{{ route('workable-settings.destroy', $setting) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this setting?')">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-600">No Workable settings configured.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
