<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Job Description Template') }}: {{ $template->name }}
            </h2>
            <div>
                <a href="{{ route('job-description-templates.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Templates
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <x-container>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('job-description-templates.update', $template) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('job_description_templates._form', ['template' => $template])
                        
                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Update Template') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </x-container>
    </div>
</x-app-layout>