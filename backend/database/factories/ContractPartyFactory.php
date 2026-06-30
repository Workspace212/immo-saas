<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractParty;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContractParty>
 */
class ContractPartyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $partyType = fake()->randomElement(['owner', 'client', 'guarantor', 'witness', 'other']);
        $isOwner = $partyType === 'owner';
        $isClient = in_array($partyType, ['client', 'guarantor', 'witness'], true);

        return [
            'contract_id' => Contract::factory(),
            'owner_id' => $isOwner ? Owner::factory() : null,
            'client_id' => $isClient ? Client::factory() : null,
            'party_type' => $partyType,
            'role' => fake()->randomElement([
                'seller',
                'buyer',
                'landlord',
                'tenant',
                'guarantor',
                'witness',
            ]),
            'ownership_percentage' => $isOwner ? fake()->randomFloat(2, 1, 100) : null,
            'display_order' => fake()->numberBetween(0, 10),
            'signed' => fake()->boolean(),
            'signed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
