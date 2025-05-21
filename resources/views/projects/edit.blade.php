<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Project') }}
            </h2>
            <div>
                <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    Back to Project
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

            <!-- Include the multi-step form -->
            @include('projects.multi_step_form', ['project' => $project])
        </div>
    </div>
</x-app-layout>