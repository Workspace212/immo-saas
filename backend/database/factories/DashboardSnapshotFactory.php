<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\DashboardSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DashboardSnapshot> */
class DashboardSnapshotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'created_by' => fake()->boolean(70) ? User::factory() : null,
            'snapshot_key' => fake()->unique()->randomElement([
                'daily-kpis',
                'monthly-kpis',
                'sales-performance',
                'rental-performance',
            ]),
            'period_type' => fake()->randomElement(['daily', 'monthly']),
            'snapshot_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'metric_values' => [
                'properties_count' => fake()->numberBetween(10, 500),
                'contracts_count' => fake()->numberBetween(1, 100),
                'revenue' => fake()->randomFloat(2, 10000, 1000000),
            ],
            'comparison_values' => [
                'revenue_change_percent' => fake()->randomFloat(2, -30, 30),
            ],
            'metadata' => [
                'generated_by' => 'system',
            ],
        ];
    }
}
