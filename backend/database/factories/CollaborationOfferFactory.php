<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Collaboration;
use App\Models\CollaborationOffer;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollaborationOffer> */
class CollaborationOfferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collaboration_id' => Collaboration::factory(),
            'property_id' => Property::factory(),
            'client_id' => fake()->boolean(70) ? Client::factory() : null,
            'submitted_by' => fake()->boolean(80) ? User::factory() : null,
            'offer_number' => fake()->unique()->bothify('OFR-####??'),
            'amount' => fake()->randomFloat(2, 100000, 5000000),
            'currency' => 'MAD',
            'status' => fake()->randomElement(['draft', 'submitted', 'accepted', 'rejected', 'cancelled']),
            'submitted_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'accepted_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'rejected_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
