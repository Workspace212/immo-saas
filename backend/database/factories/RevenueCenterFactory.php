<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Property;
use App\Models\RevenueCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RevenueCenter> */
class RevenueCenterFactory extends Factory
{
    public function definition(): array
    {
        $revenue = fake()->randomFloat(2, 10000, 1000000);
        $expense = fake()->randomFloat(2, 1000, $revenue);

        return [
            'agency_id' => Agency::factory(),
            'property_id' => fake()->boolean(70) ? Property::factory() : null,
            'agent_id' => fake()->boolean(70) ? User::factory() : null,
            'name' => fake()->company(),
            'code' => fake()->unique()->bothify('RC-####??'),
            'center_type' => fake()->randomElement(['agency', 'property', 'agent', 'project']),
            'project_name' => fake()->optional()->words(3, true),
            'revenue_total' => $revenue,
            'expense_total' => $expense,
            'profit_total' => $revenue - $expense,
            'statistics' => ['margin_percent' => fake()->randomFloat(2, 5, 60)],
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
