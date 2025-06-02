<?php

namespace App\Services;

use App\Models\WorkableSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class WorkableService
{
    private function request(): PendingRequest
    {
        return Http::withoutVerifying();
    }

    private function sendRequest(string $url, WorkableSetting $setting)
    {
        while (true) {
            $response = $this->request()->withHeaders([
                'Authorization' => 'Bearer ' . $setting->api_token,
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->status() === 429) {
                $retry = (int) ($response->header('Retry-After') ?? 1);
                sleep($retry);
                continue;
            }

            if ($response->failed()) {
                throw new \Exception('Workable API error: ' . $response->body());
            }

            return $response;
        }
    }

    public function listCandidates(WorkableSetting $setting, array $params = []): array
    {
        $candidates = [];
        $params['limit'] = $params['limit'] ?? 100;
        $base = "https://{$setting->subdomain}.workable.com/spi/v3/candidates";
        $next = $base . '?' . http_build_query($params);

        while ($next) {
            $response = $this->sendRequest($next, $setting);

            $data = $response->json();
            foreach ($data['candidates'] ?? [] as $candidate) {
                $candidates[] = $candidate;
            }
            $next = $data['paging']['next'] ?? null;
        }

        return $candidates;
    }

    public function listJobs(WorkableSetting $setting): array
    {
        $jobs = [];
        $next = "https://{$setting->subdomain}.workable.com/spi/v3/jobs?limit=100";

        while ($next) {
            $response = $this->sendRequest($next, $setting);

            $data = $response->json();
            foreach ($data['jobs'] ?? [] as $job) {
                $jobs[] = $job;
            }
            $next = $data['paging']['next'] ?? null;
        }

        return $jobs;
    }

    public function getCandidate(WorkableSetting $setting, string $id): array
    {
        $url = "https://{$setting->subdomain}.workable.com/spi/v3/candidates/{$id}";
        $response = $this->sendRequest($url, $setting);

        return $response->json();
    }
}
