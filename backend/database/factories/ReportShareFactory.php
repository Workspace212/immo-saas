<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Report;
use App\Models\ReportShare;
use App\Models\SavedReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReportShare> */
class ReportShareFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'report_id' => fake()->boolean(70) ? Report::factory() : null,
            'saved_report_id' => fake()->boolean(70) ? SavedReport::factory() : null,
            'shared_with_user_id' => User::factory(),
            'shared_by' => fake()->boolean(80) ? User::factory() : null,
            'role' => fake()->optional()->randomElement(['manager', 'agent', 'assistant']),
            'can_view' => true,
            'can_edit' => fake()->boolean(30),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+6 months'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
