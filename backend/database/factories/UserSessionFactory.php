<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<UserSession> */
class UserSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => User::factory(),
            'session_token' => Str::random(64),
            'ip_address' => fake()->ipv4(),
            'browser' => fake()->userAgent(),
            'device' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'location' => fake()->optional()->city(),
            'last_activity_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'is_active' => fake()->boolean(80),
            'force_logged_out' => fake()->boolean(10),
            'force_logged_out_at' => fake()->optional()->dateTimeBetween('-1 day', 'now'),
        ];
    }
}
