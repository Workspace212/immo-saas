<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['individual', 'company']);
        $isCompany = $type === 'company';

        return [
            'agency_id' => Agency::factory(),
            'type' => $type,
            'full_name' => $isCompany ? null : fake()->name(),
            'cin_passport' => $isCompany ? null : fake()->optional()->bothify('??######'),
            'phone' => fake()->optional()->phoneNumber(),
            'whatsapp' => fake()->optional()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'address' => fake()->optional()->address(),
            'city' => fake()->optional()->city(),
            'country' => 'Morocco',
            'company_name' => $isCompany ? fake()->company() : null,
            'ice' => $isCompany ? fake()->optional()->numerify('###############') : null,
            'rc' => $isCompany ? fake()->optional()->numerify('######') : null,
            'if_number' => $isCompany ? fake()->optional()->numerify('########') : null,
            'patente' => $isCompany ? fake()->optional()->numerify('########') : null,
            'representative_name' => $isCompany ? fake()->name() : null,
            'notes' => fake()->optional()->sentence(),
            'status' => 'active',
        ];
    }
}
