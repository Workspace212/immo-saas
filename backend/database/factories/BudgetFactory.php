<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Budget;
use App\Models\FinancialCategory;
use App\Models\RevenueCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Budget> */
class BudgetFactory extends Factory
{
    public function definition(): array
    {
        $planned = fake()->randomFloat(2, 10000, 1000000);
        $consumed = fake()->randomFloat(2, 0, $planned);

        return [
            'agency_id' => Agency::factory(),
            'financial_category_id' => fake()->boolean(80) ? FinancialCategory::factory() : null,
            'revenue_center_id' => fake()->boolean(60) ? RevenueCenter::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'budget_number' => fake()->unique()->bothify('BUD-####??'),
            'name' => fake()->words(3, true),
            'budget_year' => now()->year,
            'period_type' => fake()->randomElement(['annual', 'monthly']),
            'period_number' => fake()->numberBetween(1, 12),
            'planned_amount' => $planned,
            'consumed_amount' => $consumed,
            'remaining_amount' => $planned - $consumed,
            'currency' => 'MAD',
            'alert_threshold_percent' => fake()->randomFloat(2, 70, 95),
            'alerts_enabled' => true,
            'status' => fake()->randomElement(['active', 'closed', 'cancelled']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
