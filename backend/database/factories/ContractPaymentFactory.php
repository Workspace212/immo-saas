<?php

namespace Database\Factories;

use App\Models\ContractPayment;
use App\Models\ContractPaymentSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractPayment>
 */
class ContractPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_payment_schedule_id' => ContractPaymentSchedule::factory(),
            'received_by' => fake()->boolean(80) ? User::factory() : null,
            'payment_number' => fake()->unique()->bothify('PAY-####??'),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'amount' => fake()->randomFloat(2, 1000, 100000),
            'currency' => 'MAD',
            'payment_method' => fake()->randomElement([
                'cash',
                'bank_transfer',
                'check',
                'card',
                'mobile_payment',
                'other',
            ]),
            'reference' => fake()->optional()->bothify('REF-####??'),
            'bank_name' => fake()->optional()->company(),
            'status' => fake()->randomElement(['completed', 'cancelled', 'refunded']),
            'receipt_number' => fake()->optional()->bothify('REC-####??'),
            'receipt_generated_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'proof_file' => fake()->optional()->filePath(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
