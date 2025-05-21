<!-- Salary Comparison -->
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6 text-gray-900">
        <h3 class="text-lg font-semibold mb-4">Salary Comparison</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600">Average Salary</p>
                <p class="font-medium">{{ $project->average_salary ? '$'.number_format($project->average_salary, 2) : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Minimum Salary</p>
                <p class="font-medium">{{ $project->min_salary ? '$'.number_format($project->min_salary, 2) : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Maximum Salary</p>
                <p class="font-medium">{{ $project->max_salary ? '$'.number_format($project->max_salary, 2) : 'N/A' }}</p>
            </div>
        </div>

        @if($project->similar_job_postings)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Similar Job Postings</p>
                <p class="mt-1">{{ $project->similar_job_postings }}</p>
            </div>
        @endif

        @if($project->salary_data_source)
            <div class="mt-4">
                <p class="text-sm text-gray-600">Salary Data Source</p>
                <p class="mt-1">{{ $project->salary_data_source }}</p>
            </div>
        @endif
    </div>
</div>