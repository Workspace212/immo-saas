<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Budget;
use App\Models\BudgetLine;
use App\Models\FinancialCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BudgetLine> */
class BudgetLineFactory extends Factory
{
    public function definition(): array
    {
        $planned = fake()->randomFloat(2, 1000, 100000);
        $actual = fake()->randomFloat(2, 0, $planned * 1.2);

        return [
            'agency_id' => Agency::factory(),
            'budget_id' => Budget::factory(),
            'financial_category_id' => fake()->boolean(80) ? FinancialCategory::factory() : null,
            'name' => fake()->unique()->words(3, true),
            'period_type' => fake()->randomElement(['annual', 'monthly']),
            'period_number' => fake()->numberBetween(1, 12),
            'planned_amount' => $planned,
            'actual_amount' => $actual,
            'variance_amount' => $planned - $actual,
            'currency' => 'MAD',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
