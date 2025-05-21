<!-- AI Questions -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 text-gray-900">
        <h3 class="text-lg font-semibold mb-4">AI-Generated Questions</h3>
        
        @if($project->candidate_questions)
            <div class="mb-4">
                <p class="text-md font-bold">Candidate Questions</p>
                <div class="mt-2 space-y-2">
                    @if(is_array($project->candidate_questions))
                        @foreach($project->candidate_questions as $key => $question)
                            @if(trim($question))
                                <div class="p-3 bg-gray-50 rounded-md">
                                    <p class="text-sm">{{ $key + 1 }}. {!! $question !!}</p>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="p-3 bg-gray-50 rounded-md">
                           <p class="text-sm">{!! nl2br($project->candidate_questions) !!}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($project->recruiter_questions)
            <div class="mb-4 mt-6">
                <p class="text-md font-bold">Recruiter Questions</p>
                <div class="mt-2 space-y-2">
                    @if(is_array($project->recruiter_questions))
                        @foreach($project->recruiter_questions as $key => $question)
                            @if(trim($question))
                                <div class="p-3 bg-gray-50 rounded-md">
                                    <p class="text-sm">{{ $key + 1 }}. {!! $question !!}</p>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <div class="p-3 bg-gray-50 rounded-md">
                           <p class="text-sm">{!! nl2br($project->recruiter_questions) !!}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if(!$project->candidate_questions && !$project->recruiter_questions)
            <p class="text-sm text-gray-500">No AI-generated questions available yet.</p>
        @endif
    </div>
</div>