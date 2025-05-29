<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Submit Profile to Client</h2>
    </x-slot>

    <div >
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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
                    
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <ul class="list-disc pl-5 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('projects.candidates.profiles.submissions.store', [$project, $candidate, $profile]) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Client Email</label>
                            <input type="email" name="client_email" required class="mt-1 block w-full border-gray-300 rounded-md" />
                            <x-input-error :messages="$errors->get('client_email')" class="mt-2" />
                        </div>
                        <!-- <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Client Name</label>
                            <input type="text" name="client_name" class="mt-1 block w-full border-gray-300 rounded-md" />
                            <x-input-error :messages="$errors->get('client_name')" class="mt-2" />
                        </div> -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Subject</label>
                            <input type="text" name="subject" class="mt-1 block w-full border-gray-300 rounded-md" value="Candidate Profile Submission: {{{$candidate->full_name}}}" />
                            <small class="block text-xs text-gray-500">Note: The default subject line is "Candidate Profile Submission: {candidate_name}".</small>
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <textarea name="email_body" id="email_body" rows="10" class="block w-full border-gray-300 rounded-md">{{ old('email_body', $template) }}</textarea>
                            <small class="block text-xs text-gray-500">Use placeholders: @{{ candidate_name }}, @{{ profile_title }}, @{{ profile }}</small>
                            <div class="mt-2">
                                <button type="button" id="insert_placeholder" class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                    Insert Placeholder
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('email_body')" class="mt-2" />
                        </div>
                        <div class="mb-4 flex align-center items-center">
                            <input type="checkbox" id="attach_cv" name="attach_cv" value="1" class="mr-2" checked/>
                            <label class="block text-sm font-medium text-gray-700" for="attach_cv">Attach CV</label>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Preview</label>
                            <div id="preview" class="border p-4 rounded-md bg-gray-50 min-h-32 prose prose-sm max-w-none"></div>
                        </div>
                        <div class="flex justify-end">
                            <a href="{{ route('projects.candidates.profiles.show', [$project, $candidate, $profile]) }}" class="px-4 py-2 mr-3 bg-gray-200 rounded-md">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Submit</button>
                        </div>
                    </form>
                    
                    <!-- TinyMCE CDN -->
                    <script src="{{ asset('js/tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
                    
                    <!-- Custom styles for preview -->
                    <style>
                        #preview {
                            line-height: 1.6;
                        }
                        #preview h1, #preview h2, #preview h3, #preview h4, #preview h5, #preview h6 {
                            font-weight: bold;
                            /* margin-top: 1.5em; */
                            margin-bottom: 0.5em;
                        }
                        #preview h1 { font-size: 2em; }
                        #preview h2 { font-size: 1.5em; }
                        #preview h3 { font-size: 1.25em; }
                        #preview h4 { font-size: 1.1em; }
                        #preview h5 { font-size: 1em; }
                        #preview h6 { font-size: 0.9em; }
                        #preview p {
                            margin-bottom: 1em;
                        }
                        #preview strong, #preview b {
                            font-weight: bold;
                        }
                        #preview em, #preview i {
                            font-style: italic;
                        }
                        #preview ul, #preview ol {
                            margin: 1em 0;
                            padding-left: 2em;
                        }
                        #preview ul {
                            list-style-type: disc;
                        }
                        #preview ol {
                            list-style-type: decimal;
                        }
                        #preview li {
                            margin-bottom: 0.5em;
                        }
                        #preview a {
                            color: #3b82f6;
                            text-decoration: underline;
                        }
                        #preview blockquote {
                            border-left: 4px solid #e5e7eb;
                            padding-left: 1em;
                            margin: 1em 0;
                            font-style: italic;
                        }
                        #preview table {
                            border-collapse: collapse;
                            width: 100%;
                            margin: 1em 0;
                        }
                        #preview th, #preview td {
                            border: 1px solid #e5e7eb;
                            padding: 0.5em;
                            text-align: left;
                        }
                        #preview th {
                            background-color: #f9fafb;
                            font-weight: bold;
                        }
                        #preview img {
                            max-width: 100%;
                            height: auto;
                        }
                        #preview hr {
                            border: none;
                            border-top: 1px solid #e5e7eb;
                            margin: 2em 0;
                        }
                    </style>
                    
                    <script>
                        const templateField = document.getElementById('email_body');
                        const preview = document.getElementById('preview');
                        const insertPlaceholderBtn = document.getElementById('insert_placeholder');

                        const profileHtml = @json(view('emails.partials.profile', ['profile' => $profile])->render());
                        const candidateName = @json($candidate->full_name);
                        const profileTitle = @json($profile->title);

                        let tinymceEditor = null;

                        function getTemplateContent() {
                            if (tinymceEditor) {
                                return tinymceEditor.getContent();
                            }
                            return templateField.value || '';
                        }

                        function updatePreview() {
                            try {
                                let html = getTemplateContent();
                                
                                // Replace placeholders with actual values
                                html = html.replace(/{\{\s*candidate_name\s*\}\}/g, candidateName);
                                html = html.replace(/{\{\s*profile_title\s*\}\}/g, profileTitle);
                                html = html.replace(/{\{\s*profile\s*\}\}/g, profileHtml);
                                
                                // Set innerHTML to render HTML properly
                                preview.innerHTML = html;
                            } catch (error) {
                                console.error('Error updating preview:', error);
                                preview.innerHTML = '<p class="text-red-500">Error rendering preview</p>';
                            }
                        }

                        function insertPlaceholder() {
                            const placeholders = [
                                { text: 'Candidate Name', value: '@{{ candidate_name }}' },
                                { text: 'Profile Title', value: '@{{ profile_title }}' },
                                { text: 'Profile Content', value: '@{{ profile }}' },
                            ];

                            // Create a simple dropdown
                            const dropdown = document.createElement('select');
                            dropdown.className = 'absolute z-10 bg-white border border-gray-300 rounded shadow-lg p-2';
                            dropdown.style.top = insertPlaceholderBtn.offsetTop + insertPlaceholderBtn.offsetHeight + 'px';
                            dropdown.style.left = insertPlaceholderBtn.offsetLeft + 'px';

                            const defaultOption = document.createElement('option');
                            defaultOption.textContent = 'Select a placeholder...';
                            defaultOption.value = '';
                            dropdown.appendChild(defaultOption);

                            placeholders.forEach(placeholder => {
                                const option = document.createElement('option');
                                option.textContent = placeholder.text;
                                option.value = placeholder.value;
                                dropdown.appendChild(option);
                            });

                            dropdown.addEventListener('change', function() {
                                if (this.value) {
                                    if (tinymceEditor) {
                                        tinymceEditor.insertContent(this.value);
                                    } else {
                                        const cursorPos = templateField.selectionStart;
                                        const textBefore = templateField.value.substring(0, cursorPos);
                                        const textAfter = templateField.value.substring(cursorPos);
                                        templateField.value = textBefore + this.value + textAfter;
                                        templateField.focus();
                                        templateField.setSelectionRange(cursorPos + this.value.length, cursorPos + this.value.length);
                                    }
                                    updatePreview();
                                }
                                document.body.removeChild(dropdown);
                            });

                            dropdown.addEventListener('blur', function() {
                                setTimeout(() => {
                                    if (document.body.contains(dropdown)) {
                                        document.body.removeChild(dropdown);
                                    }
                                }, 200);
                            });

                            document.body.appendChild(dropdown);
                            dropdown.focus();
                        }

                        // Initialize TinyMCE on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            tinymce.init({
                                selector: '#email_body',
                                height: 400,
                                menubar: false,
                                plugins: [
                                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                                    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'quickbars', 'editimage'
                                ],
                                toolbar: 'undo redo | blocks | link image | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | advtablerownumbering | help',
                                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                                setup: function(editor) {
                                    tinymceEditor = editor;
                                    editor.on('input change keyup', function() {
                                        updatePreview();
                                    });
                                },
                                init_instance_callback: function(editor) {
                                    updatePreview();
                                }
                            });

                            // Add event listeners
                            if (insertPlaceholderBtn) insertPlaceholderBtn.addEventListener('click', insertPlaceholder);
                        });

                        // Ensure form submission gets the right content
                        document.querySelector('form').addEventListener('submit', function() {
                            if (tinymceEditor) {
                                templateField.value = tinymceEditor.getContent();
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>