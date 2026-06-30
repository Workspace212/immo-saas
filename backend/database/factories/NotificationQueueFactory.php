<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\NotificationQueue;
use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NotificationQueue> */
class NotificationQueueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'notification_template_id' => fake()->boolean(70) ? NotificationTemplate::factory() : null,
            'channel' => fake()->randomElement(['internal', 'email', 'sms', 'whatsapp', 'push']),
            'recipient' => fake()->optional()->safeEmail(),
            'subject' => fake()->optional()->sentence(4),
            'body' => fake()->paragraph(),
            'payload' => ['reference' => fake()->uuid()],
            'status' => fake()->randomElement(['pending', 'processing', 'sent', 'failed', 'cancelled']),
            'scheduled_at' => fake()->optional()->dateTimeBetween('now', '+1 week'),
            'sent_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'failed_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'error_message' => fake()->optional()->sentence(),
            'attempts' => fake()->numberBetween(0, 3),
        ];
    }
}
