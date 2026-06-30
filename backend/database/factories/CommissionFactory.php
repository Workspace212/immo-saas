<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Commission;
use App\Models\Contract;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Commission> */
class CommissionFactory extends Factory
{
    public function definition(): array
    {
        $calculated = fake()->randomFloat(2, 1000, 100000);
        $paid = fake()->randomFloat(2, 0, $calculated);

        return [
            'agency_id' => Agency::factory(),
            'agent_id' => User::factory(),
            'contract_id' => fake()->boolean(80) ? Contract::factory() : null,
            'property_id' => fake()->boolean(80) ? Property::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'validated_by' => fake()->boolean(60) ? User::factory() : null,
            'commission_number' => fake()->unique()->bothify('COM-####??'),
            'operation_type' => fake()->randomElement(['sale', 'rental', 'property_management']),
            'commission_type' => fake()->randomElement(['percent', 'fixed']),
            'commission_value' => fake()->randomFloat(2, 1, 100),
            'base_amount' => fake()->randomFloat(2, 10000, 5000000),
            'calculated_amount' => $calculated,
            'paid_amount' => $paid,
            'remaining_amount' => $calculated - $paid,
            'currency' => 'MAD',
            'is_manual' => fake()->boolean(20),
            'status' => fake()->randomElement(['pending', 'validated', 'partially_paid', 'paid', 'cancelled']),
            'validated_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
