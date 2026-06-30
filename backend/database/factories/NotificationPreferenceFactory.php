<?php

namespace Database\Factories;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<NotificationPreference> */
class NotificationPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notification_type' => fake()->unique()->randomElement([
                'appointment_reminder',
                'complaint_update',
                'contract_update',
                'payment_due',
                'collaboration_update',
            ]),
            'internal_enabled' => true,
            'email_enabled' => fake()->boolean(80),
            'sms_enabled' => fake()->boolean(30),
            'whatsapp_enabled' => fake()->boolean(40),
            'push_enabled' => fake()->boolean(80),
        ];
    }
}
