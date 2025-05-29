<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit AI Prompt') }}: {{ $prompt->name }}
            </h2>
            <div>
                <a href="{{ route('ai-settings.prompts') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Prompts
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('ai-settings.prompts.update', $prompt) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="feature" :value="__('Feature')" />
                                <div class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm">
                                    {{ $features[$prompt->feature] ?? $prompt->feature }}
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Feature cannot be changed after creation</p>
                            </div>
                            
                            <div>
                                <x-input-label for="name" :value="__('Prompt Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $prompt->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="prompt_template" :value="__('Prompt Template')" />
                            <textarea id="prompt_template" name="prompt_template" rows="10" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('prompt_template', $prompt->prompt_template) }}</textarea>
                            <x-input-error :messages="$errors->get('prompt_template')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">
                                Use placeholders like @{{project_title}}, @{{job_title}}, @{{responsibilities}}, etc. for dynamic content.
                            </p>
                        </div>
                        
                        <!-- Hidden input to maintain any existing parameters -->
                        <input type="hidden" name="parameters" value="{{ json_encode(old('parameters', $prompt->parameters ?? [])) }}">
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Available Parameters:</strong> You can use any parameter in your prompt template by including it as a placeholder. For example: @{{project_title}}, @{{job_title}}, @{{responsibilities}}, etc.
                                    </p>
                                    <p class="text-sm text-yellow-700 mt-2">
                                        Common parameters include: project_title, job_title, responsibilities, requirements, industry, experience_level, company_name, location, department, required_skills, preferred_skills, document_text, resume_text, job_description, and more.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="provider" :value="__('Provider (Optional)')" />
                                <select id="provider" name="provider" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Any Provider</option>
                                    @foreach($providers as $key => $value)
                                        <option value="{{ $key }}" {{ old('provider', $prompt->provider) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('provider')" class="mt-2" />
                            </div>
                            
                            <div>
                                <x-input-label for="model" :value="__('Model (Optional)')" />
                                <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model', $prompt->model)" />
                                <x-input-error :messages="$errors->get('model')" class="mt-2" />
                                <p class="text-sm text-gray-500 mt-1">
                                    E.g., gpt-4, claude-3-opus, etc.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input id="is_default" type="checkbox" name="is_default" value="1" {{ old('is_default', $prompt->is_default) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <label for="is_default" class="ml-2 text-sm text-gray-600">Set as Default Prompt for this Feature</label>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Update Prompt') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>