<?php

namespace Database\Factories;

use App\Models\Owner;
use App\Models\OwnerDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerDocument>
 */
class OwnerDocumentFactory extends Factory
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
            'owner_id' => Owner::factory(),
            'uploaded_by' => fake()->boolean(80) ? User::factory() : null,
            'document_type' => fake()->randomElement([
                'identity',
                'ownership',
                'company',
                'bank',
                'other',
            ]),
            'file_path' => 'owner-documents/'.$originalName,
            'original_name' => $originalName,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
