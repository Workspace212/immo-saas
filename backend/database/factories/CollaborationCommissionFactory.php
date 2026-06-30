<?php

namespace Database\Factories;

use App\Models\Collaboration;
use App\Models\CollaborationCommission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollaborationCommission> */
class CollaborationCommissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collaboration_id' => Collaboration::factory(),
            'agent_id' => User::factory(),
            'commission_role' => fake()->randomElement(['requesting_agent', 'owner_agent', 'other']),
            'commission_type' => fake()->randomElement(['percent', 'fixed']),
            'commission_value' => fake()->randomFloat(2, 1, 100),
            'calculated_amount' => fake()->optional()->randomFloat(2, 1000, 100000),
            'currency' => 'MAD',
            'status' => fake()->randomElement(['pending', 'validated', 'paid', 'cancelled']),
            'validated_by' => fake()->boolean(60) ? User::factory() : null,
            'validated_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
