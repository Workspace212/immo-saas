<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Collaboration;
use App\Models\CollaborationVisit;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollaborationVisit> */
class CollaborationVisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collaboration_id' => Collaboration::factory(),
            'property_id' => Property::factory(),
            'client_id' => fake()->boolean(70) ? Client::factory() : null,
            'scheduled_by' => fake()->boolean(80) ? User::factory() : null,
            'visit_date' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement(['scheduled', 'completed', 'cancelled', 'no_show']),
            'feedback' => fake()->optional()->paragraph(),
            'result' => fake()->optional()->randomElement(['interested', 'not_interested', 'offer_made', 'follow_up', 'other']),
        ];
    }
}
