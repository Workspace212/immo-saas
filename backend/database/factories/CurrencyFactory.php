<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Currency> */
class CurrencyFactory extends Factory
{
    public function definition(): array
    {
        $currency = fake()->randomElement([
            ['name' => 'Moroccan Dirham', 'iso_code' => 'MAD', 'symbol' => 'MAD', 'rate' => 1],
            ['name' => 'Euro', 'iso_code' => 'EUR', 'symbol' => '€', 'rate' => 10.8],
            ['name' => 'US Dollar', 'iso_code' => 'USD', 'symbol' => '$', 'rate' => 10],
        ]);

        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'name' => $currency['name'],
            'iso_code' => $currency['iso_code'],
            'symbol' => $currency['symbol'],
            'exchange_rate' => $currency['rate'],
            'is_default' => $currency['iso_code'] === 'MAD',
            'is_active' => true,
        ];
    }
}
