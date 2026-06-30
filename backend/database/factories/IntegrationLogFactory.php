<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Integration;
use App\Models\IntegrationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IntegrationLog> */
class IntegrationLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'integration_id' => Integration::factory(),
            'action' => fake()->randomElement(['sync_started', 'sync_completed', 'webhook_received', 'token_refreshed']),
            'result' => fake()->randomElement(['success', 'failed', 'warning']),
            'message' => fake()->optional()->sentence(),
            'context' => ['request_id' => fake()->uuid()],
            'logged_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
