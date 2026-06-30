<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Collaboration;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Collaboration> */
class CollaborationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'property_id' => Property::factory(),
            'client_id' => fake()->boolean(70) ? Client::factory() : null,
            'requesting_agent_id' => User::factory(),
            'owner_agent_id' => User::factory(),
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'collaboration_number' => fake()->unique()->bothify('COL-####??'),
            'collaboration_type' => fake()->randomElement(['property_client_match', 'property_visit', 'sale', 'rental', 'other']),
            'status' => fake()->randomElement(['pending', 'accepted', 'rejected', 'in_progress', 'completed', 'cancelled']),
            'request_message' => fake()->optional()->paragraph(),
            'rejection_reason' => fake()->optional()->sentence(),
            'accepted_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'rejected_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'completed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'cancelled_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
