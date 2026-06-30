<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Mandate;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mandate>
 */
class MandateFactory extends Factory
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
            'owner_id' => fake()->boolean(60) ? Owner::factory() : null,
            'client_id' => fake()->boolean(40) ? Client::factory() : null,
            'assigned_agent_id' => fake()->boolean(80) ? User::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'mandate_number' => fake()->unique()->bothify('MAN-####??'),
            'party_type' => fake()->randomElement(['owner', 'client']),
            'mandate_type' => fake()->randomElement(['simple', 'exclusive', 'semi_exclusive']),
            'operation_type' => fake()->optional()->randomElement([
                'sale',
                'long_term_rent',
                'short_term_rent',
                'property_management',
            ]),
            'start_date' => $startDate,
            'end_date' => $startDate === null ? null : fake()->dateTimeBetween($startDate, '+1 year'),
            'status' => fake()->randomElement(['draft', 'active', 'expired', 'cancelled']),
            'commission_type' => fake()->optional()->randomElement(['percent', 'fixed', 'one_month_rent']),
            'commission_value' => fake()->optional()->randomFloat(2, 1, 100000),
            'commission_notes' => fake()->optional()->sentence(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
