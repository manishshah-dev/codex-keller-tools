<?php

namespace App\Jobs;

use App\Models\AISetting;
use App\Models\AIPrompt;
use App\Models\Candidate;
use App\Models\Project;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeAllCandidatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $projectId;
    public int $aiSettingId;
    public string $aiModel;
    public ?int $aiPromptId; // Nullable

    /**
     * Create a new job instance.
     */
    public function __construct(int $projectId, int $aiSettingId, string $aiModel, ?int $aiPromptId)
    {
        $this->projectId = $projectId;
        $this->aiSettingId = $aiSettingId;
        $this->aiModel = $aiModel;
        $this->aiPromptId = $aiPromptId;
    }

    /**
     * Execute the job.
     */
    public function handle(AIService $aiService): void
    {
        Log::info("Starting AnalyzeAllCandidatesJob for Project ID: {$this->projectId}");

        try {
            $project = Project::with(['candidates', 'requirements'])->findOrFail($this->projectId);
            $aiSetting = AISetting::findOrFail($this->aiSettingId);
            $aiPrompt = $this->aiPromptId ? AIPrompt::find($this->aiPromptId) : null;

            if ($project->candidates->isEmpty()) {
                Log::info("No candidates found for Project ID: {$this->projectId}. Exiting job.");
                return;
            }

            if ($project->requirements->isEmpty()) {
                Log::warning("No requirements found for Project ID: {$this->projectId}. Analysis might be inaccurate.");
                // Decide if you want to proceed without requirements or fail the job
            }

            // Prepare requirements data (example, adjust as needed for AIService)
            $requirementsText = $project->requirements->map(function ($req) {
                return "- " . $req->name . ($req->is_required ? ' (Required)' : '') . ' [Weight: ' . $req->weight . ']';
            })->implode("\n");


            foreach ($project->candidates as $candidate) {
                // Ensure candidate has text content (handle potential missing data)
                if (empty($candidate->resume_text)) {
                     Log::warning("Skipping candidate ID {$candidate->id} due to missing resume text.");
                     continue;
                }

                try {
                    // Call the AIService to analyze the CV
                    $analysisResult = $aiService->analyzeCvAgainstRequirements(
                        $project, // Pass the whole project object
                        $aiSetting,
                        $this->aiModel,
                        $aiPrompt, // Pass the specific prompt object or null
                        $candidate->resume_text,
                        $requirementsText // Pass the formatted requirements string
                        // user_id can be derived from $project->user_id if needed in the service
                    );


                    if ($analysisResult['error']) {
                        Log::error("AI analysis failed for Candidate ID {$candidate->id}: " . $analysisResult['error']);
                        $candidate->status = 'analysis_failed';
                        // Optionally store the error message in the candidate record
                    } else {
                        $candidate->match_score = $analysisResult['match_score'];
                        $candidate->status = $analysisResult['status'];
                        $candidate->analysis_details = $analysisResult['analysis_details']; // Store detailed analysis
                        Log::info("Successfully analyzed Candidate ID {$candidate->id}. Score: {$candidate->match_score}");
                    }

                    // Log details before saving
                    Log::info("Attempting to save analysis details for Candidate ID {$candidate->id}:", [
                        'score' => $candidate->match_score,
                        'status' => $candidate->status,
                        'details_type' => gettype($candidate->analysis_details),
                        'details_preview' => is_string($candidate->analysis_details) ? substr($candidate->analysis_details, 0, 100) . '...' : (is_array($candidate->analysis_details) ? 'Array data' : 'Other type')
                    ]);
                    $candidate->save();
                    Log::info("Saved analysis details for Candidate ID {$candidate->id}.");

                } catch (\Exception $e) {
                    Log::error("Error analyzing Candidate ID {$candidate->id}: " . $e->getMessage());
                    // Optionally update candidate status to failed
                     $candidate->status = 'analysis_failed';
                     $candidate->saveQuietly(); // Save without triggering events if needed
                }
                 // Optional: Add a small delay to avoid hitting API rate limits if analyzing many candidates
                 // sleep(1);
            }

            Log::info("Finished AnalyzeAllCandidatesJob for Project ID: {$this->projectId}");

            // TODO: Add notification logic here (e.g., email the user)

        } catch (\Exception $e) {
            Log::error("AnalyzeAllCandidatesJob failed for Project ID: {$this->projectId}. Error: " . $e->getMessage());
            // Handle potential job failure (e.g., release back to queue with delay)
            $this->fail($e); // Marks the job as failed
        }
    }
}
