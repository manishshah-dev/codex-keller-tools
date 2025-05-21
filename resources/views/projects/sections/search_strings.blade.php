<!-- Search Strings -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 text-gray-900">
        <h3 class="text-lg font-semibold mb-4">Search Strings</h3>
        
        @if($project->linkedin_boolean_string)
            <div class="mb-4">
                <p class="text-sm text-gray-600">LinkedIn Boolean String</p>
                <div class="mt-1 p-3 bg-gray-100 rounded-md overflow-x-auto">
                    <code class="text-sm">{{ $project->linkedin_boolean_string }}</code>
                </div>
                <button onclick="copyToClipboard('linkedin-boolean')" class="mt-2 text-xs text-blue-600 hover:text-blue-800">Copy to clipboard</button>
                <textarea id="linkedin-boolean" class="hidden">{{ $project->linkedin_boolean_string }}</textarea>
            </div>
        @endif

        @if($project->google_xray_linkedin_string)
            <div class="mb-4">
                <p class="text-sm text-gray-600">Google X-Ray LinkedIn String</p>
                <div class="mt-1 p-3 bg-gray-100 rounded-md overflow-x-auto">
                    <code class="text-sm">{{ $project->google_xray_linkedin_string }}</code>
                </div>
                <button onclick="copyToClipboard('google-xray-linkedin')" class="mt-2 text-xs text-blue-600 hover:text-blue-800">Copy to clipboard</button>
                <textarea id="google-xray-linkedin" class="hidden">{{ $project->google_xray_linkedin_string }}</textarea>
            </div>
        @endif

        @if($project->google_xray_cv_string)
            <div class="mb-4">
                <p class="text-sm text-gray-600">Google X-Ray CV String</p>
                <div class="mt-1 p-3 bg-gray-100 rounded-md overflow-x-auto">
                    <code class="text-sm">{{ $project->google_xray_cv_string }}</code>
                </div>
                <button onclick="copyToClipboard('google-xray-cv')" class="mt-2 text-xs text-blue-600 hover:text-blue-800">Copy to clipboard</button>
                <textarea id="google-xray-cv" class="hidden">{{ $project->google_xray_cv_string }}</textarea>
            </div>
        @endif

        @if($project->search_string_notes)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Search String Notes</p>
                <p class="mt-1">{{ $project->search_string_notes }}</p>
            </div>
        @endif
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const textarea = document.getElementById(elementId);
    textarea.select();
    document.execCommand('copy');
    alert('Copied to clipboard!');
}
</script>