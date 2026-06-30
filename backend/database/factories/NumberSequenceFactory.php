<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\NumberSequence;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NumberSequence> */
class NumberSequenceFactory extends Factory
{
    public function definition(): array
    {
        $key = fake()->unique()->randomElement(['property', 'contract', 'mandate', 'invoice', 'quote', 'complaint', 'payment']);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'sequence_key' => $key,
            'name' => ucfirst($key),
            'prefix' => strtoupper(substr($key, 0, 3)).'-',
            'suffix' => null,
            'number_length' => 5,
            'next_number' => fake()->numberBetween(1, 999),
            'is_active' => true,
        ];
    }
}
