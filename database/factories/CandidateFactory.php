<?php

namespace Database\Factories;

use App\Models\Candidate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    public function definition()
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->optional()->phoneNumber,
            'location' => $this->faker->optional()->city,
            'current_company' => $this->faker->optional()->company,
            'current_position' => $this->faker->optional()->jobTitle,
            'linkedin_url' => $this->faker->optional()->url,
            'resume_path' => null,
            'resume_text' => null,
            'match_score' => $this->faker->optional()->numberBetween(0, 100),
            'status' => $this->faker->randomElement(['new', 'contacted', 'interviewing', 'offered', 'hired', 'rejected', 'withdrawn']),
            'source' => $this->faker->randomElement(['upload', 'batch_upload', 'workable', 'manual']),
            'notes' => $this->faker->optional()->paragraph,
            'workable_id' => $this->faker->optional()->unique()->regexify('[a-z0-9]{10,20}'),
            'last_analyzed_at' => $this->faker->optional()->dateTimeThisYear(),
            'analysis_details' => null,
        ];
    }
}
