<?php

namespace Database\Factories;

use App\Models\WorkableSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkableSettingFactory extends Factory
{
    protected $model = WorkableSetting::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company . ' Workable',
            'subdomain' => $this->faker->slug,
            'api_token' => $this->faker->sha256,
            'is_active' => true,
            'is_default' => false,
        ];
    }
}
