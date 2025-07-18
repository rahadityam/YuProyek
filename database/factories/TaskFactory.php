<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['To Do', 'In Progress', 'Review', 'Done']),
            'project_id' => Project::factory(),
            'assigned_to' => User::factory(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}