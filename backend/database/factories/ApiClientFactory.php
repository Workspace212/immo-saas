<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ApiClient> */
class ApiClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'name' => fake()->company().' App',
            'client_key' => 'cli_'.Str::random(32),
            'secret' => Str::random(64),
            'permissions' => fake()->randomElements(['properties:read', 'contracts:write', 'clients:read', 'webhooks:manage'], 2),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
