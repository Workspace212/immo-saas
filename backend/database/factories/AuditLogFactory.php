<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AuditLog> */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => fake()->boolean(80) ? Agency::factory() : null,
            'user_id' => fake()->boolean(80) ? User::factory() : null,
            'action_type' => fake()->randomElement(['created', 'updated', 'deleted', 'restored']),
            'module' => fake()->randomElement(['properties', 'contracts', 'finance', 'users']),
            'auditable_type' => fake()->optional()->randomElement(['Property', 'Contract', 'Invoice']),
            'auditable_id' => fake()->optional()->numberBetween(1, 1000),
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => 'active'],
            'ip_address' => fake()->ipv4(),
            'browser' => fake()->userAgent(),
            'device' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'performed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
