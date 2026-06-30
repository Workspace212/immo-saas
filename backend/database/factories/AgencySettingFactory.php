<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\AgencySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AgencySetting> */
class AgencySettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'timezone' => 'Africa/Casablanca',
            'language' => fake()->randomElement(['fr', 'ar', 'en']),
            'default_currency' => 'MAD',
            'date_format' => 'd/m/Y',
            'settings' => ['week_start' => 'monday', 'notifications_enabled' => true],
        ];
    }
}
