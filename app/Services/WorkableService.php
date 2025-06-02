<?php

namespace App\Services;

use App\Models\WorkableSetting;
use Illuminate\Support\Facades\Http;

class WorkableService
{
    /**
     * Perform a GET request with basic rate limit handling.
     */
    protected function request(string $url, WorkableSetting $setting)
    {
        $attempts = 0;
        while (true) {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $setting->api_token,
                'Accept'        => 'application/json',
            ])->get($url);

            if ($response->status() === 429 && $attempts < 3) {
                $delay = (int) ($response->header('Retry-After', 60));
                sleep($delay);
                $attempts++;
                continue;
            }

            if ($response->failed()) {
                throw new \Exception('Workable API error: ' . $response->body());
            }

            return $response->json();
        }
    }

    /**
     * Retrieve all candidates matching the provided filters.
     */
    public function listCandidates(WorkableSetting $setting, array $params = []): array
    {
        $candidates = [];

        $query = array_merge(['limit' => 100], $params);
        $next = "https://{$setting->subdomain}.workable.com/spi/v3/candidates?" . http_build_query($query);

        while ($next) {
            $data = $this->request($next, $setting);
            foreach ($data['candidates'] ?? [] as $candidate) {
                $candidates[] = $candidate;
            }
            $next = $data['paging']['next'] ?? null;
        }

        return $candidates;
    }

    /**
     * Get a single candidate by ID.
     */
    public function getCandidate(WorkableSetting $setting, string $id): array
    {
        $url = "https://{$setting->subdomain}.workable.com/spi/v3/candidates/{$id}";
        return $this->request($url, $setting);
    }

    /**
     * Retrieve all jobs for the account.
     */
    public function listJobs(WorkableSetting $setting): array
    {
        $jobs = [];
        $next = "https://{$setting->subdomain}.workable.com/spi/v3/jobs?limit=100";

        while ($next) {
            $data = $this->request($next, $setting);
            foreach ($data['jobs'] ?? [] as $job) {
                $jobs[] = $job;
            }
            $next = $data['paging']['next'] ?? null;
        }

        return $jobs;
    }
}
