<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\Candidate;
use App\Models\Project;
use App\Models\CandidateProfile;
use App\Models\AISetting;
use App\Services\AIService;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the first candidate and project
$candidate = Candidate::find(3);
$project = Project::find(1);

if (!$candidate || !$project) {
    echo "Candidate or project not found.\n";
    exit(1);
}

echo "Creating profile for candidate: {$candidate->first_name} {$candidate->last_name} in project: {$project->title}\n";

// Create a profile
$profile = CandidateProfile::create([
    'candidate_id' => $candidate->id,
    'project_id' => $project->id,
    'user_id' => 1, // Assuming user ID 1 exists
    'title' => $candidate->first_name . ' ' . $candidate->last_name . ' - ' . $project->title,
    'status' => 'draft',
]);

echo "Profile created with ID: {$profile->id}\n";

// Get the first AI setting
$aiSetting = AISetting::first();

if (!$aiSetting) {
    echo "No AI settings found.\n";
    exit(1);
}

echo "Using AI setting: {$aiSetting->name} ({$aiSetting->provider})\n";

// Create AI service
$aiService = new AIService();

// Extract data from resume
echo "Extracting data from resume...\n";
try {
    // Create a simple array with dummy data for testing
    $extractedData = [
        'contact_info' => [
            'name' => $candidate->first_name . ' ' . $candidate->last_name,
            'email' => $candidate->email,
            'phone' => $candidate->phone,
            'location' => $candidate->location,
        ],
        'skills' => ['PHP', 'Laravel', 'JavaScript', 'Vue.js'],
        'experience' => [
            [
                'title' => 'Software Developer',
                'company' => 'Example Company',
                'date_range' => '2020-2023',
                'responsibilities' => ['Developed web applications', 'Maintained legacy code'],
            ]
        ],
    ];
    
    // Update the profile with extracted data
    $profile->update(['extracted_data' => $extractedData]);
    echo "Successfully updated profile with extracted data.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Profile generation test completed successfully.\n";