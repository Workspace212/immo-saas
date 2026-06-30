<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CreditNote> */
class CreditNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'agency_id' => Agency::factory(),
            'invoice_id' => Invoice::factory(),
            'created_by' => fake()->boolean(80) ? User::factory() : null,
            'credit_note_number' => fake()->unique()->bothify('CN-####??'),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'currency' => 'MAD',
            'reason' => fake()->sentence(),
            'status' => fake()->randomElement(['draft', 'issued', 'cancelled']),
            'issued_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'pdf_path' => fake()->optional()->filePath(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
