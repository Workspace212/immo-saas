<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FinancialAccount> */
class FinancialAccountFactory extends Factory
{
    public function definition(): array
    {
        $opening = fake()->randomFloat(2, 0, 100000);

        return [
            'agency_id' => Agency::factory(),
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'name' => fake()->randomElement(['Caisse principale', 'Compte bancaire', 'Compte espèces', 'Compte virtuel']),
            'code' => fake()->unique()->bothify('ACC-####??'),
            'account_type' => fake()->randomElement(['cash_register', 'bank', 'cash', 'virtual']),
            'currency' => 'MAD',
            'opening_balance' => $opening,
            'current_balance' => $opening + fake()->randomFloat(2, -10000, 50000),
            'bank_name' => fake()->optional()->company(),
            'account_number' => fake()->optional()->iban('MA'),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
