<?php

namespace App\Services;

use App\Models\WorkableSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WorkableService
{
    protected function sendRequest(WorkableSetting $setting, string $url, array $query = [])
    {
        $attempts = 0;

        while (true) {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $setting->api_token,
                'Accept' => 'application/json',
            ])->get($url, $query);

            if ($response->status() === 429 && $attempts < 5) {
                $attempts++;
                $retryAfter = (int) ($response->header('Retry-After', 1));
                sleep(max($retryAfter, 1));
                continue;
            }

            if ($response->failed()) {
                throw new \Exception('Workable API error: ' . $response->body());
            }

            return $response;
        }
    }

    public function listJobs(WorkableSetting $setting, array $params = []): array
    {
        $jobs = [];
        $params['limit'] = 100;
        $url = "https://{$setting->subdomain}.workable.com/spi/v3/jobs";
        $next = $url . '?' . http_build_query($params);

        while ($next) {
            $response = $this->sendRequest($setting, $next);
            $data = $response->json();
            foreach ($data['jobs'] ?? [] as $job) {
                $jobs[] = $job;
            }
            $next = $data['paging']['next'] ?? null;
        }

        return $jobs;
    }

    public function listCandidates(WorkableSetting $setting, array $params = []): array
    {
        $candidates = [];
        $params['limit'] = 100;
        $url = "https://{$setting->subdomain}.workable.com/spi/v3/candidates";
        $next = $url . '?' . http_build_query($params);

        while ($next) {
            $response = $this->sendRequest($setting, $next);
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
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $setting->api_token,
            'Accept' => 'application/json',
        ])->get($url);

        if ($response->failed()) {
            throw new \Exception('Workable API error: ' . $response->body());
        }

        return $response->json();
    }
}
