<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Report;
use App\Models\SavedReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<SavedReport> */
class SavedReportFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'report_id' => Report::factory(),
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'configuration' => ['columns' => ['name', 'amount', 'status']],
            'is_shared' => fake()->boolean(40),
            'is_default' => fake()->boolean(20),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
