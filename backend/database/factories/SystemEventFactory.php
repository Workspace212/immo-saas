<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\SystemEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SystemEvent> */
class SystemEventFactory extends Factory
{
    public function definition(): array
    {
        $resolved = fake()->boolean(50);

        return [
            'agency_id' => fake()->boolean(70) ? Agency::factory() : null,
            'user_id' => fake()->boolean(60) ? User::factory() : null,
            'resolved_by' => $resolved ? User::factory() : null,
            'event_key' => fake()->randomElement(['queue_failed', 'sync_error', 'payment_webhook', 'backup_completed']),
            'level' => fake()->randomElement(['info', 'warning', 'error', 'critical']),
            'message' => fake()->sentence(),
            'details' => ['context' => fake()->sentence()],
            'is_resolved' => $resolved,
            'resolved_at' => $resolved ? fake()->dateTimeBetween('-1 week', 'now') : null,
            'occurred_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
