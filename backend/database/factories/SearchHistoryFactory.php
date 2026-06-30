<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SearchHistory> */
class SearchHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'search_query' => fake()->words(2, true),
            'module' => fake()->randomElement(['properties', 'clients', 'contracts', 'complaints']),
            'result_count' => fake()->numberBetween(0, 100),
            'duration_ms' => fake()->numberBetween(10, 2000),
            'filters' => ['city' => fake()->city()],
            'searched_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
