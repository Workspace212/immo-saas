<?php

namespace Database\Factories;

use App\Models\PropertyInspection;
use App\Models\PropertyInspectionSignature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyInspectionSignature>
 */
class PropertyInspectionSignatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $signed = fake()->boolean(70);
        $refused = ! $signed && fake()->boolean(30);

        return [
            'property_inspection_id' => PropertyInspection::factory(),
            'signer_type' => fake()->randomElement(['agency', 'tenant', 'owner', 'guarantor', 'other']),
            'signer_name' => fake()->name(),
            'signer_role' => fake()->optional()->jobTitle(),
            'signature_path' => $signed ? 'inspection-signatures/'.fake()->uuid().'.png' : null,
            'signed' => $signed,
            'signed_at' => $signed ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'refused' => $refused,
            'refusal_reason' => $refused ? fake()->sentence() : null,
        ];
    }
}
