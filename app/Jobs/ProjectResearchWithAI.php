<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\AISetting;
use App\Models\AIPrompt;
use App\Services\AIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProjectResearchWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $project;
    protected $aiSetting;
    protected $documentText;
    protected $selectedModel;
    protected $enableSearch;
    protected $aiPromptIds;
    protected $userId;

    /**
     * Create a new job instance.
     *
     * @param Project $project
     * @param AISetting $aiSetting
     * @param string $documentText
     * @param string|null $selectedModel
     * @param bool $enableSearch
     * @param array|null $aiPromptIds
     * @param int $userId
     * @return void
     */
    public function __construct(
        Project $project,
        AISetting $aiSetting,
        string $documentText,
        ?string $selectedModel = null,
        bool $enableSearch = false,
        ?array $aiPromptIds = null,
        int $userId = null
    ) {
        $this->project = $project;
        $this->aiSetting = $aiSetting;
        $this->documentText = $documentText;
        $this->selectedModel = $selectedModel;
        $this->enableSearch = $enableSearch;
        $this->aiPromptIds = $aiPromptIds;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @param AIService $aiService
     * @return void
     */
    public function handle(AIService $aiService): void
    {
        try {

            // Use the selected model if provided, otherwise use default
            $aiModel = $this->selectedModel ?? 'gpt-4';
            
            // Include Claap transcript in the document text if available
            $documentText = $this->documentText;
            if (!empty($this->project->claap_transcript)) {
                $documentText .= "\n\nClaap Meeting Transcript:\n" . $this->project->claap_transcript;
            }
            
            // Get the selected prompts if provided
            $selectedPrompts = [];
            if ($this->aiPromptIds && is_array($this->aiPromptIds)) {
                // The format is an associative array where keys are feature names
                // and values are the selected prompt IDs for each feature
                foreach ($this->aiPromptIds as $feature => $promptId) {
                    if (!empty($promptId)) {
                        // Get the prompt for this feature
                        $prompt = AIPrompt::find($promptId);
                        if ($prompt) {
                            $selectedPrompts[$feature] = $prompt;
                        }
                    }
                }
                
            }

            // Process the document with AI and update the project
            $this->processAI($aiService, $aiModel, $documentText, $selectedPrompts);
            
        } catch (Exception $e) {
            // Update the project status to indicate that AI processing failed
            $this->project->update([
                'ai_processing_status' => 'failed'
            ]);
        }
    }

    /**
     * Process the document with AI and update the project.
     *
     * @param AIService $aiService
     * @param string $aiModel
     * @param string $documentText
     * @param array $selectedPrompts
     * @return void
     */
    private function processAI(AIService $aiService, string $aiModel, string $documentText, array $selectedPrompts): void
    {
        try{
            // 1. Extract job details
            $jobDetailsPrompt = '';
            if (isset($selectedPrompts['job_details']) && is_object($selectedPrompts['job_details']) && method_exists($selectedPrompts['job_details'], 'formatPrompt')) {
                // Use the selected prompt for job details
                $jobDetailsPrompt = $selectedPrompts['job_details']->formatPrompt(['document_text' => $documentText]);
            } else {
                // Try to find a default prompt for job details
                $defaultPrompt = AIPrompt::where('feature', 'job_details')
                    ->where('provider', $this->aiSetting->provider)
                    ->where('model', $aiModel)
                    ->where('is_default', true)
                    ->first();
                
                if ($defaultPrompt) {
                    $jobDetailsPrompt = $defaultPrompt->formatPrompt(['document_text' => $documentText]);
                } else {
                    // Fallback to the hardcoded prompt
                    $jobDetailsPrompt = $this->prepareJobDetailsPrompt($documentText);
                }
            }
            
            $jobDetailsResponse = $aiService->generateContent(
                $this->aiSetting,
                $aiModel,
                $jobDetailsPrompt,
                [],
                $this->userId,
                'job_details',
                $this->enableSearch
            );
            $jobDetailsData = $this->parseJobDetailsResponse($jobDetailsResponse['content']);
            $jobDetailsData['translation_language'] = $this->project->translation_language ?? 'multi';
            
            // 2. Generate company research
            $companyResearchPrompt = '';
            $companyResearchPromptData = [
                'company_name' => $jobDetailsData['company_name'] ?? $this->project->company_name ?? '',
                'job_title' => $jobDetailsData['job_title'] ?? $this->project->job_title ?? '',
                'industry_details' => $jobDetailsData['industry_details'] ?? $this->project->industry_details ?? ''
            ];
            
            // Check if company_research prompt exists in selected prompts
            if (isset($selectedPrompts['company_research']) && is_object($selectedPrompts['company_research']) && method_exists($selectedPrompts['company_research'], 'formatPrompt')) {
                // Use the selected prompt for company research
                $companyResearchPrompt = $selectedPrompts['company_research']->formatPrompt($companyResearchPromptData);
            } else {
                // Try to find a default prompt for company research
                $companyResearchDefaultPrompt = AIPrompt::where('feature', 'company_research')
                    ->where('provider', $this->aiSetting->provider)
                    ->where('model', $aiModel)
                    ->where('is_default', true)
                    ->first();

                if ($companyResearchDefaultPrompt) {
                    $companyResearchPrompt = $companyResearchDefaultPrompt->formatPrompt($companyResearchPromptData);
                } else {
                    // Fallback to the hardcoded prompt
                    $companyResearchPrompt = $this->prepareCompanyResearchPromptFromDocument($documentText, $jobDetailsData);
                }
            }
            
            $companyResearchResponse = $aiService->generateContent(
                $this->aiSetting,
                $aiModel,
                $companyResearchPrompt,
                [],
                $this->userId,
                'company_research',
                $this->enableSearch
            );
            $companyResearchData = $this->parseCompanyResearchResponse($companyResearchResponse['content']);
            
            // 3. Generate salary comparison
            $salaryComparisonPrompt = '';
            $salaryComparisonPromptData = [
                'job_title' => $jobDetailsData['job_title'] ?? $this->project->job_title ?? '',
                'location' => $jobDetailsData['location'] ?? $this->project->location ?? '',
                'experience_level' => $jobDetailsData['experience_level'] ?? $this->project->experience_level ?? '',
                'industry_details' => $jobDetailsData['industry_details'] ?? $this->project->industry_details ?? ''
            ];
            
            // Check if salary_comparison prompt exists in selected prompts
            if (isset($selectedPrompts['salary_comparison']) && is_object($selectedPrompts['salary_comparison']) && method_exists($selectedPrompts['salary_comparison'], 'formatPrompt')) {
                // Use the selected prompt for salary comparison
                $salaryComparisonPrompt = $selectedPrompts['salary_comparison']->formatPrompt($salaryComparisonPromptData);
            } else {
                // Try to find a default prompt for salary comparison
                $salaryComparisonDefaultPrompt = AIPrompt::where('feature', 'salary_comparison')
                    ->where('provider', $this->aiSetting->provider)
                    ->where('model', $aiModel)
                    ->where('is_default', true)
                    ->first();
                
                if ($salaryComparisonDefaultPrompt) {
                    $salaryComparisonPrompt = $salaryComparisonDefaultPrompt->formatPrompt($salaryComparisonPromptData);
                } else {
                    // Fallback to the hardcoded prompt
                    $salaryComparisonPrompt = $this->prepareSalaryComparisonPromptFromDocument($documentText, $jobDetailsData);
                }
            }
            
            $salaryComparisonResponse = $aiService->generateContent(
                $this->aiSetting,
                $aiModel,
                $salaryComparisonPrompt,
                [],
                $this->userId,
                'salary_comparison',
                $this->enableSearch
            );
            $salaryComparisonData = $this->parseSalaryComparisonResponse($salaryComparisonResponse['content']);
            
            // 4. Generate search strings
            $searchStringsPrompt = '';
            $searchStringsPromptData = [
                'job_title' => $jobDetailsData['job_title'] ?? $this->project->job_title ?? '',
                'required_skills' => $jobDetailsData['required_skills'] ?? $this->project->required_skills ?? '',
                'preferred_skills' => $jobDetailsData['preferred_skills'] ?? $this->project->preferred_skills ?? '',
                'experience_level' => $jobDetailsData['experience_level'] ?? $this->project->experience_level ?? '',
                'industry_details' => $jobDetailsData['industry_details'] ?? $this->project->industry_details ?? ''
            ];
            
            // Check if search_strings prompt exists in selected prompts
            if (isset($selectedPrompts['search_strings']) && is_object($selectedPrompts['search_strings']) && method_exists($selectedPrompts['search_strings'], 'formatPrompt')) {
                // Use the selected prompt for search strings
                $searchStringsPrompt = $selectedPrompts['search_strings']->formatPrompt($searchStringsPromptData);
            } else {
                // Try to find a default prompt for search strings
                $searchStringsDefaultPrompt = AIPrompt::where('feature', 'search_strings')
                    ->where('provider', $this->aiSetting->provider)
                    ->where('model', $aiModel)
                    ->where('is_default', true)
                    ->first();
                
                if ($searchStringsDefaultPrompt) {
                    $searchStringsPrompt = $searchStringsDefaultPrompt->formatPrompt($searchStringsPromptData);

                } else {
                    // Fallback to the hardcoded prompt
                    $searchStringsPrompt = $this->prepareSearchStringsPromptFromDocument($documentText, $jobDetailsData);
                }
            }
            
            $searchStringsResponse = $aiService->generateContent(
                $this->aiSetting,
                $aiModel,
                $searchStringsPrompt,
                [],
                $this->userId,
                'search_strings',
                $this->enableSearch
            );
            $searchStringsData = $this->parseSearchStringsResponse($searchStringsResponse['content']);
            
            // 5. Generate keywords
            $keywordsPrompt = '';
            $keywordsPromptData = [
                'job_title' => $jobDetailsData['job_title'] ?? $this->project->job_title ?? '',
                'required_skills' => $jobDetailsData['required_skills'] ?? $this->project->required_skills ?? '',
                'preferred_skills' => $jobDetailsData['preferred_skills'] ?? $this->project->preferred_skills ?? '',
                'experience_level' => $jobDetailsData['experience_level'] ?? $this->project->experience_level ?? '',
                'industry_details' => $jobDetailsData['industry_details'] ?? $this->project->industry_details ?? ''
            ];
            
            // Check if keywords prompt exists in selected prompts
            if (isset($selectedPrompts['keywords']) && is_object($selectedPrompts['keywords']) && method_exists($selectedPrompts['keywords'], 'formatPrompt')) {
                // Use the selected prompt for keywords
                $keywordsPrompt = $selectedPrompts['keywords']->formatPrompt($keywordsPromptData);
            } else {
                // Try to find a default prompt for keywords
                $keywordsDefaultPrompt = AIPrompt::where('feature', 'keywords')
                    ->where('provider', $this->aiSetting->provider)
                    ->where('model', $aiModel)
                    ->where('is_default', true)
                    ->first();
                
                if ($keywordsDefaultPrompt) {
                    $keywordsPrompt = $keywordsDefaultPrompt->formatPrompt($keywordsPromptData);
                } else {
                    // Fallback to the hardcoded prompt
                    $keywordsPrompt = $this->prepareKeywordsPromptFromDocument($documentText, $jobDetailsData);
                }
            }
            
            $keywordsResponse = $aiService->generateContent(
                $this->aiSetting,
                $aiModel,
                $keywordsPrompt,
                [],
                $this->userId,
                'keywords',
                $this->enableSearch
            );
            $keywordsData = $this->parseKeywordsResponse($keywordsResponse['content'], $jobDetailsData['translation_language'] ?? 'multi');
            
            // 6. Generate questions
            $questionsPrompt = '';
            $questionsPromptData = [
                'job_title' => $jobDetailsData['job_title'] ?? $this->project->job_title ?? '',
                'required_skills' => $jobDetailsData['required_skills'] ?? $this->project->required_skills ?? '',
                'preferred_skills' => $jobDetailsData['preferred_skills'] ?? $this->project->preferred_skills ?? '',
                'experience_level' => $jobDetailsData['experience_level'] ?? $this->project->experience_level ?? ''
            ];
            
            // Check if ai_questions prompt exists in selected prompts
            if (isset($selectedPrompts['ai_questions']) && is_object($selectedPrompts['ai_questions']) && method_exists($selectedPrompts['ai_questions'], 'formatPrompt')) {
                // Use the selected prompt for questions
                $questionsPrompt = $selectedPrompts['ai_questions']->formatPrompt($questionsPromptData);

            } else {
                // Try to find a default prompt for questions
                $questionsDefaultPrompt = AIPrompt::where('feature', 'ai_questions')
                    ->where('provider', $this->aiSetting->provider)
                    ->where('model', $aiModel)
                    ->where('is_default', true)
                    ->first();
                
                if ($questionsDefaultPrompt) {
                    $questionsPrompt = $questionsDefaultPrompt->formatPrompt($questionsPromptData);
                } else {
                    // Fallback to the hardcoded prompt
                    $questionsPrompt = $this->prepareQuestionsPromptFromDocument($documentText, $jobDetailsData);
                }
            }
            
            $questionsResponse = $aiService->generateContent(
                $this->aiSetting,
                $aiModel,
                $questionsPrompt,
                [],
                $this->userId,
                'questions',
                $this->enableSearch
            );
            $questionsData = $this->parseQuestionsResponse($questionsResponse['content']);
            
            // Merge all data
            $allData = array_merge(
                $jobDetailsData,
                $companyResearchData,
                $salaryComparisonData,
                $searchStringsData,
                $keywordsData,
                $questionsData
            );
            
            // Update the project with all data and set the AI processing status to completed
            $allData['ai_processing_status'] = 'completed';
            $this->project->update($allData);
        } catch (Exception $e) {
            // Log the error and update the project status to indicate that AI processing failed
            Log::error('AI processing failed: ' . $e->getMessage());
            $this->project->update([
                'ai_processing_status' => "failed (" . $e->getMessage() . ")"
            ]);
        }
    }

    /**
     * Prepare the prompt for extracting job details from document.
     *
     * @param string $documentText
     * @return string
     */
    private function prepareJobDetailsPrompt(string $documentText): string
    {
        return <<<PROMPT
You are an expert recruiter's assistant. I need you to extract job details from the following document text.

IMPORTANT: The document text is the ONLY source of information. Do not make up or infer information that is not explicitly stated in the document. If information is not available, leave the field empty.

Document Text:
{$documentText}

Please extract the following information in JSON format:
1. Job title
2. Department
3. Location
4. Required skills
5. Preferred skills
6. Experience level
7. Education requirements
8. Employment type
9. Salary range
10. Company name
11. Job description overview
12. Responsibilities
13. Non-negotiable requirements
14. Preferred requirements
15. Benefits

Format your response as a valid JSON object with the following structure:
{
  "job_title": "Extracted job title",
  "department": "Extracted department",
  "location": "Extracted location",
  "required_skills": "Comma-separated list of required skills",
  "preferred_skills": "Comma-separated list of preferred skills",
  "experience_level": "Extracted experience level",
  "education_requirements": "Extracted education requirements",
  "employment_type": "Extracted employment type (full-time, part-time, contract, etc.)",
  "salary_range": "Extracted salary range",
  "company_name": "Extracted company name",
  "overview": "Extracted job overview",
  "responsibilities": "Extracted responsibilities",
  "requirements_non_negotiable": "Extracted non-negotiable requirements",
  "requirements_preferred": "Extracted preferred requirements",
  "benefits": "Extracted benefits"
}

Only return the JSON object, no other text. If you cannot find information for a field, leave it as an empty string.
PROMPT;
    }

    /**
     * Prepare the prompt for company research from document.
     *
     * @param string $documentText
     * @param array $jobDetailsData
     * @return string
     */
    private function prepareCompanyResearchPromptFromDocument(string $documentText, array $jobDetailsData): string
    {
        // Extract company name and job title from job details data, but don't rely on them exclusively
        $companyName = $jobDetailsData['company_name'] ?? '';
        $jobTitle = $jobDetailsData['job_title'] ?? '';
        $industryDetails = $jobDetailsData['industry_details'] ?? '';
        
        // If we don't have a company name, try to extract it from the document text
        if (empty($companyName)) {
            // Look for common patterns that might indicate a company name
            if (preg_match('/company(?:\s+name)?(?:\s*:|\s+is)\s+([^\.]+)/i', $documentText, $matches)) {
                $companyName = trim($matches[1]);
            } elseif (preg_match('/(?:at|for|with)\s+([A-Z][A-Za-z0-9\s&]+?)(?:\.|,|\s+(?:is|we|our|the))/i', $documentText, $matches)) {
                $companyName = trim($matches[1]);
            }
        }
        
        // If we don't have a job title, try to extract it from the document text
        if (empty($jobTitle)) {
            // Look for common patterns that might indicate a job title
            if (preg_match('/(?:position|job|role)(?:\s+title)?(?:\s*:|\s+is)\s+([^\.]+)/i', $documentText, $matches)) {
                $jobTitle = trim($matches[1]);
            } elseif (preg_match('/(?:hiring|recruiting|looking for|seeking)(?:\s+a)?\s+([A-Za-z0-9\s]+?(?:developer|engineer|manager|director|specialist|analyst|consultant|designer|architect))/i', $documentText, $matches)) {
                $jobTitle = trim($matches[1]);
            }
        }
        
        // If we don't have industry details, try to extract it from the document text
        if (empty($industryDetails)) {
            // Look for common patterns that might indicate industry details
            if (preg_match('/industry(?:\s*:|\s+is)\s+([^\.]+)/i', $documentText, $matches)) {
                $industryDetails = trim($matches[1]);
            } elseif (preg_match('/(?:in the|within the|part of the)\s+([A-Za-z0-9\s]+?(?:industry|sector|field))/i', $documentText, $matches)) {
                $industryDetails = trim($matches[1]);
            }
        }
        
        return <<<PROMPT
You are an expert recruiter's assistant. I need you to research the company mentioned in the following document and provide detailed information.

IMPORTANT: The document text is the PRIMARY source of information. If the company name, job title, or industry details extracted below are empty or incorrect, extract them directly from the document text.

Document Text:
{$documentText}

Extracted Company Name: {$companyName}
Extracted Job Title: {$jobTitle}
Extracted Industry: {$industryDetails}

IMPORTANT: If Google Search is enabled, please use it to search for accurate and up-to-date information about this company.
Search for the company's official website, LinkedIn page, and other reliable sources to gather the most accurate information.

If you cannot determine the company name from the document, focus on providing general industry information based on the job title and requirements.

Please provide the following information in JSON format:
1. Company founding date (if available) - search for when the company was established
2. Company size (number of employees) - look for employee count on LinkedIn or company website
3. Annual turnover/revenue (if available) - search for financial information
4. Company website URL - find the official website
5. LinkedIn URL - find the company's LinkedIn page
6. Main competitors - identify key competitors in the same industry
7. Industry details - provide comprehensive information about the industry
8. Typical clients - identify the types of clients or customers the company serves

Format your response as a valid JSON object with the following structure:
{
  "founding_date": "YYYY-MM-DD",
  "company_size": "e.g., 50-100 employees",
  "turnover": "e.g., $5-10 million",
  "website_url": "https://company-website.com",
  "linkedin_url": "https://linkedin.com/company/company-name",
  "competitors": "Competitor1, Competitor2, Competitor3",
  "industry_details": "Detailed description of the industry",
  "typical_clients": "Description of typical clients"
}

Only return the JSON object, no other text. If you cannot find information for a field, leave it as an empty string.
PROMPT;
    }


    /**
     * Prepare the prompt for salary comparison from document.
     *
     * @param string $documentText
     * @param array $jobDetailsData
     * @return string
     */
    private function prepareSalaryComparisonPromptFromDocument(string $documentText, array $jobDetailsData): string
    {
        // Extract job details from job details data, but don't rely on them exclusively
        $jobTitle = $jobDetailsData['job_title'] ?? '';
        $location = $jobDetailsData['location'] ?? '';
        $experienceLevel = $jobDetailsData['experience_level'] ?? '';
        $industryDetails = $jobDetailsData['industry_details'] ?? '';
        
        // If we don't have a job title, try to extract it from the document text
        if (empty($jobTitle)) {
            // Look for common patterns that might indicate a job title
            if (preg_match('/(?:position|job|role)(?:\s+title)?(?:\s*:|\s+is)\s+([^\.]+)/i', $documentText, $matches)) {
                $jobTitle = trim($matches[1]);
            } elseif (preg_match('/(?:hiring|recruiting|looking for|seeking)(?:\s+a)?\s+([A-Za-z0-9\s]+?(?:developer|engineer|manager|director|specialist|analyst|consultant|designer|architect))/i', $documentText, $matches)) {
                $jobTitle = trim($matches[1]);
            }
        }
        
        // If we don't have a location, try to extract it from the document text
        if (empty($location)) {
            // Look for common patterns that might indicate a location
            if (preg_match('/location(?:\s*:|\s+is)\s+([^\.]+)/i', $documentText, $matches)) {
                $location = trim($matches[1]);
            } elseif (preg_match('/(?:based in|located in|position in|job in|role in)\s+([A-Za-z0-9\s,]+)/i', $documentText, $matches)) {
                $location = trim($matches[1]);
            }
        }
        
        // If we don't have an experience level, try to extract it from the document text
        if (empty($experienceLevel)) {
            // Look for common patterns that might indicate experience level
            if (preg_match('/experience(?:\s+level)?(?:\s*:|\s+is|\s+required)?\s+([^\.]+?years?|senior|junior|mid|entry)/i', $documentText, $matches)) {
                $experienceLevel = trim($matches[1]);
            } elseif (preg_match('/(\d+\+?\s+years?(?:\s+of)?(?:\s+experience)?)/i', $documentText, $matches)) {
                $experienceLevel = trim($matches[1]);
            } elseif (preg_match('/(senior|junior|mid-level|entry-level)/i', $documentText, $matches)) {
                $experienceLevel = trim($matches[1]);
            }
        }
        
        return <<<PROMPT
You are an expert recruiter's assistant. I need you to provide salary comparison data for the job described in the following document:

IMPORTANT: The document text is the PRIMARY source of information. If the job title, location, or experience level extracted below is empty or incorrect, extract it directly from the document text.

If you cannot determine specific salary information for the exact job title and location, provide estimates based on similar roles in similar locations.

Document Text:
{$documentText}

Extracted Job Title: {$jobTitle}
Extracted Location: {$location}
Extracted Experience Level: {$experienceLevel}

IMPORTANT: If Google Search is enabled, please use it to search for accurate and up-to-date salary information for this role.
Search for salary data on sites like Glassdoor, Indeed, LinkedIn Salary, PayScale, and other reliable sources.

Please provide the following information in JSON format:
1. Average salary for this role in this location - search for current average salary data
2. Minimum salary range - find the lower end of the salary range
3. Maximum salary range - find the upper end of the salary range
4. Similar job postings (brief descriptions) - search for current job postings with similar titles and requirements
5. Salary data source - list the sources you used to gather this information

Format your response as a valid JSON object with the following structure:
{
  "average_salary": 75000,
  "min_salary": 65000,
  "max_salary": 85000,
  "similar_job_postings": "Brief descriptions of similar job postings",
  "salary_data_source": "Source of the salary data"
}

Only return the JSON object, no other text. If you cannot find information for a field, provide a reasonable estimate based on similar roles or nearby locations.
PROMPT;
    }

    /**
     * Prepare the prompt for search strings from document.
     *
     * @param string $documentText
     * @param array $jobDetailsData
     * @return string
     */
    private function prepareSearchStringsPromptFromDocument(string $documentText, array $jobDetailsData): string
    {
        // Extract job details from job details data, but don't rely on them exclusively
        $jobTitle = $jobDetailsData['job_title'] ?? '';
        $requiredSkills = $jobDetailsData['required_skills'] ?? '';
        $preferredSkills = $jobDetailsData['preferred_skills'] ?? '';
        $experienceLevel = $jobDetailsData['experience_level'] ?? '';
        $educationRequirements = $jobDetailsData['education_requirements'] ?? '';
        
        // If we don't have a job title, try to extract it from the document text
        if (empty($jobTitle)) {
            // Look for common patterns that might indicate a job title
            if (preg_match('/(?:position|job|role)(?:\s+title)?(?:\s*:|\s+is)\s+([^\.]+)/i', $documentText, $matches)) {
                $jobTitle = trim($matches[1]);
            } elseif (preg_match('/(?:hiring|recruiting|looking for|seeking)(?:\s+a)?\s+([A-Za-z0-9\s]+?(?:developer|engineer|manager|director|specialist|analyst|consultant|designer|architect))/i', $documentText, $matches)) {
                $jobTitle = trim($matches[1]);
            }
        }
        
        // If we don't have required skills, try to extract them from the document text
        if (empty($requiredSkills)) {
            // Look for common patterns that might indicate required skills
            if (preg_match('/(?:required|essential|necessary|must have|key)(?:\s+skills)?(?:\s*:|\s+include|\s+are)\s+([^\.]+)/i', $documentText, $matches)) {
                $requiredSkills = trim($matches[1]);
            } elseif (preg_match('/(?:skills|requirements|qualifications)(?:\s*:|\s+include|\s+are)\s+([^\.]+)/i', $documentText, $matches)) {
                $requiredSkills = trim($matches[1]);
            }
        }
        
        // If we don't have preferred skills, try to extract them from the document text
        if (empty($preferredSkills)) {
            // Look for common patterns that might indicate preferred skills
            if (preg_match('/(?:preferred|desirable|nice to have|bonus|plus)(?:\s+skills)?(?:\s*:|\s+include|\s+are)\s+([^\.]+)/i', $documentText, $matches)) {
                $preferredSkills = trim($matches[1]);
            }
        }
        
        return <<<PROMPT
You are an expert recruiter's assistant. I need you to generate search strings for the job described in the following document:

IMPORTANT: The document text is the PRIMARY source of information. If any of the extracted fields below are empty or incorrect, extract the information directly from the document text.

If you cannot find specific skills or requirements in the document, use your knowledge of the industry to suggest relevant search terms based on the job title and any other available information.

Document Text:
{$documentText}

Extracted Job Title: {$jobTitle}
Extracted Required Skills: {$requiredSkills}
Extracted Preferred Skills: {$preferredSkills}
Extracted Experience Level: {$experienceLevel}
Extracted Education Requirements: {$educationRequirements}

Please provide the following search strings in JSON format:
1. LinkedIn Boolean search string
2. Google X-ray search string for LinkedIn
3. Google X-ray search string for CVs/Resumes

Format your response as a valid JSON object with the following structure:
{
  "linkedin_boolean_string": "Detailed LinkedIn Boolean search string",
  "google_xray_linkedin_string": "Google X-ray search string for LinkedIn",
  "google_xray_cv_string": "Google X-ray search string for CVs/Resumes",
  "search_string_notes": "Any notes or tips for using these search strings"
}

Only return the JSON object, no other text.
PROMPT;
    }


    
    /**
     * Parse the company research response.
     *
     * @param string $response
     * @return array
     */
    private function parseCompanyResearchResponse(string $response): array
    {
        try {
            $response = preg_replace('/^```json\s*|\s*```$/', '', $response);
            // Try to parse the JSON response
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, extract information using regex
                $data = [];
                
                // Extract founding date
                if (preg_match('/founding_date["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $foundingDate = trim($matches[1], "\", ");
                    // Only set founding_date if it's a valid date or null if empty
                    $data['founding_date'] = !empty($foundingDate) ? $foundingDate : null;
                } else {
                    $data['founding_date'] = null;
                }
                
                // Extract company size
                if (preg_match('/company_size["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['company_size'] = trim($matches[1], "\", ");
                } else {
                    $data['company_size'] = "";
                }
                
                // Extract turnover
                if (preg_match('/turnover["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['turnover'] = trim($matches[1], "\", ");
                } else {
                    $data['turnover'] = "";
                }
                
                // Extract website URL
                if (preg_match('/website_url["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['website_url'] = trim($matches[1], "\", ");
                } else {
                    $data['website_url'] = "";
                }
                
                // Extract LinkedIn URL
                if (preg_match('/linkedin_url["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['linkedin_url'] = trim($matches[1], "\", ");
                } else {
                    $data['linkedin_url'] = "";
                }
                
                // Extract competitors
                if (preg_match('/competitors["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['competitors'] = trim($matches[1], "\", ");
                } else {
                    $data['competitors'] = "";
                }
                
                // Extract industry details
                if (preg_match('/industry_details["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['industry_details'] = trim($matches[1], "\", ");
                } else {
                    $data['industry_details'] = "";
                }
                
                // Extract typical clients
                if (preg_match('/typical_clients["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['typical_clients'] = trim($matches[1], "\", ");
                } else {
                    $data['typical_clients'] = "";
                }
            } else {
                // If JSON parsing succeeds, ensure founding_date is null if empty
                if (isset($data['founding_date']) && empty($data['founding_date'])) {
                    $data['founding_date'] = null;
                }
                
                // Ensure website_url and linkedin_url are set
                if (!isset($data['website_url'])) {
                    $data['website_url'] = "";
                }
                
                if (!isset($data['linkedin_url'])) {
                    $data['linkedin_url'] = "";
                }
            }
            
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

      /**
     * Parse the salary comparison response.
     *
     * @param string $response
     * @return array
     */
    private function parseSalaryComparisonResponse(string $response): array
    {
        try {
            $response = preg_replace('/^```json\s*|\s*```$/', '', $response);
            // Try to parse the JSON response
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, extract information using regex
                $data = [];
                
                // Extract average salary
                if (preg_match('/average_salary["\s]*:["\s]*(\d+)/', $response, $matches)) {
                    $data['average_salary'] = (float) $matches[1];
                }
                
                // Extract min salary
                if (preg_match('/min_salary["\s]*:["\s]*(\d+)/', $response, $matches)) {
                    $data['min_salary'] = (float) $matches[1];
                }

                // Extract max salary
                if (preg_match('/max_salary["\s]*:["\s]*(\d+)/', $response, $matches)) {
                    $data['max_salary'] = (float) $matches[1];
                }
                
                // Extract similar job postings
                if (preg_match('/similar_job_postings["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['similar_job_postings'] = trim($matches[1], "\", ");
                }
                
                // Extract salary data source
                if (preg_match('/salary_data_source["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['salary_data_source'] = trim($matches[1], "\", ");
                }
            }
            
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    
    /**
     * Parse the search strings response.
     *
     * @param string $response
     * @return array
     */
    private function parseSearchStringsResponse(string $response): array
    {
        try {
            $response = preg_replace('/^```json\s*|\s*```$/', '', $response);
            // Try to parse the JSON response
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {                
                // If JSON parsing fails, extract information using regex
                $data = [];
                
                // Extract LinkedIn Boolean string
                if (preg_match('/linkedin_boolean_string["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['linkedin_boolean_string'] = trim($matches[1], "\", ");
                }
                
                // Extract Google X-ray LinkedIn string
                if (preg_match('/google_xray_linkedin_string["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['google_xray_linkedin_string'] = trim($matches[1], "\", ");
                }
                
                // Extract Google X-ray CV string
                if (preg_match('/google_xray_cv_string["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['google_xray_cv_string'] = trim($matches[1], "\", ");
                }
                
                // Extract search string notes
                if (preg_match('/search_string_notes["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['search_string_notes'] = trim($matches[1], "\", ");
                }
            }
            
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    
    /**
     * Parse the keywords response.
     *
     * @param string $response
     * @return array
     */
    private function parseKeywordsResponse(string $response, string $translationLanguage = 'multi'): array
    {
        try {
            $response = preg_replace('/^```json\s*|\s*```$/', '', $response);            
            // Try to parse the JSON response
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, extract information using regex
                $data = [];
                
                // Extract keywords
                if (preg_match('/keywords["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['keywords'] = trim($matches[1], "\", ");
                }
                
                // Extract synonyms
                if (preg_match('/synonyms["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['synonyms'] = trim($matches[1], "\", ");
                }
                
                // Extract translations
                if (preg_match('/translations["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['translations'] = trim($matches[1], "\", ");
                }
            }
            
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }
    

    /**
     * Parse the questions response.
     *
     * @param string $response
     * @return array
     */
    private function parseQuestionsResponse(string $response): array
    {
        try {
            $response = preg_replace('/^```json\s*|\s*```$/', '', $response);
            // Try to parse the JSON response
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, extract information using regex
                $data = [];
                
                // Extract candidate questions
                if (preg_match('/candidate_questions["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['candidate_questions'] = trim($matches[1], "\", ");
                }
                
                // Extract recruiter questions
                if (preg_match('/recruiter_questions["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['recruiter_questions'] = trim($matches[1], "\", ");
                }
            }
            
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    

    /**
     * Parse the job details response.
     *
     * @param string $response
     * @return array
     */
    private function parseJobDetailsResponse(string $response): array
    {
        try {
            $response = preg_replace('/^```json\s*|\s*```$/', '', $response);
            // Try to parse the JSON response
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, extract information using regex
                $data = [];
                
                // Extract job title
                if (preg_match('/job_title["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['job_title'] = trim($matches[1], "\", ");
                }
                
                // Extract department
                if (preg_match('/department["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['department'] = trim($matches[1], "\", ");
                }
                
                // Extract location
                if (preg_match('/location["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['location'] = trim($matches[1], "\", ");
                }
                
                // Extract required skills
                if (preg_match('/required_skills["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['required_skills'] = trim($matches[1], "\", ");
                }
                
                // Extract preferred skills
                if (preg_match('/preferred_skills["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['preferred_skills'] = trim($matches[1], "\", ");
                }
                
                // Extract experience level
                if (preg_match('/experience_level["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['experience_level'] = trim($matches[1], "\", ");
                }
                
                // Extract education requirements
                if (preg_match('/education_requirements["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['education_requirements'] = trim($matches[1], "\", ");
                }
                
                // Extract employment type
                if (preg_match('/employment_type["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['employment_type'] = trim($matches[1], "\", ");
                }
                
                // Extract salary range
                if (preg_match('/salary_range["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['salary_range'] = trim($matches[1], "\", ");
                }
                
                // Extract company name
                if (preg_match('/company_name["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['company_name'] = trim($matches[1], "\", ");
                }
                
                // Extract overview
                if (preg_match('/overview["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['overview'] = trim($matches[1], "\", ");
                }
                
                // Extract responsibilities
                if (preg_match('/responsibilities["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['responsibilities'] = trim($matches[1], "\", ");
                }
                
                // Extract non-negotiable requirements
                if (preg_match('/requirements_non_negotiable["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['requirements_non_negotiable'] = trim($matches[1], "\", ");
                }
                
                // Extract preferred requirements
                if (preg_match('/requirements_preferred["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['requirements_preferred'] = trim($matches[1], "\", ");
                }
                
                // Extract benefits
                if (preg_match('/benefits["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['benefits'] = trim($matches[1], "\", ");
                }
                
                // Extract industry_details
                if (preg_match('/industry_details["\s]*:["\s]*([^"]+)/', $response, $matches)) {
                    $data['industry_details'] = trim($matches[1], "\", ");
                }
            }
            
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

}