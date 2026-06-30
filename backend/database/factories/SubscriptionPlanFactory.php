<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'price_monthly' => fake()->randomFloat(2, 9, 999),
            'max_users' => fake()->numberBetween(1, 100),
            'max_properties' => fake()->numberBetween(10, 1000),
            'features_json' => fake()->optional()->randomElements([
                'analytics',
                'custom_branding',
                'email_support',
                'property_exports',
            ], fake()->numberBetween(1, 4)),
            'is_active' => true,
        ];
    }
}
