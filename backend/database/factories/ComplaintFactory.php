<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Complaint;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'property_id' => Property::factory(),
            'client_id' => fake()->boolean(70) ? Client::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'assigned_to' => fake()->boolean(70) ? User::factory() : null,
            'complaint_number' => fake()->unique()->bothify('CMP-####??'),
            'complaint_type' => fake()->randomElement([
                'maintenance',
                'payment',
                'noise',
                'cleanliness',
                'other',
            ]),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'priority' => fake()->randomElement(['low', 'normal', 'urgent', 'critical']),
            'status' => fake()->randomElement([
                'new',
                'seen',
                'assigned',
                'in_progress',
                'waiting_provider',
                'resolved',
                'closed',
            ]),
            'provider_name' => fake()->optional()->company(),
            'provider_phone' => fake()->optional()->phoneNumber(),
            'intervention_date' => fake()->optional()->dateTimeBetween('-1 month', '+1 month'),
            'amount_paid' => fake()->optional()->randomFloat(2, 100, 10000),
            'paid_by' => fake()->optional()->randomElement(['agency', 'owner', 'client']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
