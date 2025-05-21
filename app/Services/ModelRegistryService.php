<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ModelRegistryService
{
    protected $cacheKey = 'ai_model_registry';
    protected $cacheDuration = 60 * 60 * 24; // 24 hours in seconds
    protected $openRouterApiUrl = 'https://openrouter.ai/api/v1/models';
    protected $googleApiUrlTemplate = 'https://generativelanguage.googleapis.com/v1beta/models/?key={api_key}';
    protected $fallbackGoogleApiKey = 'AIzaSyC2B-njN0fVfeCEAjrbHGHGMV0LC0k-tVI';

    /**
     * Get the list of AI models, fetching from API if cache is empty or expired.
     * Returns an array of model IDs.
     *
     * @return array
     */
    public function getModels(): array
    {
        try {
            // Remember caches the result. If the key exists and hasn't expired, it returns the cached value.
            // Otherwise, it executes the closure, caches its result for the duration, and returns it.
            $models = Cache::remember($this->cacheKey, $this->cacheDuration, function () {
                return $this->fetchAndParseModels();
            });
            
            // Check if models is empty and use fallback if needed
            if (empty($models)) {
                Log::warning("Cache returned empty models array, using fallback");
                $models = $this->getFallbackModels();
            }
            
            return $models;
        } catch (Exception $e) {
            Log::error("Failed to get AI models from cache or API: " . $e->getMessage());
            return $this->getFallbackModels();
        }
    }

    /**
     * Force refresh the cache by fetching from the API immediately.
     *
     * @return array
     */
    public function refreshCache(): array
    {
         Log::info("Refreshing AI model registry cache from API.");
        try {
            $models = $this->fetchAndParseModels();
            Cache::put($this->cacheKey, $models, $this->cacheDuration);
            Log::info("Successfully refreshed AI model registry cache. Found " . count($models) . " models.");
            return $models;
        } catch (Exception $e) {
            Log::error("Failed to refresh AI model registry cache: " . $e->getMessage());
            // Optionally clear the cache on failure?
            // Cache::forget($this->cacheKey);
            return []; // Return empty array on failure
        }
    }

    /**
     * Fetches models from the OpenRouter API and parses the response.
     *
     * @return array Array of model IDs.
     * @throws Exception If the API call fails or parsing is unsuccessful.
     */
    protected function fetchAndParseModels(): array
    {
        // Initialize the provider models map
        $providerModelsMap = [];
        
        // Fetch models from OpenRouter API
        $openRouterModels = $this->fetchOpenRouterModels();
        if (!empty($openRouterModels)) {
            $providerModelsMap = array_merge($providerModelsMap, $openRouterModels);
        }
        
        // Fetch Google Gemini models
        $googleModels = $this->fetchGoogleModels();
        if (!empty($googleModels) && isset($googleModels['google'])) {
            // If we already have Google models from OpenRouter, merge them
            if (isset($providerModelsMap['google'])) {
                $providerModelsMap['google'] = array_unique(
                    array_merge($providerModelsMap['google'], $googleModels['google'])
                );
                sort($providerModelsMap['google']);
            } else {
                $providerModelsMap['google'] = $googleModels['google'];
            }
        }
        
        // If we have no models at all, use fallback
        if (empty($providerModelsMap)) {
            return $this->getFallbackModels();
        }
        
        // Sort providers alphabetically by key
        ksort($providerModelsMap);
        
        return $providerModelsMap;
    }
    
    /**
     * Fetches models from the OpenRouter API.
     *
     * @return array Array of models grouped by provider.
     */
    protected function fetchOpenRouterModels(): array
    {
        try {
            // WARNING: Using withoutVerifying() due to potential local SSL issues.
            // Remove this in production after ensuring proper CA certificate setup.
            $response = Http::withoutVerifying()->timeout(15)->get($this->openRouterApiUrl);
    
            if ($response->failed()) {
                Log::error("OpenRouter API request failed. Status: " . $response->status() . " Body: " . $response->body());
                return [];
            }
    
            $data = $response->json();
    
            if (!isset($data['data']) || !is_array($data['data'])) {
                Log::error("Invalid OpenRouter API response format. Data:", $data);
                return [];
            }
            
            // Process the data to group models by provider
            $providerModelsMap = [];
            foreach ($data['data'] as $modelData) {
                if (isset($modelData['id']) && is_string($modelData['id'])) {
                    // Split 'openai/gpt-4o-mini' into 'openai' and 'gpt-4o-mini'
                    $parts = explode('/', $modelData['id'], 2);
                    if (count($parts) === 2) {
                        $provider = $parts[0];
                        $modelName = $parts[1];
                        // Only get $modelName before colon if it exists
                        $modelName = explode(':', $modelName)[0];
                        
                        // Initialize provider array if it doesn't exist
                        if (!isset($providerModelsMap[$provider])) {
                            $providerModelsMap[$provider] = [];
                        }
                        
                        // Add model to the provider's list if not already present
                        if (!in_array($modelName, $providerModelsMap[$provider])) {
                            $providerModelsMap[$provider][] = $modelName;
                        }
                    } else {
                        Log::warning("Could not parse provider/model from OpenRouter ID: " . $modelData['id']);
                    }
                }
            }
            
            // Sort models within each provider alphabetically
            foreach ($providerModelsMap as $provider => &$models) {
                sort($models);
            }
            unset($models); // Unset reference

            // unset google models if they exist
            if (isset($providerModelsMap['google'])) {
                unset($providerModelsMap['google']);
            }
            
            return $providerModelsMap;
            
        } catch (Exception $e) {
            Log::error("Exception during OpenRouter API request: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Fetches Google Gemini models using the Google API.
     *
     * @return array Array of Google models.
     */
    protected function fetchGoogleModels(): array
    {
        try {
            // Try to get a Google API key from the database
            $googleApiKey = $this->getGoogleApiKey();
            
            // Replace the API key placeholder in the URL template
            $apiUrl = str_replace('{api_key}', $googleApiKey, $this->googleApiUrlTemplate);
            
            // Make the API request
            $response = Http::withoutVerifying()->timeout(15)->get($apiUrl);
            
            if ($response->failed()) {
                Log::error("Google API request failed. Status: " . $response->status() . " Body: " . $response->body());
                return ['google' => $this->getFallbackModels()['google']];
            }
            
            $data = $response->json();
            
            // Check if the response has the expected structure
            if (!isset($data['models']) || !is_array($data['models'])) {
                Log::error("Invalid Google API response format. Data:", $data);
                return ['google' => $this->getFallbackModels()['google']];
            }
            
            // Extract model names
            $googleModels = [];
            foreach ($data['models'] as $model) {
                if (isset($model['name']) && is_string($model['name'])) {
                    // Extract just the model name from the full path
                    $modelName = basename($model['name']);
                    
                    // Add model to the list if not already present
                    if (!in_array($modelName, $googleModels)) {
                        $googleModels[] = $modelName;
                    }
                }
            }
            
            // Sort models alphabetically
            sort($googleModels);
            
            return ['google' => $googleModels];
            
        } catch (Exception $e) {
            Log::error("Exception during Google API request: " . $e->getMessage());
            return ['google' => $this->getFallbackModels()['google']];
        }
    }
    
    /**
     * Get a Google API key from the database or use the fallback.
     *
     * @return string The API key to use.
     */
    protected function getGoogleApiKey(): string
    {
        try {
            // Try to get a Google API setting from the database
            $googleSetting = \App\Models\AISetting::where('provider', 'google')
                ->where('is_active', true)
                ->first();
            
            // If we found a setting with an API key, use it
            if ($googleSetting && !empty($googleSetting->api_key)) {
                return $googleSetting->api_key;
            }
            
            // Otherwise, use the fallback API key
            return $this->fallbackGoogleApiKey;
            
        } catch (Exception $e) {
            Log::error("Exception while getting Google API key: " . $e->getMessage());
            return $this->fallbackGoogleApiKey;
        }
    }

    /**
     * Clear the model registry cache.
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
        Log::info("Cleared AI model registry cache.");
    }
    
    /**
     * Get a fallback map of models in case the API call fails.
     *
     * @return array
     */
    protected function getFallbackModels(): array
    {
        Log::info("Using fallback model map");
        return [
            'openai' => ['gpt-4', 'gpt-4-turbo', 'gpt-3.5-turbo'],
            'anthropic' => ['claude-3-opus', 'claude-3-sonnet', 'claude-3-haiku'],
            'google' => ['gemini-pro', 'gemini-pro-vision'],
            'meta' => ['llama-3-70b', 'llama-3-8b']
        ];
    }
}