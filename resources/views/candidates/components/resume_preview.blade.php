<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Resume Preview</h3>
            @if($candidate->resume_path)
                <a href="{{ route('candidates.resume.view', $candidate) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    View Resume
                </a>
            @endif
        </div>
        
        @if($candidate->resume_path)
            @php
                $extension = strtolower(pathinfo($candidate->resume_path, PATHINFO_EXTENSION));
                $resumeUrl = route('candidates.resume.view', $candidate);
                $downloadUrl = route('candidates.resume.download', $candidate);
                
            @endphp
            <div class="mb-4">
                @if($extension === 'pdf')
                    <iframe src="{{ $resumeUrl }}" type="application/pdf" width="100%" height="600px" style="border: 1px solid #ccc;"></iframe>
                @elseif(in_array($extension, ['doc', 'docx']))
                    @php
                        $signedUrl = URL::temporarySignedRoute('candidates.resume.public', now()->addMinutes(60), $candidate);
                    @endphp
                    <iframe
                        src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($signedUrl) }}"
                        width="100%" height="600" frameborder="0">
                    </iframe>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm text-gray-700 font-medium">File Preview Not Available</p>
                                <p class="text-xs text-gray-600">Preview not supported for {{ strtoupper($extension) }} files.</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ $downloadUrl }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Download File
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            @if($candidate->resume_text)
                <details class="mb-4">
                    <summary class="text-sm text-gray-600 cursor-pointer">View Extracted Resume Text</summary>
                    <div class="mt-2 border border-gray-200 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <pre class="whitespace-pre-wrap font-sans text-sm">{{ $candidate->resume_text }}</pre>
                    </div>
                </details>
            @endif
        @else
            <div class="bg-gray-50 p-4 rounded text-center">
                <p>No resume available for preview</p>
            </div>
        @endif
    </div>
</div>

