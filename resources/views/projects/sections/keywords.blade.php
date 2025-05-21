<!-- Keywords -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 text-gray-900">
        <h3 class="text-lg font-semibold mb-4">Keywords & Synonyms</h3>
        
        @if($project->keywords)
            <div class="mb-4">
                <p class="text-sm text-gray-600">Keywords</p>
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach(explode(',', $project->keywords) as $keyword)
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">{{ trim($keyword) }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if($project->synonyms)
            <div class="mb-4">
                <p class="text-sm text-gray-600">Synonyms</p>
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach(explode(',', $project->synonyms) as $synonym)
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">{{ trim($synonym) }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if($project->translations)
            <div class="mb-4">
                <p class="text-sm text-gray-600">Translations in <strong>{{strtoupper($project->translation_language)}}</strong></p>
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach(explode(',', $project->translations) as $translation)
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">{{ trim($translation) }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @if(!$project->keywords && !$project->synonyms && !$project->translations)
            <p class="text-sm text-gray-500">No keywords or synonyms added yet.</p>
        @endif
    </div>
</div>