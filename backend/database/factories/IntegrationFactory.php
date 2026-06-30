<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Integration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Integration> */
class IntegrationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'provider' => fake()->randomElement(['google', 'outlook', 'whatsapp', 'stripe', 'mailgun']),
            'integration_type' => fake()->randomElement(['calendar', 'payment', 'messaging', 'email']),
            'name' => fake()->words(2, true),
            'settings' => ['enabled_features' => ['sync', 'webhooks']],
            'credentials' => ['masked' => true],
            'is_active' => true,
            'last_synced_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
