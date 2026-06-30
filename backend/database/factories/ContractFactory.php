<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Contract;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
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
            'property_id' => fake()->boolean(80) ? Property::factory() : null,
            'previous_contract_id' => null,
            'assigned_agent_id' => fake()->boolean(80) ? User::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'contract_number' => fake()->unique()->bothify('CTR-####??'),
            'contract_type' => fake()->randomElement([
                'sale',
                'long_term_rent',
                'short_term_rent',
                'property_management',
            ]),
            'status' => fake()->randomElement(['draft', 'active', 'expired', 'cancelled', 'completed']),
            'start_date' => $startDate,
            'end_date' => $startDate === null ? null : fake()->dateTimeBetween($startDate, '+1 year'),
            'signed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'amount' => fake()->optional()->randomFloat(2, 1000, 5000000),
            'currency' => 'MAD',
            'deposit_amount' => fake()->optional()->randomFloat(2, 1000, 100000),
            'charges_amount' => fake()->optional()->randomFloat(2, 100, 10000),
            'agency_fee_type' => fake()->optional()->randomElement(['percent', 'fixed', 'one_month_rent']),
            'agency_fee_value' => fake()->optional()->randomFloat(2, 1, 100),
            'agency_fee_amount' => fake()->optional()->randomFloat(2, 1000, 100000),
            'agency_fee_is_manual' => false,
            'owner_amount' => fake()->optional()->randomFloat(2, 1000, 5000000),
            'owner_amount_is_manual' => false,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
