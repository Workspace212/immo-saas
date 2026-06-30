<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\SearchSuggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SearchSuggestion> */
class SearchSuggestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'module' => fake()->randomElement(['properties', 'clients', 'contracts', 'complaints']),
            'keyword' => fake()->unique()->word(),
            'frequency' => fake()->numberBetween(1, 500),
            'is_active' => true,
        ];
    }
}
