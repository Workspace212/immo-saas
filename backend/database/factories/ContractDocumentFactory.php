<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\ContractDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractDocument>
 */
class ContractDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $originalName = fake()->word().'.pdf';

        return [
            'contract_id' => Contract::factory(),
            'uploaded_by' => fake()->boolean(80) ? User::factory() : null,
            'document_type' => fake()->randomElement([
                'signed_contract',
                'annex',
                'receipt',
                'payment_proof',
                'inventory',
                'termination',
                'renewal',
                'other',
            ]),
            'file_path' => 'contract-documents/'.$originalName,
            'original_name' => $originalName,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
