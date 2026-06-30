<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Complaint;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\FinancialAccount;
use App\Models\FinancialCategory;
use App\Models\Property;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Expense> */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        $amountHt = fake()->randomFloat(2, 100, 50000);
        $taxRate = fake()->randomElement([0, 10, 20]);
        $taxAmount = $amountHt * $taxRate / 100;

        return [
            'agency_id' => Agency::factory(),
            'financial_category_id' => fake()->boolean(80) ? FinancialCategory::factory() : null,
            'financial_account_id' => fake()->boolean(80) ? FinancialAccount::factory() : null,
            'provider_id' => fake()->boolean(60) ? Provider::factory() : null,
            'property_id' => fake()->boolean(60) ? Property::factory() : null,
            'contract_id' => fake()->boolean(40) ? Contract::factory() : null,
            'complaint_id' => fake()->boolean(40) ? Complaint::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'validated_by' => fake()->boolean(60) ? User::factory() : null,
            'expense_number' => fake()->unique()->bothify('EXP-####??'),
            'expense_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'amount_ht' => $amountHt,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'amount_ttc' => $amountHt + $taxAmount,
            'currency' => 'MAD',
            'payment_method' => fake()->optional()->randomElement(['cash', 'bank_transfer', 'check', 'card']),
            'status' => fake()->randomElement(['draft', 'validated', 'paid', 'cancelled']),
            'validated_at' => fake()->optional()->dateTimeBetween('-6 months', 'now'),
            'attachment_path' => fake()->optional()->filePath(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
