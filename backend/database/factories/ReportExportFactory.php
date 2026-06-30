<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Report;
use App\Models\ReportExport;
use App\Models\SavedReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReportExport> */
class ReportExportFactory extends Factory
{
    public function definition(): array
    {
        $format = fake()->randomElement(['pdf', 'excel', 'csv']);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'report_id' => fake()->boolean(80) ? Report::factory() : null,
            'saved_report_id' => fake()->boolean(50) ? SavedReport::factory() : null,
            'requested_by' => fake()->boolean(80) ? User::factory() : null,
            'export_number' => fake()->unique()->bothify('REXP-####??'),
            'export_format' => $format,
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed']),
            'file_path' => fake()->optional()->filePath(),
            'file_name' => 'report.'.($format === 'excel' ? 'xlsx' : $format),
            'file_size' => fake()->optional()->numberBetween(1000, 5000000),
            'parameters' => ['locale' => 'fr'],
            'started_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'completed_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'failed_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'error_message' => fake()->optional()->sentence(),
        ];
    }
}
