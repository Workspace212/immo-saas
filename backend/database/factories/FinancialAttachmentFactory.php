<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\FinancialAttachment;
use App\Models\FinancialDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FinancialAttachment> */
class FinancialAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $originalName = fake()->word().'.pdf';

        return [
            'agency_id' => Agency::factory(),
            'financial_document_id' => FinancialDocument::factory(),
            'uploaded_by' => fake()->boolean(80) ? User::factory() : null,
            'attachment_type' => fake()->randomElement(['invoice', 'receipt', 'bank_statement', 'proof', 'other']),
            'file_path' => 'financial-attachments/'.$originalName,
            'original_name' => $originalName,
            'display_order' => fake()->numberBetween(0, 10),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
