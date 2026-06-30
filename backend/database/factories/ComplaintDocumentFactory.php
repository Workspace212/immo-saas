<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintDocument>
 */
class ComplaintDocumentFactory extends Factory
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
            'complaint_id' => Complaint::factory(),
            'uploaded_by' => fake()->boolean(80) ? User::factory() : null,
            'document_type' => fake()->randomElement([
                'before_photo',
                'after_photo',
                'invoice',
                'quote',
                'intervention_report',
                'other',
            ]),
            'file_path' => 'complaint-documents/'.$originalName,
            'original_name' => $originalName,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
