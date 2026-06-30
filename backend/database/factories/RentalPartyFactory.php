<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\RentalParty;
use App\Models\RentalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalParty>
 */
class RentalPartyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rental_unit_id' => RentalUnit::factory(),
            'client_id' => Client::factory(),
            'party_type' => fake()->randomElement(['tenant', 'guarantor']),
            'role' => fake()->randomElement(['primary', 'secondary']),
            'display_order' => fake()->numberBetween(0, 10),
            'signed' => fake()->boolean(),
            'signature_date' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
