<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\ApiClient;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ApiToken> */
class ApiTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'api_client_id' => ApiClient::factory(),
            'user_id' => fake()->boolean(60) ? User::factory() : null,
            'name' => fake()->words(2, true),
            'token' => 'tok_'.Str::random(64),
            'permissions' => fake()->randomElements(['properties:read', 'contracts:write', 'clients:read'], 2),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+1 year'),
            'last_accessed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'is_active' => true,
        ];
    }
}
