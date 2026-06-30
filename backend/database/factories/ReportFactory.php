<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Report> */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'report_number' => fake()->unique()->bothify('RPT-####??'),
            'name' => fake()->randomElement(['Rapport ventes', 'Rapport loyers', 'Rapport rentabilité', 'Rapport visites']),
            'report_type' => fake()->randomElement(['financial', 'property', 'contract', 'activity', 'custom']),
            'category' => fake()->randomElement(['sales', 'rentals', 'finance', 'operations']),
            'parameters' => ['group_by' => fake()->randomElement(['month', 'agent', 'property'])],
            'filters' => ['status' => fake()->randomElement(['active', 'completed', 'pending'])],
            'period_start' => fake()->optional()->dateTimeBetween('-1 year', '-1 month'),
            'period_end' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'visibility' => fake()->randomElement(['private', 'agency', 'shared']),
            'is_favorite' => fake()->boolean(30),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
