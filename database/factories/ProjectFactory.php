<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->bs,
            'job_title' => $this->faker->jobTitle,
            'company_name' => $this->faker->company,
            'department' => $this->faker->optional()->word,
            'location' => $this->faker->optional()->city,
            'description' => $this->faker->optional()->paragraph,
            'is_active' => true,
        ];
    }
}
