<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\ApiWebhook;
use App\Models\ApiWebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ApiWebhookLog> */
class ApiWebhookLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'api_webhook_id' => ApiWebhook::factory(),
            'event' => fake()->randomElement(['property.created', 'contract.signed', 'invoice.paid']),
            'payload' => ['id' => fake()->numberBetween(1, 1000)],
            'response_body' => fake()->optional()->sentence(),
            'http_code' => fake()->optional()->randomElement([200, 201, 400, 500]),
            'duration_ms' => fake()->numberBetween(50, 3000),
            'status' => fake()->randomElement(['pending', 'success', 'failed']),
            'attempted_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'error_message' => fake()->optional()->sentence(),
        ];
    }
}
