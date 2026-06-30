<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\DashboardFavoriteFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DashboardFavoriteFilter> */
class DashboardFavoriteFilterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => User::factory(),
            'module' => fake()->randomElement(['properties', 'contracts', 'complaints', 'appointments']),
            'name' => fake()->unique()->words(3, true),
            'search_query' => fake()->optional()->word(),
            'filters' => [
                'status' => fake()->randomElement(['active', 'draft', 'pending', 'completed']),
                'city' => fake()->city(),
            ],
            'is_favorite' => true,
            'is_shared' => fake()->boolean(30),
            'display_order' => fake()->numberBetween(0, 20),
        ];
    }
}
