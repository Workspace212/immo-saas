<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\FinancialClosing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FinancialClosing> */
class FinancialClosingFactory extends Factory
{
    public function definition(): array
    {
        $revenue = fake()->randomFloat(2, 10000, 1000000);
        $expense = fake()->randomFloat(2, 1000, $revenue);

        return [
            'agency_id' => Agency::factory(),
            'closed_by' => fake()->boolean(80) ? User::factory() : null,
            'closing_number' => fake()->unique()->bothify('CLOS-####??'),
            'closing_type' => fake()->randomElement(['monthly', 'annual']),
            'period_year' => now()->year,
            'period_month' => fake()->numberBetween(1, 12),
            'closed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'is_locked' => fake()->boolean(50),
            'locked_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'total_revenue' => $revenue,
            'total_expense' => $expense,
            'net_result' => $revenue - $expense,
            'status' => fake()->randomElement(['draft', 'closed', 'locked']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
