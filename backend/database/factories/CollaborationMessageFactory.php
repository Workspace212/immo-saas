<?php

namespace Database\Factories;

use App\Models\Collaboration;
use App\Models\CollaborationMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollaborationMessage> */
class CollaborationMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'collaboration_id' => Collaboration::factory(),
            'sender_id' => fake()->boolean(90) ? User::factory() : null,
            'message' => fake()->paragraph(),
            'is_internal' => fake()->boolean(80),
            'read_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
