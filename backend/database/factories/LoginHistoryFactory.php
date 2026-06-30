<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LoginHistory> */
class LoginHistoryFactory extends Factory
{
    public function definition(): array
    {
        $successful = fake()->boolean(85);
        $loggedInAt = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => fake()->boolean(90) ? User::factory() : null,
            'logged_in_at' => $loggedInAt,
            'logged_out_at' => $successful ? fake()->optional()->dateTimeBetween($loggedInAt, 'now') : null,
            'successful' => $successful,
            'ip_address' => fake()->ipv4(),
            'browser' => fake()->userAgent(),
            'device' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'failure_reason' => $successful ? null : fake()->randomElement(['invalid_password', 'locked_account', 'unknown_email']),
        ];
    }
}
