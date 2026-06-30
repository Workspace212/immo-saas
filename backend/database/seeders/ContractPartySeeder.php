<?php

namespace Database\Seeders;

use App\Models\ContractParty;
use Illuminate\Database\Seeder;

class ContractPartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContractParty::factory()->count(10)->create();
    }
}
