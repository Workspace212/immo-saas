<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AppNotification> */
class AppNotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'created_by' => fake()->boolean(60) ? User::factory() : null,
            'notification_number' => fake()->optional()->unique()->bothify('NOT-####??'),
            'type' => fake()->randomElement(['appointment_reminder', 'complaint_update', 'contract_update', 'payment_due']),
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'data' => ['url' => fake()->url()],
            'priority' => fake()->randomElement(['low', 'normal', 'urgent', 'critical']),
            'status' => fake()->randomElement(['unread', 'read', 'archived']),
            'read_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'action_url' => fake()->optional()->url(),
            'related_type' => fake()->optional()->randomElement(['appointment', 'contract', 'complaint']),
            'related_id' => fake()->optional()->numberBetween(1, 1000),
        ];
    }
}
