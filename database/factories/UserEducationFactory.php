<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserEducationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'level' => $this->faker->randomElement(['High School', 'Bachelors', 'Masters']),
            'institution' => $this->faker->company() . ' University',
            'major' => $this->faker->jobTitle(),
            'graduation_year' => $this->faker->year(),
        ];
    }
}