<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\FinancialAccount;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvoicePayment> */
class InvoicePaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'invoice_id' => Invoice::factory(),
            'financial_account_id' => fake()->boolean(80) ? FinancialAccount::factory() : null,
            'received_by' => fake()->boolean(80) ? User::factory() : null,
            'payment_number' => fake()->unique()->bothify('IPAY-####??'),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'amount' => fake()->randomFloat(2, 100, 100000),
            'currency' => 'MAD',
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'check', 'card', 'mobile_payment']),
            'reference' => fake()->optional()->bothify('REF-####??'),
            'receipt_number' => fake()->optional()->bothify('REC-####??'),
            'receipt_path' => fake()->optional()->filePath(),
            'status' => fake()->randomElement(['completed', 'cancelled', 'refunded']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
