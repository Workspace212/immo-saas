<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Report;
use App\Models\SavedReport;
use App\Models\ScheduledReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ScheduledReport> */
class ScheduledReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'report_id' => Report::factory(),
            'saved_report_id' => fake()->boolean(50) ? SavedReport::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'name' => fake()->words(3, true),
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly']),
            'send_time' => fake()->time('H:i:s'),
            'recipients' => [fake()->safeEmail(), fake()->safeEmail()],
            'email_enabled' => true,
            'whatsapp_enabled' => fake()->boolean(40),
            'last_sent_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'next_run_at' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
