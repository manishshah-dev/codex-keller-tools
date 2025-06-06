<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Http;

class BrightHireService
{
    public function getTranscript(IntegrationSetting $setting, string $interviewId): ?string
    {
        $url = "https://api.brighthire.ai/interviews/{$interviewId}/transcript";
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $setting->api_token,
            'Accept' => 'application/json',
        ])->get($url);

        if ($response->failed()) {
            return null;
        }

        return $response->json('transcript');
    }
}
