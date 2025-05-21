<?php

namespace App\Services;

use App\Models\AISetting;
use App\Models\AIUsageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AIService
{
    /**
     * Call the OpenAI API to generate content.
     *
     * @param AISetting $aiSetting The specific AI Setting configuration to use
     * @param string $model The model to use (e.g., 'gpt-4', 'gpt-3.5-turbo')
     * @param string $prompt The prompt to send to the API
     * @param array $options Additional options for the API call
     * @param int $userId The ID of the user making the request
     * @param string $feature The feature being used (e.g., 'job_description')
     * @return array The response from the API
     * @throws Exception If the API call fails
     */
    // Changed first parameter from string $provider to AISetting $aiSetting
    public function generateContent(AISetting $aiSetting, string $model, string $prompt, array $options = [], int $userId, string $feature, bool $enable_search = false): array
    {
        // Removed entry debug log
        // Removed internal query, as the specific setting is now passed in

        // Initialize response data
        $responseData = [];
        $success = false;
        $tokensUsed = 0;
        $cost = 0;
        $content = '';
        
        // Append JSON instructions for CV analyzer feature
        if ($feature === 'cv_analyzer' && !str_contains($prompt, 'respond ONLY with a valid JSON')) {
            $prompt .= "\n\n**IMPORTANT INSTRUCTION:** Your response MUST be a valid JSON object only with the following structure:
{
  \"match_score\": 0.0-1.0,
  \"justification\": \"Brief explanation of the score\",
  \"requirement_breakdown\": [
    {
      \"requirement\": \"Requirement name\",
      \"match\": true/false,
      \"evidence\": \"Evidence from resume\",
      \"score\": 0.0-1.0
    }
  ],
  \"red_flags\": [\"Potential concern 1\", \"Potential concern 2\"],
  \"interview_questions\": [\"Question 1\", \"Question 2\"]
}

Do not include any text, markdown formatting, or code blocks outside the JSON. Do not use ```json or ``` markers around your response.";
        }

        try {
            // Make the API call based on the provider
            switch ($aiSetting->provider) { // Use provider type from the passed object
                case 'openai':
                    $response = $this->callOpenAI($aiSetting, $model, $prompt, $options);
                    break;
                case 'anthropic':
                    $response = $this->callAnthropic($aiSetting, $model, $prompt, $options);
                    break;
                case 'google':
                    $response = $this->callGoogle($aiSetting, $model, $prompt, $options, $enable_search);
                    break;
                default:
                    throw new Exception("Unsupported AI provider: {$aiSetting->provider}");
            }

            // Process the response
            $responseData = $response['data'];
            $success = $response['success'];
            $tokensUsed = $response['tokens_used'];
            $cost = $response['cost'];
            $content = $response['content'];

        } catch (Exception $e) {
            // Log error before re-throwing
            Log::error("AI API call failed for feature '{$feature}' using provider '{$aiSetting->provider}' model '{$model}': {$e->getMessage()}", [
                'setting_id' => $aiSetting->id,
                'exception' => $e, // Log the full exception if needed
            ]);
            // Removed logging for failed API call
            
            throw $e; // Re-throw the exception after logging
        }

        // Removed logging for successful API call

        $result = [
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'cost' => $cost,
            'data' => $responseData, // Include raw response data if needed
        ];
        // Removed success debug log
        return $result;
    }

    /**
     * Call the OpenAI API.
     *
     * @param AISetting $aiSetting The AI settings for OpenAI
     * @param string $model The model to use
     * @param string $prompt The prompt to send to the API
     * @param array $options Additional options for the API call
     * @return array The response from the API
     * @throws Exception If the API call fails
     */
    private function callOpenAI(AISetting $aiSetting, string $model, string $prompt, array $options = []): array
    {
        $apiKey = $aiSetting->api_key;
        $organizationId = $aiSetting->organization_id;

        $headers = [
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ];

        if ($organizationId) {
            $headers['OpenAI-Organization'] = $organizationId;
        }

        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 3000;

        $payload = [
            'model' => $model,
            'messages' => [
                // Use a more neutral system message or one based on the feature
                ['role' => 'system', 'content' => 'You are a helpful AI assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];

        try {
            // WARNING: Disabling SSL verification for local development due to cURL error 60.
            // Ensure proper CA certificate configuration in production.
            $response = Http::withoutVerifying()->withHeaders($headers)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            if ($response->failed()) {
                $error = $response->body();
                $error_msg = json_decode($error, true)['error']['message'] ?? 'Unknown error';
                throw new Exception($error_msg);
            }

            $responseData = $response->json();
            $content = $responseData['choices'][0]['message']['content'] ?? '';
            $tokensUsed = $responseData['usage']['total_tokens'] ?? 0;
            
            // Calculate cost based on model and tokens used
            // These are approximate costs and should be updated based on current pricing
            $costPerToken = 0;
            if ($model === 'gpt-4') {
                $costPerToken = 0.00003; // $0.03 per 1K tokens
            } elseif ($model === 'gpt-3.5-turbo') {
                $costPerToken = 0.000002; // $0.002 per 1K tokens
            }
            
            $cost = $tokensUsed * $costPerToken;

            return [
                'success' => true,
                'content' => $content,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            throw new Exception("OpenAI API call failed: {$e->getMessage()}");
        }
    }

    /**
     * Call the Anthropic API.
     *
     * @param AISetting $aiSetting The AI settings for Anthropic
     * @param string $model The model to use
     * @param string $prompt The prompt to send to the API
     * @param array $options Additional options for the API call
     * @return array The response from the API
     * @throws Exception If the API call fails
     */
    private function callAnthropic(AISetting $aiSetting, string $model, string $prompt, array $options = []): array
    {
        $apiKey = $aiSetting->api_key;

        $headers = [
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ];

        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 3000;

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];

        try {
            // WARNING: Disabling SSL verification for local development due to cURL error 60.
            // Ensure proper CA certificate configuration in production.
            $response = Http::withoutVerifying()->withHeaders($headers)
                ->post('https://api.anthropic.com/v1/messages', $payload);

            if ($response->failed()) {
                $error = $response->body();
                $error_msg = json_decode($error, true)['error']['message'] ?? 'Unknown error';
                throw new Exception($error_msg);
            }

            $responseData = $response->json();
            $content = $responseData['content'][0]['text'] ?? '';
            
            // Anthropic doesn't provide token usage in the response, so we'll estimate
            // Roughly 4 characters per token
            $tokensUsed = ceil(strlen($prompt) / 4) + ceil(strlen($content) / 4);
            
            // Calculate cost based on model and tokens used
            // These are approximate costs and should be updated based on current pricing
            $costPerToken = 0;
            if ($model === 'claude-3-opus') {
                $costPerToken = 0.00003; // $0.03 per 1K tokens
            } elseif ($model === 'claude-3-sonnet') {
                $costPerToken = 0.00001; // $0.01 per 1K tokens
            }
            
            $cost = $tokensUsed * $costPerToken;

            return [
                'success' => true,
                'content' => $content,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            throw new Exception("Anthropic API call failed: {$e->getMessage()}");
        }
    }

    /**
     * Call the Google API.
     *
     * @param AISetting $aiSetting The AI settings for Google
     * @param string $model The model to use
     * @param string $prompt The prompt to send to the API
     * @param array $options Additional options for the API call
     * @param bool $enable_search Whether to enable Google Search capability
     * @return array The response from the API
     * @throws Exception If the API call fails
     */
    private function callGoogle(AISetting $aiSetting, string $model, string $prompt, array $options = [], bool $enable_search = false): array
    {
        $apiKey = $aiSetting->api_key;

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 3000;

        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxTokens,
                'responseMimeType' => 'text/plain',
            ],
        ];
        
        // Add Google Search tool if enabled
        if ($enable_search) {
            if (str_starts_with($model, 'gemini-1.5')) {
                $payload['tools'] = [
                    [ 'google_search_retrieval' => new \stdClass() ]
                ];
            } elseif (str_starts_with($model, 'gemini-2.')) {
                $payload['tools'] = [
                    [ 'google_search' => new \stdClass() ]
                ];
            } 

            Log::info('Google Search enabled for API call', ['model' => $model]);
        }

        try {
            // WARNING: Disabling SSL verification for local development due to cURL error 60.
            // Ensure proper CA certificate configuration in production.
            $response = Http::withoutVerifying()
                ->withHeaders($headers)
                ->timeout(120)          // wait up to 2 minutes for the first byte
                ->connectTimeout(10)    // fail fast on network issues
                ->retry(2, 200, throw: false) // optional: 2 retries, 200 ms back-off
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", $payload);

            if ($response->failed()) {
                $error = $response->body();
                $error_msg = json_decode($error, true)['error']['message'] ?? 'Unknown error';
                throw new Exception($error_msg);
            }

            $responseData = $response->json();
            // Log the full raw response for debugging
            // Use Log::info to ensure visibility regardless of LOG_LEVEL
            Log::info('Raw Google API Response received.');
            Log::info('Raw Google API Response Body:', ['response_body' => json_encode($responseData)]); // Optionally log body if needed, ensure it's serializable
            
            // Safely access nested data, checking if 'candidates' exists and is not empty
            if (!empty($responseData['candidates']) && isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $content = $responseData['candidates'][0]['content']['parts'][0]['text'];
            } else {
                // Log a warning if the expected content structure is missing
                Log::warning('Google API response missing expected content structure (candidates[0].content.parts[0].text).', ['response_body' => json_encode($responseData)]);
                $content = ''; // Set content to empty if not found
            }
            Log::info('Extracted Google API Content:', ['content' => $content]);

            // Google doesn't provide token usage in the response, so we'll estimate
            // Roughly 4 characters per token
            $tokensUsed = ceil(strlen($prompt) / 4) + ceil(strlen($content) / 4);
            
            // Calculate cost based on model and tokens used
            // These are approximate costs and should be updated based on current pricing
            $costPerToken = 0;
            if ($model === 'gemini-pro') {
                $costPerToken = 0.000001; // $0.001 per 1K tokens
            }
            
            $cost = $tokensUsed * $costPerToken;

            return [
                'success' => true,
                'content' => $content,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'data' => $responseData,
            ];
        } catch (Exception $e) {
            // Log the specific exception message from the HTTP call
            Log::error("Google API HTTP request failed: " . $e->getMessage(), ['exception' => $e]);
            throw new Exception("Google API call failed: {$e->getMessage()}");
        }
    }
/**
 * Analyze a candidate's CV text against project requirements using AI.
 *
 * @param \App\Models\Project $project The project being analyzed.
 * @param AISetting $aiSetting The AI Setting configuration.
 * @param string $model The specific AI model to use.
 * @param ?\App\Models\AIPrompt $aiPrompt Optional specific prompt to use.
 * @param string $resumeText The extracted text from the candidate's resume.
 * @param string $requirementsText A formatted string of project requirements.
 * @return array ['match_score' => float|null, 'status' => string, 'analysis_details' => string|null, 'error' => string|null, 'tokens_used' => int, 'cost' => float]
 */
public function analyzeCvAgainstRequirements(
    \App\Models\Project $project, // Add project parameter
    AISetting $aiSetting,
    string $model,
    ?\App\Models\AIPrompt $aiPrompt,
    string $resumeText,
    string $requirementsText
): array {
    $userId = $project->user_id; // Get user ID from project
    $feature = 'cv_analyzer';
    Log::info("Starting CV analysis using {$aiSetting->provider} model {$model}. Setting ID: {$aiSetting->id}");

    // 1. Construct the Prompt
    // Use the specific prompt if provided, otherwise use the default JSON prompt
    if ($aiPrompt && !empty($aiPrompt->prompt_template)) {
        $promptTemplate = $aiPrompt->prompt_template;
        Log::info("Using specific prompt template ID: {$aiPrompt->id}");
    } else {
        Log::info("Using default JSON prompt template.");
        // Default/Generic Prompt asking for JSON output
        $promptTemplate = <<<PROMPT
You are an expert recruitment assistant. Analyze the following resume text based on the provided job requirements.
Evaluate how well the candidate matches the requirements, considering required skills/experience and weights.

**IMPORTANT:** Respond ONLY with a valid JSON object containing the following keys:
- "match_score": A numeric score between 0.0 (no match) and 1.0 (perfect match).
- "justification": A brief text explanation for the score.
- "requirement_breakdown": An array of objects, each detailing the match for a specific requirement (e.g., {"requirement": "JavaScript", "match": true/false, "evidence": "..."}).
- "red_flags": An array of strings listing potential concerns.
- "interview_questions": An array of strings with suggested interview questions.

**Job Requirements:**
{{requirements}}

**Candidate Resume Text:**
{{resume_text}}

**JSON Response:**
PROMPT;
    }

    // Replace all known placeholders in the chosen template
    $finalPrompt = str_replace(
        ['{{job_title}}', '{{company_name}}', '{{requirements}}', '{{resume_text}}'],
        [$project->job_title ?? 'N/A', $project->company_name ?? 'N/A', $requirementsText, $resumeText],
        $promptTemplate // Use the selected template
    );

     // Append JSON instructions to ensure consistent output format
     // Only append JSON instructions if they're not already in the prompt template
     if (!str_contains($finalPrompt, 'respond ONLY with a valid JSON') &&
         !str_contains($finalPrompt, 'MUST respond ONLY with a valid JSON')) {
         $finalPrompt .= "\n\n**IMPORTANT:** Respond ONLY with a valid JSON object containing the following keys:\n" .
                        '- "match_score": A numeric score between 0.0 (no match) and 1.0 (perfect match).\n' .
                        '- "justification": A brief text explanation for the score.\n' .
                        '- "requirement_breakdown": An array of objects with requirement details.\n' .
                        '- "red_flags": An array of strings listing potential concerns.\n' .
                        '- "interview_questions": An array of suggested interview questions.';
     }

    // Define default options, allow overrides if needed via $aiPrompt or future params
    $options = [
        'temperature' => $aiPrompt->temperature ?? 0.5, // Lower temperature for more factual analysis
        'max_tokens' => $aiPrompt->max_tokens ?? 3000,   // Adjust as needed (restored from test)
    ];

    // Log the final prompt being sent (using info level)
    Log::info('Final prompt for CV analysis:', ['prompt' => $finalPrompt]);

    // 2. Call the AI Service (using existing generateContent)
    try {
        $aiResponse = $this->generateContent(
            $aiSetting,
            $model,
            $finalPrompt,
            $options,
            $userId,
            $feature
        );

        $rawContent = trim($aiResponse['content']);
        Log::debug("Raw AI response for CV analysis: " . $rawContent);

        // 3. Parse the Response (Prioritize JSON)
        $matchScore = null;
        $analysisDetails = $rawContent; // Keep raw content as fallback detail
        $parsedData = null;

        try {
            // Enhanced JSON extraction and cleaning
            // First, try to find JSON within markdown code blocks
            if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $rawContent, $matches)) {
                $jsonString = trim($matches[1]);
            } else {
                // If no code blocks, try to extract anything that looks like JSON
                // Remove any non-JSON text before the first { and after the last }
                if (preg_match('/(\{[\s\S]*\})/', $rawContent, $matches)) {
                    $jsonString = trim($matches[1]);
                } else {
                    // Just use the raw content as a last resort
                    $jsonString = $rawContent;
                }
            }
            
            // Try to parse the JSON
            $parsedData = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($parsedData['match_score']) && is_numeric($parsedData['match_score'])) {
                $potentialScore = (float)$parsedData['match_score'];
                if ($potentialScore >= 0.0 && $potentialScore <= 1.0) {
                    $matchScore = $potentialScore;
                    // Store the whole parsed data as details if JSON is valid
                    $analysisDetails = $parsedData;
                    Log::info("Successfully parsed match_score {$matchScore} from JSON response.");
                    
                    // Validate and ensure all expected fields exist
                    if (!isset($parsedData['justification'])) {
                        $parsedData['justification'] = "No justification provided.";
                    }
                    
                    if (!isset($parsedData['requirement_breakdown']) || !is_array($parsedData['requirement_breakdown'])) {
                        $parsedData['requirement_breakdown'] = [];
                    }
                    
                    if (!isset($parsedData['red_flags']) || !is_array($parsedData['red_flags'])) {
                        $parsedData['red_flags'] = [];
                    }
                    
                    if (!isset($parsedData['interview_questions']) || !is_array($parsedData['interview_questions'])) {
                        $parsedData['interview_questions'] = [];
                    }
                    
                    // Update analysis details with the validated data
                    $analysisDetails = $parsedData;
                } else {
                    Log::warning("Parsed JSON match_score '{$potentialScore}' is outside the valid 0.0-1.0 range.");
                    $parsedData = null; // Invalidate parsed data if score is bad
                }
            } else {
                 Log::warning("AI response was not valid JSON or missing 'match_score'. Error: " . json_last_error_msg());
                 $parsedData = null; // Ensure parsedData is null if JSON is invalid
            }
        } catch (\Exception $e) {
             Log::error("Error during JSON decoding: " . $e->getMessage());
             $parsedData = null;
        }

        // Fallback to Regex if JSON parsing failed or didn't yield a score
        if ($matchScore === null) {
            Log::info("Falling back to regex parsing for score extraction.");
            // Try to find "Overall Match Score: XX%" pattern
            if (preg_match('/Overall Match Score:\s*(\d+)%?/i', $rawContent, $matches)) {
                $potentialScore = (int)$matches[1];
                if ($potentialScore >= 0 && $potentialScore <= 100) {
                    $matchScore = $potentialScore / 100.0;
                    Log::info("Extracted score {$matchScore} from 'Overall Match Score: XX%' pattern (fallback).");
                } else { Log::warning("Regex fallback: Extracted score '{$potentialScore}%' is outside the valid 0-100 range."); }
            }
            // Fallback: Try to find the first standalone percentage value
            elseif (preg_match('/(\d+)%/i', $rawContent, $matches)) {
                 $potentialScore = (int)$matches[1];
                 if ($potentialScore >= 0 && $potentialScore <= 100) {
                     $matchScore = $potentialScore / 100.0;
                     Log::info("Extracted score {$matchScore} from first standalone percentage pattern (fallback).");
                 } else { Log::warning("Regex fallback: Extracted standalone percentage '{$potentialScore}%' is outside the valid 0-100 range."); }
            }
            // Fallback: Try to find the first standalone decimal value between 0 and 1
             elseif (preg_match('/\b(0\.\d+|1\.0|0)\b/', $rawContent, $matches)) {
                 $potentialScore = (float)$matches[1];
                 if ($potentialScore >= 0.0 && $potentialScore <= 1.0) {
                     $matchScore = $potentialScore;
                     Log::info("Extracted score {$matchScore} from first standalone decimal pattern (fallback).");
                 } else { Log::warning("Regex fallback: Extracted decimal '{$potentialScore}' is outside the valid 0.0-1.0 range."); }
             }

             if ($matchScore === null) {
                  Log::warning("Could not extract a valid numeric match score using JSON or regex fallbacks.");
             }
        }

        // 4. Return Structured Result
        return [
            'match_score' => $matchScore,
            'status' => $matchScore !== null ? 'analyzed' : 'analysis_failed', // Set status based on score extraction
            'analysis_details' => $analysisDetails, // Store the full AI response
            'error' => $matchScore === null ? 'Failed to parse match score from AI response.' : null,
            'tokens_used' => $aiResponse['tokens_used'],
            'cost' => $aiResponse['cost'],
        ];

    } catch (Exception $e) {
        Log::error("CV analysis failed for Setting ID {$aiSetting->id}, Model {$model}: {$e->getMessage()}");
        return [
            'match_score' => null,
            'status' => 'analysis_failed',
            'analysis_details' => null,
            'error' => "AI API call failed: " . $e->getMessage(),
            'tokens_used' => 0, // No tokens used if API call failed outright
            'cost' => 0,
        ];
    }
}

// Removed parseJobDescriptionContent method as we now expect JSON directly from AI
}