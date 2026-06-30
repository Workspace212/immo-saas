<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SavedSearch> */
class SavedSearchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(3, true),
            'module' => fake()->randomElement(['properties', 'clients', 'contracts', 'complaints']),
            'search_query' => fake()->optional()->word(),
            'filters' => ['status' => fake()->randomElement(['active', 'draft', 'pending'])],
            'is_favorite' => fake()->boolean(50),
            'is_active' => true,
        ];
    }
}
