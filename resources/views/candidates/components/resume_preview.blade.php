<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Resume Preview</h3>
            @if($candidate->resume_path)
                <a href="{{ route('candidates.resume.view', $candidate) }}" target="_blank" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    View PDF
                </a>
            @endif
        </div>
        
        @if($candidate->resume_path)
            {{-- Add the canvas for PDF.js rendering --}}
            <div class="mb-4">
                <canvas id="resume-pdf-canvas" style="border: 1px solid black; width: 100%; height: 100%;"></canvas> {{-- Adjust height as needed --}}
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


<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($candidate->resume_path)
            const pdfUrl = "{{ route('candidates.resume.view', $candidate) }}";
            const pdfjsLib = window['pdfjs-dist/build/pdf'];

            // Setting worker path is crucial for PDF.js to work
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

            const canvas = document.getElementById('resume-pdf-canvas');
            const context = canvas.getContext('2d');

            if (canvas && pdfUrl) {
                const loadingTask = pdfjsLib.getDocument(pdfUrl);
                loadingTask.promise.then(function(pdf) {
                    
                    // Fetch the first page
                    const pageNumber = 1;
                    pdf.getPage(pageNumber).then(function(page) {
                        
                        const viewport = page.getViewport({scale: 1.5}); // Adjust scale as needed

                        // Prepare canvas using PDF page dimensions
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        
                        // Render PDF page into canvas context
                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        const renderTask = page.render(renderContext);
                        renderTask.promise.then(function () {
                            // console.log('Page rendered');
                        }).catch(function (reason) {
                            console.error('Error rendering page: ' + reason);
                            context.font = "16px Arial";
                            context.fillStyle = "red";
                            context.textAlign = "center";
                            context.fillText("Error rendering PDF page.", canvas.width/2, canvas.height/2);
                        });
                    }).catch(function (reason) {
                        console.error('Error getting page: ' + reason);
                        context.font = "16px Arial";
                        context.fillStyle = "red";
                        context.textAlign = "center";
                        context.fillText("Error loading PDF page.", canvas.width/2, canvas.height/2);
                    });
                }).catch(function (reason) {
                    // PDF loading error
                    const ctx = canvas.getContext('2d');
                    ctx.font = "16px Arial";
                    ctx.fillStyle = "red";
                    ctx.textAlign = "center";
                    ctx.fillText("Error loading PDF. Ensure the file exists and is accessible.", canvas.width/2, canvas.height/2);
                });
            }
        @endif
    });
</script>