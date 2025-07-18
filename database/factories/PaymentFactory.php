<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;
use App\Models\Payment;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $approved = $this->faker->boolean();
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'payment_type' => $this->faker->randomElement(['task', 'termin', 'full', 'other']),
            'payment_name' => 'Payment for ' . $this->faker->words(2, true),
            'bank_account' => $this->faker->creditCardNumber,
            'amount' => $this->faker->numberBetween(50000, 2000000),
            'status' => $approved ? Payment::STATUS_APPROVED : Payment::STATUS_DRAFT,
            'approved_at' => $approved ? now() : null,
            'approved_by' => $approved ? User::factory() : null,
        ];
    }
}