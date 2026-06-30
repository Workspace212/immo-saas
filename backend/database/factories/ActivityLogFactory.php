<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ActivityLog> */
class ActivityLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'module' => fake()->randomElement(['agenda', 'finance', 'reports', 'notifications']),
            'action' => fake()->randomElement(['viewed', 'exported', 'validated', 'sent']),
            'description' => fake()->sentence(),
            'level' => fake()->randomElement(['info', 'warning', 'error']),
            'metadata' => ['request_id' => fake()->uuid()],
            'logged_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
