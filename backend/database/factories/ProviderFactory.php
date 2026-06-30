<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
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
            'provider_type' => fake()->randomElement([
                'plumbing',
                'electricity',
                'cleaning',
                'maintenance',
                'security',
                'other',
            ]),
            'company_name' => fake()->optional()->company(),
            'contact_name' => fake()->optional()->name(),
            'phone' => fake()->optional()->phoneNumber(),
            'whatsapp' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->companyEmail(),
            'address' => fake()->optional()->address(),
            'city' => fake()->optional()->city(),
            'ice' => fake()->optional()->numerify('###############'),
            'rc' => fake()->optional()->numerify('######'),
            'if_number' => fake()->optional()->numerify('########'),
            'patente' => fake()->optional()->numerify('########'),
            'rating' => fake()->optional()->randomFloat(2, 1, 5),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
