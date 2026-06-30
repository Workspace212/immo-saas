<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintProvider;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintProvider>
 */
class ComplaintProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'provider_id' => Provider::factory(),
            'assigned_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'intervention_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement([
                'assigned',
                'accepted',
                'in_progress',
                'completed',
                'cancelled',
            ]),
            'amount' => fake()->optional()->randomFloat(2, 100, 10000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
