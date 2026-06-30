<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\RevenueCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FinancialTransaction> */
class FinancialTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'source_account_id' => fake()->boolean(80) ? FinancialAccount::factory() : null,
            'destination_account_id' => fake()->boolean(40) ? FinancialAccount::factory() : null,
            'financial_category_id' => fake()->boolean(80) ? FinancialCategory::factory() : null,
            'revenue_center_id' => fake()->boolean(60) ? RevenueCenter::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'validated_by' => fake()->boolean(60) ? User::factory() : null,
            'transaction_number' => fake()->unique()->bothify('TRX-####??'),
            'transaction_type' => fake()->randomElement(['debit', 'credit', 'transfer']),
            'transaction_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'amount' => fake()->randomFloat(2, 100, 100000),
            'currency' => 'MAD',
            'reference' => fake()->optional()->bothify('REF-####??'),
            'description' => fake()->optional()->sentence(),
            'attachment_path' => fake()->optional()->filePath(),
            'status' => fake()->randomElement(['draft', 'validated', 'cancelled']),
            'validated_at' => fake()->optional()->dateTimeBetween('-6 months', 'now'),
            'is_reconciled' => fake()->boolean(60),
            'reconciled_at' => fake()->optional()->dateTimeBetween('-6 months', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
