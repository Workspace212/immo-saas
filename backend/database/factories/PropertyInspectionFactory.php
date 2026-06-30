<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Property;
use App\Models\PropertyInspection;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyInspection>
 */
class PropertyInspectionFactory extends Factory
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
            'rental_unit_id' => fake()->boolean(70) ? RentalUnit::factory() : null,
            'property_id' => Property::factory(),
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'validated_by' => fake()->boolean(50) ? User::factory() : null,
            'inspection_number' => fake()->unique()->bothify('INS-####??'),
            'inspection_type' => fake()->randomElement(['entry', 'exit', 'intermediate']),
            'inspection_date' => fake()->optional()->dateTimeBetween('-1 month', '+1 month'),
            'status' => fake()->randomElement(['draft', 'validated', 'cancelled']),
            'electricity_meter' => fake()->optional()->randomFloat(2, 0, 999999),
            'water_meter' => fake()->optional()->randomFloat(2, 0, 999999),
            'gas_meter' => fake()->optional()->randomFloat(2, 0, 999999),
            'keys_given' => fake()->numberBetween(0, 10),
            'remotes_given' => fake()->numberBetween(0, 5),
            'access_cards_given' => fake()->numberBetween(0, 5),
            'global_condition' => fake()->optional()->randomElement(['excellent', 'good', 'average', 'poor']),
            'tenant_comments' => fake()->optional()->paragraph(),
            'agency_comments' => fake()->optional()->paragraph(),
            'validation_date' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
