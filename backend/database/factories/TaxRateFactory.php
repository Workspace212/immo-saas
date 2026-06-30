<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Tax;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TaxRate> */
class TaxRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(70) ? Agency::factory() : null,
            'tax_id' => Tax::factory(),
            'rate' => fake()->randomElement([0, 7, 10, 14, 20]),
            'starts_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'ends_at' => fake()->optional()->dateTimeBetween('now', '+2 years'),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
