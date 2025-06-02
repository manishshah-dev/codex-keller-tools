<?php

namespace App\Services;

use App\Models\WorkableSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WorkableService
{
    /**
     * Retrieve all candidates from Workable.
     * Uses ?limit=100 for each request as recommended by Workable documentation.
     * Handles HTTP 429 responses by waiting the suggested Retry-After seconds.
     * Supports filtering by job shortcode, email, and creation date.
     */
    public function listCandidates(
        WorkableSetting $setting,
        ?string $shortcode = null,
        ?string $email = null,
        ?string $createdAfter = null, // Expects YYYY-MM-DD
        int $limit = 100
    ): array {
        $candidates = [];
        $queryParams = ['limit' => $limit];

        if ($shortcode) {
            $queryParams['shortcode'] = $shortcode;
        }
        if ($email) {
            $queryParams['email'] = $email;
        }
        if ($createdAfter) {
            // Format to ISO 8601 datetime for start of day UTC
            $queryParams['created_after'] = $createdAfter . 'T00:00:00Z';
        }

        $initialUrl = "https://{$setting->subdomain}.workable.com/spi/v3/candidates?" . http_build_query($queryParams);
        $nextUrl = $initialUrl;

        $maxPages = 100; // Safety break
        $currentPage = 0;

        Log::info("Starting Workable candidate fetch for subdomain: {$setting->subdomain}", ['filters' => $queryParams]);

        while ($nextUrl && $currentPage < $maxPages) {
            $currentPage++;
            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'Authorization' => 'Bearer ' . $setting->api_token,
                    'Accept' => 'application/json',
                ])->get($nextUrl);

                if ($response->status() === 429) {
                    $wait = (int) $response->header('Retry-After', 60);
                    Log::warning("Workable rate limit hit while fetching candidates. Waiting for {$wait} seconds.", ['url' => $nextUrl]);
                    sleep($wait);
                    continue; // Retry the same URL
                }

                $response->throw(); // Throw an exception for 4xx or 5xx status codes

                $data = $response->json();
                 if (!isset($data['candidates'])) {
                    Log::error('Workable API response missing "candidates" key while fetching candidates.', ['url' => $nextUrl, 'response_data' => $data]);
                    break;
                }

                foreach ($data['candidates'] as $candidate) {
                    $candidates[] = $candidate;
                }

                // Check for next page URL
                if (isset($data['paging']['next']) && Str::startsWith($data['paging']['next'], "https://")) {
                    $nextUrl = $data['paging']['next'];
                } else {
                    $nextUrl = null; // No more pages
                }

            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('Workable API request failed while fetching candidates.', [
                    'subdomain' => $setting->subdomain,
                    'url' => $nextUrl,
                    'status' => $e->response ? $e->response->status() : 'N/A',
                    'error_message' => $e->getMessage(),
                    'response_body' => $e->response ? $e->response->body() : 'N/A',
                ]);
                throw new \Exception('Workable API error fetching candidates: ' . $e->getMessage(), 0, $e);
            } catch (\Exception $e) {
                Log::error('Generic error while fetching Workable candidates.', [
                    'subdomain' => $setting->subdomain,
                    'url' => $nextUrl,
                    'error_message' => $e->getMessage(),
                ]);
                throw $e; // Re-throw generic exceptions
            }
        }

        if ($currentPage >= $maxPages) {
            Log::warning("Reached max pages ({$maxPages}) limit while fetching Workable candidates for subdomain: {$setting->subdomain}, filters: " . json_encode($queryParams));
        }

        Log::info("Fetched " . count($candidates) . " candidates from Workable for subdomain: {$setting->subdomain}", ['filters' => $queryParams]);
        return $candidates;
    }

    /**
     * Retrieve all jobs from Workable.
     * Uses ?limit=100 for each request.
     * Handles HTTP 429 responses by waiting the suggested Retry-After seconds.
     *
     * @param WorkableSetting $setting
     * @return array
     * @throws \Exception
     */
    public function listJobs(WorkableSetting $setting): array
    {
        $jobs = [];
        $url = "https://{$setting->subdomain}.workable.com/spi/v3/jobs?limit=100";
        $maxPages = 100; // Safety break to prevent infinite loops
        $currentPage = 0;

        Log::info("Starting Workable job fetch for subdomain: {$setting->subdomain}");

        while ($url && $currentPage < $maxPages) {
            $currentPage++;
            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'Authorization' => 'Bearer ' . $setting->api_token,
                    'Accept' => 'application/json',
                ])->get($url);

                if ($response->status() === 429) {
                    $wait = (int) $response->header('Retry-After', 60);
                    Log::warning("Workable rate limit hit. Waiting for {$wait} seconds.");
                    sleep($wait);
                    continue; // Retry the same URL
                }

                $response->throw(); // Throw an exception for 4xx or 5xx status codes

                $data = $response->json();
                if (!isset($data['jobs'])) {
                    Log::error('Workable API response missing "jobs" key.', ['url' => $url, 'response_data' => $data]);
                    break;
                }

                foreach ($data['jobs'] as $job) {
                    $jobs[] = $job;
                }

                // Check for next page URL
                if (isset($data['paging']['next']) && Str::startsWith($data['paging']['next'], "https://")) {
                    $url = $data['paging']['next'];
                } else {
                    $url = null; // No more pages
                }

            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('Workable API request failed while fetching jobs.', [
                    'subdomain' => $setting->subdomain,
                    'url' => $url,
                    'status' => $e->response ? $e->response->status() : 'N/A',
                    'error_message' => $e->getMessage(),
                    'response_body' => $e->response ? $e->response->body() : 'N/A',
                ]);
                throw new \Exception('Workable API error fetching jobs: ' . $e->getMessage(), 0, $e);
            } catch (\Exception $e) {
                Log::error('Generic error while fetching Workable jobs.', [
                    'subdomain' => $setting->subdomain,
                    'url' => $url,
                    'error_message' => $e->getMessage(),
                ]);
                throw $e; // Re-throw generic exceptions
            }
        }

        if ($currentPage >= $maxPages) {
            Log::warning("Reached max pages ({$maxPages}) limit while fetching Workable jobs for subdomain: {$setting->subdomain}.");
        }

        Log::info("Fetched " . count($jobs) . " jobs from Workable for subdomain: {$setting->subdomain}");
        return $jobs;
    }

    public function getCandidate(WorkableSetting $setting, string $id): array
    {
        $url = "https://{$setting->subdomain}.workable.com/spi/v3/candidates/{$id}";

        while (true) {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $setting->api_token,
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->status() === 429) {
                $wait = (int) $response->header('Retry-After', 60);
                sleep($wait);
                continue;
            }

            if ($response->failed()) {
                throw new \Exception('Workable API error: ' . $response->body());
            }

            return $response->json();
        }
    }
}
