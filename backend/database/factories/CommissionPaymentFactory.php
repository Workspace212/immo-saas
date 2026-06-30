<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\CommissionPayment;
use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CommissionPayment> */
class CommissionPaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'commission_id' => Commission::factory(),
            'financial_account_id' => fake()->boolean(80) ? FinancialAccount::factory() : null,
            'paid_by' => fake()->boolean(80) ? User::factory() : null,
            'payment_number' => fake()->unique()->bothify('CPY-####??'),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'currency' => 'MAD',
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'check', 'card']),
            'reference' => fake()->optional()->bothify('REF-####??'),
            'receipt_number' => fake()->optional()->bothify('REC-####??'),
            'receipt_path' => fake()->optional()->filePath(),
            'status' => fake()->randomElement(['completed', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
