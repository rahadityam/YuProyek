<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => 'certificate',
            'title' => $this->faker->sentence(3),
            'file_path' => 'user-documents/' . $this->faker->uuid . '.pdf',
        ];
    }
}