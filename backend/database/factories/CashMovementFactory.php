<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\CashMovement;
use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CashMovement> */
class CashMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'cash_account_id' => fake()->boolean(80) ? FinancialAccount::factory() : null,
            'bank_account_id' => fake()->boolean(60) ? FinancialAccount::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'movement_number' => fake()->unique()->bothify('CASH-####??'),
            'movement_type' => fake()->randomElement(['in', 'out', 'transfer']),
            'movement_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'amount' => fake()->randomFloat(2, 100, 50000),
            'currency' => 'MAD',
            'reference' => fake()->optional()->bothify('REF-####??'),
            'comment' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['draft', 'completed', 'cancelled']),
        ];
    }
}
