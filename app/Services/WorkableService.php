<?php

namespace App\Services;

use App\Models\WorkableSetting;
use Illuminate\Support\Facades\Http;

class WorkableService
{
    /**
     * Retrieve all candidates from Workable.
     * Uses ?limit=100 for each request as recommended by Workable documentation.
     * Handles HTTP 429 responses by waiting the suggested Retry-After seconds.
     */
    public function listCandidates(WorkableSetting $setting): array
    {
        $candidates = [];
        // start with limit=100 (maximum allowed per docs)
        $next = "https://{$setting->subdomain}.workable.com/spi/v3/candidates?limit=100";


        while ($next) {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $setting->api_token,
                'Accept' => 'application/json',
            ])->get($next);

            if ($response->status() === 429) {
                // simple rate limit handling based on Workable docs
                $wait = (int) $response->header('Retry-After', 60);
                sleep($wait);
                continue; // retry the same request
            }

            if ($response->failed()) {
                throw new \Exception('Workable API error: ' . $response->body());
            }

            $data = $response->json();
            foreach ($data['candidates'] ?? [] as $candidate) {
                $candidates[] = $candidate;
            }
            $next = $data['paging']['next'] ?? null;
        }

        return $candidates;
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
