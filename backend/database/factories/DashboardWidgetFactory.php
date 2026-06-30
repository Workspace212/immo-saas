<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<DashboardWidget> */
class DashboardWidgetFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Revenue Overview',
            'Active Properties',
            'Upcoming Visits',
            'Open Complaints',
            'Contracts Summary',
        ]);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'widget_key' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'title' => $title,
            'widget_type' => fake()->randomElement(['stat_card', 'chart', 'table', 'calendar', 'list']),
            'position_x' => fake()->numberBetween(0, 8),
            'position_y' => fake()->numberBetween(0, 8),
            'width' => fake()->numberBetween(3, 12),
            'height' => fake()->numberBetween(2, 8),
            'visible_roles' => fake()->randomElements(['Manager', 'Assistant', 'Agent', 'Owner'], fake()->numberBetween(1, 3)),
            'display_order' => fake()->numberBetween(0, 20),
            'is_visible' => true,
            'settings' => [
                'refresh_interval' => fake()->randomElement([60, 300, 900]),
                'color' => fake()->hexColor(),
            ],
        ];
    }
}
