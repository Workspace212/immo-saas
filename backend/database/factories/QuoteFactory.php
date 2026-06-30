<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Quote> */
class QuoteFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 100000);
        $tax = $subtotal * 0.2;

        return [
            'agency_id' => Agency::factory(),
            'client_id' => fake()->boolean(80) ? Client::factory() : null,
            'invoice_id' => fake()->boolean(30) ? Invoice::factory() : null,
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'quote_number' => fake()->unique()->bothify('QUO-####??'),
            'title' => fake()->sentence(4),
            'status' => fake()->randomElement(['draft', 'sent', 'accepted', 'rejected', 'converted', 'expired']),
            'issued_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'valid_until' => fake()->dateTimeBetween('now', '+2 months'),
            'currency' => 'MAD',
            'subtotal_amount' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $tax,
            'total_amount' => $subtotal + $tax,
            'lines' => [
                ['description' => fake()->sentence(), 'quantity' => 1, 'unit_price' => $subtotal],
            ],
            'converted_at' => fake()->optional()->dateTimeBetween('-1 week', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
