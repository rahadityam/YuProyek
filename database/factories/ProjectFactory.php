<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+6 months'),
            'budget' => $this->faker->numberBetween(1000000, 100000000),
            'status' => 'open',
            'owner_id' => User::factory(),
            'wip_limits' => $this->faker->optional()->numberBetween(1, 10),
            'difficulty_weight' => 60,
            'priority_weight' => 40,
            'payment_calculation_type' => 'task',
        ];
    }
}