<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\SearchIndex;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SearchIndex> */
class SearchIndexFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'module' => fake()->randomElement(['properties', 'clients', 'contracts', 'complaints']),
            'searchable_type' => fake()->randomElement(['Property', 'Client', 'Contract', 'Complaint']),
            'searchable_id' => fake()->unique()->numberBetween(1, 100000),
            'title' => fake()->sentence(4),
            'content' => fake()->paragraph(),
            'index_data' => ['keywords' => fake()->words(5)],
            'indexed_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'is_active' => true,
        ];
    }
}
