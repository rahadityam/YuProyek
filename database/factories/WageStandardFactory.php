<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;

class WageStandardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'job_category' => $this->faker->jobTitle,
            'task_price' => $this->faker->numberBetween(50000, 250000),
        ];
    }
}