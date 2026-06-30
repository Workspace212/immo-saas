<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AppointmentReminder> */
class AppointmentReminderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'reminder_type' => fake()->randomElement([
                'before_15_minutes',
                'before_1_hour',
                'before_1_day',
                'custom',
            ]),
            'remind_at' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement(['pending', 'sent', 'failed', 'cancelled']),
            'sent_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'channel' => fake()->randomElement(['notification', 'email', 'sms', 'whatsapp']),
            'error_message' => fake()->optional()->sentence(),
        ];
    }
}
