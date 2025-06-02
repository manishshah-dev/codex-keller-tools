<?php

namespace App\Services;

use App\Models\WorkableSetting;
use Illuminate\Support\Facades\Http;

class WorkableService
{
    public function listCandidates(WorkableSetting $setting): array
    {
        $candidates = [];
        $next = "https://{$setting->subdomain}.workable.com/spi/v3/candidates";

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
