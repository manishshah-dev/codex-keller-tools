<?php

namespace Database\Factories;

use App\Models\WorkableJob;
use App\Models\WorkableSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkableJobFactory extends Factory
{
    protected $model = WorkableJob::class;

    public function definition()
    {
        return [
            'workable_setting_id' => WorkableSetting::factory(),
            'workable_job_id' => $this->faker->unique()->regexify('[a-z0-9]{6,10}'),
            'title' => $this->faker->jobTitle,
            'full_title' => $this->faker->jobTitle . ' - ' . $this->faker->city,
            'shortcode' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'state' => $this->faker->randomElement(['published', 'archived', 'draft']),
            'department' => $this->faker->optional()->word,
            'url' => $this->faker->url,
            'application_url' => $this->faker->url,
            'shortlink' => $this->faker->url,
            'location_str' => $this->faker->city . ', ' . $this->faker->country,
            'country' => $this->faker->country,
            'country_code' => $this->faker->countryCode,
            'region' => $this->faker->state,
            'city' => $this->faker->city,
            'zip_code' => $this->faker->postcode,
            'telecommuting' => $this->faker->boolean,
            'workplace_type' => $this->faker->randomElement(['remote', 'on_site', 'hybrid']),
            'salary_currency' => $this->faker->currencyCode,
            'raw_location_data' => ['original_workable_location_field' => $this->faker->address],
            'raw_data' => ['full_workable_payload' => $this->faker->text],
            'workable_created_at' => $this->faker->dateTimeThisYear(),
            'workable_updated_at' => $this->faker->optional()->dateTimeThisYear(),
        ];
    }
}
