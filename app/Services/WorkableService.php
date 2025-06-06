<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WorkableService
{
    public function listCandidates(IntegrationSetting $setting, array $params=[]): array
    {
        $candidates = [];
        $params['limit'] = 100;
        $url = "https://{$setting->subdomain}.workable.com/spi/v3/candidates";
        $next = $url . '?' . http_build_query($params);
        // dd($next);

        while ($next) {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $setting->api_token,
                'Accept' => 'application/json',
            ])->get($next);

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

    public function getCandidate(IntegrationSetting $setting, string $id): array
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

    public function listJobs(IntegrationSetting $setting): array
    {
        $jobs = [];
        $next = "https://{$setting->subdomain}.workable.com/spi/v3/jobs?limit=100";

        while ($next) {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $setting->api_token,
                'Accept' => 'application/json',
            ])->get($next);

            if ($response->failed()) {
                throw new \Exception('Workable API error: ' . $response->body());
            }

            $data = $response->json();
            foreach ($data['jobs'] ?? [] as $job) {
                $jobs[] = $job;
            }
            $next = $data['paging']['next'] ?? null;
        }

        return $jobs;
    }

    public function listJobCandidates(IntegrationSetting $setting, string $job_shortcode): array
    {
        $candidates = [];
        // $next = "https://{$setting->subdomain}.workable.com/spi/v3/jobs/{$job_shortcode}/candidates?limit=100";
        $next = "https://{$setting->subdomain}.workable.com/spi/v3/candidates/{$id}?shortcode={$job_shortcode}&limit=100";

        while ($next) {
            $response = Http::withoutVerifying()->withHeaders([
                'Authorization' => 'Bearer ' . $setting->api_token,
                'Accept' => 'application/json',
            ])->get($next);

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
}
