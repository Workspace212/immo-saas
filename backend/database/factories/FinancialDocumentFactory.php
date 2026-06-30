<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\FinancialDocument;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FinancialDocument> */
class FinancialDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'financial_transaction_id' => fake()->boolean(50) ? FinancialTransaction::factory() : null,
            'expense_id' => fake()->boolean(50) ? Expense::factory() : null,
            'contract_id' => fake()->boolean(40) ? Contract::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'document_number' => fake()->unique()->bothify('FDOC-####??'),
            'document_type' => fake()->randomElement(['invoice', 'receipt', 'bank_statement', 'supporting_document']),
            'title' => fake()->sentence(4),
            'document_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'amount' => fake()->optional()->randomFloat(2, 100, 100000),
            'currency' => 'MAD',
            'status' => fake()->randomElement(['draft', 'validated', 'archived']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
