<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\BankReconciliation;
use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BankReconciliation> */
class BankReconciliationFactory extends Factory
{
    public function definition(): array
    {
        $system = fake()->randomFloat(2, 0, 1000000);
        $bank = $system + fake()->randomFloat(2, -5000, 5000);

        return [
            'agency_id' => Agency::factory(),
            'financial_account_id' => FinancialAccount::factory(),
            'validated_by' => fake()->boolean(70) ? User::factory() : null,
            'reconciliation_number' => fake()->unique()->bothify('REC-BANK-####??'),
            'period_start' => fake()->dateTimeBetween('-6 months', '-1 month'),
            'period_end' => fake()->dateTimeBetween('-1 month', 'now'),
            'bank_balance' => $bank,
            'system_balance' => $system,
            'difference_amount' => $bank - $system,
            'status' => fake()->randomElement(['draft', 'validated', 'cancelled']),
            'validated_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
