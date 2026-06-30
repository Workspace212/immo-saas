<?php

namespace Database\Factories;

use App\Models\Collaboration;
use App\Models\CollaborationDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CollaborationDocument> */
class CollaborationDocumentFactory extends Factory
{
    public function definition(): array
    {
        $originalName = fake()->word().'.pdf';

        return [
            'collaboration_id' => Collaboration::factory(),
            'uploaded_by' => fake()->boolean(80) ? User::factory() : null,
            'document_type' => fake()->randomElement([
                'client_document',
                'property_document',
                'offer_document',
                'contract_document',
                'other',
            ]),
            'file_path' => 'collaboration-documents/'.$originalName,
            'original_name' => $originalName,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
