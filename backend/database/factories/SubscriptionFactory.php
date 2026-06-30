<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'agency_id' => Agency::factory(),
            'subscription_plan_id' => SubscriptionPlan::factory(),
            'start_date' => $startDate,
            'end_date' => fake()->optional()->dateTimeBetween($startDate, '+1 year'),
            'status' => 'active',
            'amount' => fake()->randomFloat(2, 9, 999),
            'payment_status' => 'pending',
        ];
    }
}
