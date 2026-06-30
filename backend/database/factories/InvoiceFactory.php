<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Contract;
use App\Models\FinancialTransaction;
use App\Models\Invoice;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Invoice> */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 100000);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.1);
        $tax = ($subtotal - $discount) * 0.2;
        $total = $subtotal - $discount + $tax;
        $paid = fake()->randomFloat(2, 0, $total);

        return [
            'agency_id' => Agency::factory(),
            'client_id' => fake()->boolean(70) ? Client::factory() : null,
            'owner_id' => fake()->boolean(40) ? Owner::factory() : null,
            'contract_id' => fake()->boolean(60) ? Contract::factory() : null,
            'financial_transaction_id' => fake()->boolean(40) ? FinancialTransaction::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'invoice_number' => fake()->unique()->bothify('INV-####??'),
            'status' => fake()->randomElement(['draft', 'sent', 'partially_paid', 'paid', 'cancelled']),
            'issued_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'due_at' => fake()->dateTimeBetween('now', '+1 month'),
            'currency' => 'MAD',
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'remaining_amount' => $total - $paid,
            'pdf_path' => fake()->optional()->filePath(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
