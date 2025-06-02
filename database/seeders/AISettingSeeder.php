<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AISetting;

class AISettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create AI providers
        AISetting::create([
            'provider' => 'openai',
            'name' => 'OpenAI',
            'api_key' => 'sk-example-openai-key',
            'is_active' => true,
            'is_default' => true,
            'models' => ['gpt-4', 'gpt-3.5-turbo'],
            // 'capabilities' => [
            //     'job_description',
            //     'qualifying_questions',
            //     'salary_comparison',
            //     'search_strings',
            //     'keywords',
            //     'candidate_questions',
            //     'recruiter_questions',
            // ],
        ]);

        AISetting::create([
            'provider' => 'anthropic',
            'name' => 'Anthropic',
            'api_key' => 'sk-example-anthropic-key',
            'is_active' => true,
            'is_default' => false,
            'models' => ['claude-3-opus', 'claude-3-sonnet'],
            // 'capabilities' => [
            //     'job_description',
            //     'qualifying_questions',
            // ],
        ]);

        AISetting::create([
            'provider' => 'google',
            'name' => 'Google AI',
            'api_key' => 'example-google-key',
            'is_active' => true,
            'is_default' => false,
            'models' => ['gemini-pro'],
            // 'capabilities' => [
            //     'job_description',
            // ],
        ]);
    }
}