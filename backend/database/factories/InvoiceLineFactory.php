<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InvoiceLine> */
class InvoiceLineFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 5);
        $unitPrice = fake()->randomFloat(2, 100, 10000);
        $discount = fake()->randomFloat(2, 0, 500);
        $taxRate = fake()->randomElement([0, 10, 20]);
        $base = max(0, ($quantity * $unitPrice) - $discount);
        $tax = $base * $taxRate / 100;

        return [
            'agency_id' => Agency::factory(),
            'invoice_id' => Invoice::factory(),
            'description' => fake()->sentence(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discount,
            'tax_rate' => $taxRate,
            'tax_amount' => $tax,
            'line_total' => $base + $tax,
            'display_order' => fake()->numberBetween(0, 10),
        ];
    }
}
