<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Contract;
use App\Models\Property;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RentalUnit>
 */
class RentalUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->optional()->dateTimeBetween('-1 month', '+1 month');

        return [
            'agency_id' => Agency::factory(),
            'property_id' => fake()->boolean(90) ? Property::factory() : null,
            'contract_id' => fake()->boolean(70) ? Contract::factory() : null,
            'previous_rental_unit_id' => null,
            'assigned_agent_id' => fake()->boolean(80) ? User::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'rental_number' => fake()->unique()->bothify('RNT-####??'),
            'status' => fake()->randomElement(['draft', 'active', 'ended', 'cancelled']),
            'start_date' => $startDate,
            'end_date' => $startDate === null ? null : fake()->dateTimeBetween($startDate, '+1 year'),
            'rent_amount' => fake()->optional()->randomFloat(2, 1000, 50000),
            'currency' => 'MAD',
            'deposit_amount' => fake()->optional()->randomFloat(2, 1000, 100000),
            'deposit_status' => fake()->randomElement([
                'pending',
                'received',
                'partially_returned',
                'returned',
                'retained',
            ]),
            'is_renewal' => false,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
