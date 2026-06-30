<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientPropertyRequest;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientPropertyRequest>
 */
class ClientPropertyRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minBudget = fake()->optional()->randomFloat(2, 100000, 1000000);

        return [
            'client_id' => Client::factory(),
            'property_type_id' => fake()->boolean(80) ? PropertyType::factory() : null,
            'city' => fake()->optional()->city(),
            'sector' => fake()->optional()->streetName(),
            'min_budget' => $minBudget,
            'max_budget' => $minBudget === null ? null : $minBudget + fake()->randomFloat(2, 50000, 2000000),
            'min_living_area_m2' => fake()->optional()->randomFloat(2, 40, 200),
            'min_land_area_m2' => fake()->optional()->randomFloat(2, 60, 1000),
            'min_bedrooms' => fake()->optional()->numberBetween(1, 5),
            'min_bathrooms' => fake()->optional()->numberBetween(1, 3),
            'activity_type' => fake()->optional()->randomElement([
                'sale',
                'long_term_rent',
                'short_term_rent',
                'property_management',
                'promotion',
            ]),
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
            'created_by' => fake()->boolean(80) ? User::factory() : null,
        ];
    }
}
