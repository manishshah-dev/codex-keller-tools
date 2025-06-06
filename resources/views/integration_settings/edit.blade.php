<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Integration Setting') }}
            </h2>
            <div>
                <a href="{{ route('integration-settings.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Back</a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('integration-settings.update', $integrationSetting) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $integrationSetting->name) }}" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="type" :value="__('Type')" />
                                <select id="type" name="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                                    <option value="workable" {{ old('type', $integrationSetting->type) == 'workable' ? 'selected' : '' }}>Workable</option>
                                    <option value="brighthire" {{ old('type', $integrationSetting->type) == 'brighthire' ? 'selected' : '' }}>BrightHire</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="api_endpoint" :value="__('API Endpoint')" />
                            <x-text-input id="api_endpoint" class="block mt-1 w-full" type="text" name="api_endpoint" value="{{ old('api_endpoint', $integrationSetting->api_endpoint) }}" required />
                            <x-input-error :messages="$errors->get('api_endpoint')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="api_key" :value="__('API Key (leave blank to keep current key)')" />
                            <x-text-input id="api_key" class="block mt-1 w-full" type="password" name="api_key" value="" />
                            <x-input-error :messages="$errors->get('api_key')" class="mt-2" />
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $integrationSetting->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="is_active" class="ml-2 text-sm text-gray-600">Active</label>
                            </div>
                            <div class="flex items-center">
                                <input id="is_default" type="checkbox" name="is_default" value="1" {{ old('is_default', $integrationSetting->is_default) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="is_default" class="ml-2 text-sm text-gray-600">Default</label>
                            </div>
                        </div>
                        <div class="flex items-center justify-end">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
