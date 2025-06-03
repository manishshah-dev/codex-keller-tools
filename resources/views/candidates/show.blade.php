<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $candidate->full_name }}
                </h2>
                <p class="text-gray-600 mt-1">
                    Project: <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-900">{{ $project->title }}</a> |
                    <a href="{{ route('projects.candidates.index', $project) }}" class="text-indigo-600 hover:text-indigo-900">Back to Candidates</a>
                </p>
            </div>
            <div class="flex space-x-2 items-center">
                <a href="{{ route('projects.candidates.edit', [$project, $candidate]) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                    Edit Candidate
                </a>
                <form action="{{ route('projects.candidates.destroy', [$project, $candidate]) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded" onclick="return confirm('Are you sure you want to delete this candidate?')">
                        Delete
                    </button>
                </form>
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

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Candidate Info -->
                <div class="lg:col-span-1">
                    <!-- Basic Info Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold">Candidate Information</h3>
                                <div class="flex space-x-2">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $candidate->status_badge_class }}">
                                        {{ ucfirst($candidate->status) }}
                                    </span>
                                    <span class="px-2 py-1 rounded-full text-xs capitalize font-semibold {{ $candidate->source_badge_class }}">
                                        {{ $candidate->source }}
                                    </span>    
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-500">Full Name</p>
                                    <p class="font-medium">{{ $candidate->full_name }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium">{{ $candidate->email ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Phone</p>
                                    <p class="font-medium">{{ $candidate->phone ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Location</p>
                                    <p class="font-medium">{{ $candidate->location ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Current Company</p>
                                    <p class="font-medium">{{ $candidate->current_company ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-500">Current Position</p>
                                    <p class="font-medium">{{ $candidate->current_position ?? 'N/A' }}</p>
                                </div>
                                
                                @if($candidate->linkedin_url)
                                <div>
                                    <p class="text-sm text-gray-500">LinkedIn</p>
                                    <a href="{{ $candidate->linkedin_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                        View Profile
                                    </a>
                                </div>
                                @endif

                                
                                
                                @if($candidate->workable_url)
                                <div>
                                    <p class="text-sm text-gray-500">Workable Profile</p>
                                    <a href="{{ $candidate->workable_url }}" target="_blank" rel="external nofollow" class="text-indigo-600 hover:text-indigo-900">
                                        View Profile
                                    </a>
                                </div>
                                @endif
                                
                                @if($candidate->resume_path)
                                <div>
                                    <p class="text-sm text-gray-500">Resume</p>
                                    <a href="{{ route('candidates.resume.view', $candidate) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                        Download Resume
                                    </a>
                                </div>
                                @endif
                                
                                <div>
                                    <p class="text-sm text-gray-500">Added On</p>
                                    <p class="font-medium">{{ $candidate->created_at->format('M d, Y') }}</p>
                                </div>
                                
                                @if($candidate->last_analyzed_at)
                                    <div>
                                        <p class="text-sm text-gray-500">Last Analyzed</p>
                                        <p class="font-medium">{{ $candidate->last_analyzed_at->format('M d, Y H:i') }}</p>
                                    </div>
                                @endif

                                <p class="text-md font-bold">Analyze Candidate</p>

                                <form action="{{ route('candidates.analyze', $candidate) }}" method="POST" class="!mt-0 flex items-center flex-col">
                                    @csrf
                                    <select id="analyze_ai_setting_id" name="ai_setting_id" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        @foreach($aiSettings as $setting)
                                            <option value="{{ $setting->id }}" data-provider-type="{{ $setting->provider }}" data-models='@json($setting->models ?? [])'>
                                                {{ $setting->name }} ({{ ucfirst($setting->provider) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <select id="analyze_ai_model" name="ai_model" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required disabled>
                                        <option value="">Select AI Setting first</option>
                                    </select>
                                    <select id="analyze_ai_prompt_id" name="ai_prompt_id" class="text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" disabled>
                                        <option value="">Use Default/Generic Prompt</option>
                                    </select>
                                    <button type="submit" class="mt-5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                                        Analyze Candidate
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-4">Notes</h3>
                            
                            @if($candidate->notes)
                                <p class="whitespace-pre-line">{{ $candidate->notes }}</p>
                            @else
                                <p class="text-gray-500">No notes available.</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Chat History -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Chat History</h3>
                                <a href="{{ route('projects.analyzer', $project) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                                    Open CV Analyzer
                                </a>
                            </div>
                            
                            @if(count($chatMessages) > 0)
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    @foreach($chatMessages as $message)
                                        <div class="p-3 rounded-lg {{ $message->message_class }}">
                                            <p>{{ $message->message }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $message->created_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-500">No chat history available.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Match Analysis -->
                <div class="lg:col-span-2">
                    <!-- Detailed Analysis Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-4">Detailed AI Analysis</h3>

                            @if($candidate->analysis_details)
                                @php
                                    // Initialize details variable
                                    $details = null;
                                    $rawData = $candidate->analysis_details;
                                    
                                    // First attempt: Direct JSON decode if it's a string
                                    if (is_string($rawData)) {
                                        $details = json_decode($rawData, true);
                                    } elseif (is_array($rawData)) {
                                        $details = $rawData;
                                    }
                                    
                                    // Second attempt: If details is still not an array or decode failed
                                    if (!is_array($details) || json_last_error() !== JSON_ERROR_NONE) {
                                        // Handle case where the JSON might be a string within an array
                                        if (is_array($rawData) && isset($rawData[0]) && is_string($rawData[0])) {
                                            $details = json_decode($rawData[0], true);
                                        }
                                    }
                                    
                                    // Third attempt: Handle escaped JSON strings (like "{\r\n  \"match_score\": 0.5...")
                                    if ((!is_array($details) || json_last_error() !== JSON_ERROR_NONE) && is_string($rawData)) {
                                        if (strpos($rawData, '"{"') === 0 || strpos($rawData, '"{\"') === 0) {
                                            // Remove the extra quotes at the beginning and end
                                            $cleanString = trim($rawData, '"');
                                            // Unescape the JSON string
                                            $unescapedString = stripcslashes($cleanString);
                                            // Decode the unescaped JSON
                                            $details = json_decode($unescapedString, true);
                                        }
                                    }
                                    
                                    // Fourth attempt: Try to extract JSON from a string using regex
                                    if ((!is_array($details) || json_last_error() !== JSON_ERROR_NONE) && is_string($rawData)) {
                                        // Look for JSON object pattern
                                        if (preg_match('/(\{[\s\S]*\})/', $rawData, $matches)) {
                                            $jsonString = trim($matches[1]);
                                            $details = json_decode($jsonString, true);
                                        }
                                    }
                                    
                                    // Fourth attempt: Try to clean up the string and parse again
                                    if ((!is_array($details) || json_last_error() !== JSON_ERROR_NONE) && is_string($rawData)) {
                                        // Remove any non-JSON characters that might be causing issues
                                        $cleanedString = preg_replace('/[[:cntrl:]]/', '', $rawData);
                                        $details = json_decode($cleanedString, true);
                                    }
                                    
                                    // If all attempts fail, use the original data
                                    if (!is_array($details) || json_last_error() !== JSON_ERROR_NONE) {
                                        $details = $rawData;
                                    }
                                @endphp

                                @if(is_array($details))
                                    <div class="bg-white rounded-lg shadow p-6">
                                        {{-- Display structured JSON data --}}
                                        @if(isset($details['justification']))
                                            <div class="mb-6 pb-4 border-b border-gray-200">
                                                <h4 class="font-medium text-lg mb-2 text-gray-800">Justification:</h4>
                                                <p class="text-gray-700 whitespace-pre-wrap">{{ $details['justification'] }}</p>
                                            </div>
                                        @endif

                                        @if(isset($details['requirement_breakdown']) && is_array($details['requirement_breakdown']) && count($details['requirement_breakdown']) > 0)
                                            <div class="mb-6 pb-4 border-b border-gray-200">
                                                <h4 class="font-medium text-lg mb-3 text-gray-800">Requirement Breakdown:</h4>
                                                <div class="space-y-4">
                                                    @foreach($details['requirement_breakdown'] as $item)
                                                        <div class="bg-gray-50 p-4 rounded-lg">
                                                            <div class="flex justify-between items-center mb-2">
                                                                <span class="font-semibold text-gray-800">{{ $item['requirement'] ?? 'N/A' }}</span>
                                                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $item['match'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                    {{ $item['match'] ? 'Match' : 'No Match' }}
                                                                </span>
                                                            </div>
                                                            @if(isset($item['evidence']))
                                                                <p class="text-gray-600 text-sm">{{ $item['evidence'] }}</p>
                                                            @endif
                                                            @if(isset($item['score']))
                                                                <div class="mt-2">
                                                                    <div class="flex justify-between items-center text-xs text-gray-500 mb-1">
                                                                        <span>Score</span>
                                                                        <span>{{ number_format($item['score'] * 100, 0) }}%</span>
                                                                    </div>
                                                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ number_format($item['score'] * 100, 0) }}%"></div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if(isset($details['red_flags']) && is_array($details['red_flags']) && count($details['red_flags']) > 0)
                                            <div class="mb-6 pb-4 border-b border-gray-200">
                                                <h4 class="font-medium text-lg mb-3 text-gray-800">Red Flags / Concerns:</h4>
                                                <div class="bg-red-50 p-4 rounded-lg">
                                                    <ul class="list-disc list-inside space-y-2">
                                                        @foreach($details['red_flags'] as $flag)
                                                            <li class="text-red-700">{{ $flag }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif

                                        @if(isset($details['interview_questions']) && is_array($details['interview_questions']) && count($details['interview_questions']) > 0)
                                            <div class="mb-4">
                                                <h4 class="font-medium text-lg mb-3 text-gray-800">Suggested Interview Questions:</h4>
                                                <div class="bg-blue-50 p-4 rounded-lg">
                                                    <ol class="list-decimal list-inside space-y-2">
                                                        @foreach($details['interview_questions'] as $question)
                                                            <li class="text-gray-700">{{ $question }}</li>
                                                        @endforeach
                                                    </ol>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Fallback for unexpected array structure or if only raw text was stored as JSON --}}
                                        @if(!isset($details['justification']) && !isset($details['requirement_breakdown']) && !isset($details['red_flags']) && !isset($details['interview_questions']))
                                            <div class="bg-yellow-50 p-4 rounded-lg">
                                                <h4 class="font-medium mb-2 text-yellow-800">Raw Analysis Data:</h4>
                                                @php
                                                    // Try to convert the details to a structured format
                                                    $formattedJson = json_encode($details, JSON_PRETTY_PRINT);
                                                    
                                                    // Add syntax highlighting
                                                    $formattedJson = preg_replace('/("(.*?)":)/', '<span class="text-blue-600">$1</span>', $formattedJson);
                                                    $formattedJson = preg_replace('/: (true|false|null)/', ': <span class="text-red-600">$1</span>', $formattedJson);
                                                    $formattedJson = preg_replace('/: (\d+(\.\d+)?)/', ': <span class="text-green-600">$1</span>', $formattedJson);
                                                    $formattedJson = preg_replace('/"(.*?)"(?=,|\n|\s*\})/', '<span class="text-amber-600">"$1"</span>', $formattedJson);
                                                @endphp
                                                <div class="whitespace-pre-wrap font-mono text-sm overflow-x-auto bg-gray-50 p-4 rounded border border-gray-200">{!! $formattedJson !!}</div>
                                            </div>
                                        @endif
                                    </div>
                                @elseif(is_string($candidate->analysis_details))
                                    {{-- Try to format the string as JSON if possible --}}
                                    <div class="bg-yellow-50 p-4 rounded-lg">
                                        <h4 class="font-medium mb-2 text-yellow-800">String Raw Analysis Data:</h4>
                                        @php
                                            // Try to parse as JSON for formatting
                                            $jsonData = json_decode($candidate->analysis_details);
                                            if (json_last_error() === JSON_ERROR_NONE) {
                                                $formattedJson = json_encode($jsonData, JSON_PRETTY_PRINT);
                                                
                                                // Add syntax highlighting
                                                $formattedJson = preg_replace('/("(.*?)":)/', '<span class="text-blue-600">$1</span>', $formattedJson);
                                                $formattedJson = preg_replace('/: (true|false|null)/', ': <span class="text-red-600">$1</span>', $formattedJson);
                                                $formattedJson = preg_replace('/: (\d+(\.\d+)?)/', ': <span class="text-green-600">$1</span>', $formattedJson);
                                                $formattedJson = preg_replace('/"(.*?)"(?=,|\n|\s*\})/', '<span class="text-amber-600">"$1"</span>', $formattedJson);
                                            } else {
                                                $formattedJson = htmlspecialchars($candidate->analysis_details);
                                            }
                                        @endphp
                                        <div class="whitespace-pre-wrap font-mono text-sm overflow-x-auto bg-gray-50 p-4 rounded border border-gray-200">{!! $formattedJson !!}</div>
                                    </div>
                                @else
                                    <p class="text-gray-500">Analysis details are in an unexpected format.</p>
                                @endif
                            @else
                                <div class="bg-gray-50 p-4 rounded text-center">
                                    <p>No detailed analysis available. Run analysis from the project candidate list.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Resume Preview -->
                    @include('candidates.components.resume_preview', ['candidate' => $candidate])

                    
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // Initialize Select2 for the dropdowns
        const settingSelect = document.getElementById('analyze_ai_setting_id');
        const modelSelect = document.getElementById('analyze_ai_model');
        const promptSelect = document.getElementById('analyze_ai_prompt_id');
        const providerModelsMap = @json($providerModels ?? []);
        const allPrompts = @json($prompts ?? []);

        function updateModelsDropdown() {
            const opt = settingSelect.options[settingSelect.selectedIndex];
            if (!opt || !opt.value) {
                modelSelect.innerHTML = '<option value="">Select AI Setting first</option>';
                $(modelSelect).prop('disabled', true).trigger('change');
                updatePromptsDropdown();
                return;
            }
            const provider = opt.dataset.providerType;
            let enabledModels = [];
            try {
                enabledModels = JSON.parse(opt.dataset.models || '[]');
            } catch (e) {
                enabledModels = [];
            }
            const allModels = providerModelsMap[provider] || [];
            modelSelect.innerHTML = '';
            let hasValid = false;
            allModels.forEach(m => {
                const o = document.createElement('option');
                o.value = m;
                o.textContent = m;
                if (enabledModels.includes(m)) {
                    o.selected = true;
                }
                hasValid = true;
                modelSelect.appendChild(o);
            });

            if (!hasValid) {
                const o = document.createElement('option');
                o.value = '';
                o.textContent = 'No enabled models found';
                modelSelect.appendChild(o);
                $(modelSelect).prop('disabled', true);
            } else {
                $(modelSelect).prop('disabled', false);
            }
            $(modelSelect).trigger('change');
            updatePromptsDropdown();
        }

        function updatePromptsDropdown() {
            const opt = settingSelect.options[settingSelect.selectedIndex];
            const provider = opt ? opt.dataset.providerType : null;
            const model = modelSelect.value;
            const filtered = allPrompts.filter(p => (!p.provider || p.provider === provider) && (!p.model || p.model === model));
            promptSelect.innerHTML = '<option value="">Use Default/Generic Prompt</option>';
            filtered.forEach(p => {
                const o = document.createElement('option');
                o.value = p.id;
                o.textContent = p.name + (p.is_default ? ' (Default)' : '');
                if (p.is_default) o.selected = true;
                promptSelect.appendChild(o);
            });
            $(promptSelect).prop('disabled', $(modelSelect).prop('disabled')).trigger('change');
        }

        if (settingSelect) {
            $('#analyze_ai_setting_id').on('change', updateModelsDropdown);
            $('#analyze_ai_model').on('change', updatePromptsDropdown);
            updateModelsDropdown();
        }

        $('#analyze_ai_setting_id').select2({ width: '100%' });
        $('#analyze_ai_model').select2({ width: '100%' });
        $('#analyze_ai_prompt_id').select2({ width: '100%' });
    });

</script>
</x-app-layout>