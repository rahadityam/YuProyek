<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;

class PaymentTermFactory extends Factory
{
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 week', '+1 week');
        return [
            'project_id' => Project::factory(),
            'name' => 'Termin ' . $this->faker->randomNumber(1),
            'start_date' => $startDate,
            'end_date' => $this->faker->dateTimeBetween($startDate, (clone $startDate)->modify('+2 weeks')),
        ];
    }
}