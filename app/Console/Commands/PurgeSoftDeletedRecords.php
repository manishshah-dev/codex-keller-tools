<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\JobDescription;
use App\Models\Candidate;
use App\Models\JobDescriptionTemplate;
use App\Models\QualifyingQuestion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeSoftDeletedRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:purge {days=30 : Number of days to keep soft-deleted records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete soft-deleted records older than the specified number of days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->argument('days');
        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Purging soft-deleted records older than {$days} days ({$cutoffDate->toDateTimeString()})...");
        
        // Purge projects
        $projectsCount = $this->purgeProjects($cutoffDate);
        
        // Purge job descriptions
        $jobDescriptionsCount = $this->purgeJobDescriptions($cutoffDate);
        
        // Purge candidates
        $candidatesCount = $this->purgeCandidates($cutoffDate);
        
        // Purge job description templates
        $templatesCount = $this->purgeTemplates($cutoffDate);
        
        // Purge qualifying questions
        $questionsCount = $this->purgeQuestions($cutoffDate);
        
        $totalCount = $projectsCount + $jobDescriptionsCount + $candidatesCount + $templatesCount + $questionsCount;
        
        $this->info("Purge completed. {$totalCount} records permanently deleted:");
        $this->line("- Projects: {$projectsCount}");
        $this->line("- Job Descriptions: {$jobDescriptionsCount}");
        $this->line("- Candidates: {$candidatesCount}");
        $this->line("- Job Description Templates: {$templatesCount}");
        $this->line("- Qualifying Questions: {$questionsCount}");
        
        // Log the purge
        Log::info("Trash purge completed. {$totalCount} records permanently deleted.", [
            'days' => $days,
            'cutoff_date' => $cutoffDate->toDateTimeString(),
            'projects' => $projectsCount,
            'job_descriptions' => $jobDescriptionsCount,
            'candidates' => $candidatesCount,
            'templates' => $templatesCount,
            'questions' => $questionsCount,
        ]);
    }
    
    /**
     * Purge soft-deleted projects.
     */
    private function purgeProjects(Carbon $cutoffDate): int
    {
        $query = Project::onlyTrashed()->where('deleted_at', '<', $cutoffDate);
        $count = $query->count();
        
        if ($count > 0) {
            // Force delete the records
            $query->forceDelete();
            $this->info("Purged {$count} projects.");
        }
        
        return $count;
    }
    
    /**
     * Purge soft-deleted job descriptions.
     */
    private function purgeJobDescriptions(Carbon $cutoffDate): int
    {
        $query = JobDescription::onlyTrashed()->where('deleted_at', '<', $cutoffDate);
        $count = $query->count();
        
        if ($count > 0) {
            // Force delete the records
            $query->forceDelete();
            $this->info("Purged {$count} job descriptions.");
        }
        
        return $count;
    }
    
    /**
     * Purge soft-deleted candidates.
     */
    private function purgeCandidates(Carbon $cutoffDate): int
    {
        $query = Candidate::onlyTrashed()->where('deleted_at', '<', $cutoffDate);
        $count = $query->count();
        
        if ($count > 0) {
            // Force delete the records
            $query->forceDelete();
            $this->info("Purged {$count} candidates.");
        }
        
        return $count;
    }
    
    /**
     * Purge soft-deleted job description templates.
     */
    private function purgeTemplates(Carbon $cutoffDate): int
    {
        $query = JobDescriptionTemplate::onlyTrashed()->where('deleted_at', '<', $cutoffDate);
        $count = $query->count();
        
        if ($count > 0) {
            // Force delete the records
            $query->forceDelete();
            $this->info("Purged {$count} job description templates.");
        }
        
        return $count;
    }
    
    /**
     * Purge soft-deleted qualifying questions.
     */
    private function purgeQuestions(Carbon $cutoffDate): int
    {
        $query = QualifyingQuestion::onlyTrashed()->where('deleted_at', '<', $cutoffDate);
        $count = $query->count();
        
        if ($count > 0) {
            // Force delete the records
            $query->forceDelete();
            $this->info("Purged {$count} qualifying questions.");
        }
        
        return $count;
    }
}