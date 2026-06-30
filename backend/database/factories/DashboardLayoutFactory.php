<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\DashboardLayout;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DashboardLayout> */
class DashboardLayoutFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement(['Main Dashboard', 'Sales Dashboard', 'Rental Dashboard', 'Operations Dashboard']);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'is_default' => fake()->boolean(30),
            'is_shared' => fake()->boolean(40),
            'layout' => [
                'columns' => 12,
                'widgets' => [
                    ['key' => 'revenue-overview', 'x' => 0, 'y' => 0, 'w' => 6, 'h' => 4],
                    ['key' => 'upcoming-visits', 'x' => 6, 'y' => 0, 'w' => 6, 'h' => 4],
                ],
            ],
            'settings' => [
                'theme' => fake()->randomElement(['light', 'dark']),
            ],
        ];
    }
}
