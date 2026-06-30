<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\ApiClient;
use App\Models\ApiWebhook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ApiWebhook> */
class ApiWebhookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'api_client_id' => fake()->boolean(70) ? ApiClient::factory() : null,
            'name' => fake()->words(3, true),
            'url' => fake()->url(),
            'event' => fake()->randomElement(['property.created', 'contract.signed', 'invoice.paid', 'complaint.updated']),
            'http_method' => fake()->randomElement(['POST', 'PUT']),
            'secret' => Str::random(40),
            'headers' => ['Content-Type' => 'application/json'],
            'is_active' => true,
        ];
    }
}
