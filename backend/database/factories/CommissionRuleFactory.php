<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\CommissionRule;
use App\Models\ContractType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CommissionRule> */
class CommissionRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'agent_id' => fake()->boolean(60) ? User::factory() : null,
            'contract_type_id' => fake()->boolean(70) ? ContractType::factory() : null,
            'name' => fake()->unique()->words(3, true),
            'team_name' => fake()->optional()->company(),
            'percentage' => fake()->optional()->randomFloat(2, 1, 10),
            'fixed_amount' => fake()->optional()->randomFloat(2, 1000, 20000),
            'currency' => 'MAD',
            'priority' => fake()->numberBetween(0, 10),
            'is_active' => true,
            'conditions' => ['min_amount' => fake()->numberBetween(10000, 100000)],
        ];
    }
}
