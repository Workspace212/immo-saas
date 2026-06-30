<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyAvailability;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyAvailability>
 */
class PropertyAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('-1 month', '+1 month');

        return [
            'property_id' => Property::factory(),
            'rental_unit_id' => fake()->boolean(70) ? RentalUnit::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'availability_type' => fake()->randomElement([
                'available',
                'reserved',
                'occupied',
                'maintenance',
                'blocked',
                'visit_scheduled',
            ]),
            'start_at' => $startAt,
            'end_at' => fake()->dateTimeBetween($startAt, '+2 months'),
            'title' => fake()->optional()->sentence(3),
            'notes' => fake()->optional()->sentence(),
            'status' => fake()->randomElement(['active', 'cancelled', 'completed']),
        ];
    }
}
