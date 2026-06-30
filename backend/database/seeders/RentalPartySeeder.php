<?php

namespace Database\Seeders;

use App\Models\RentalParty;
use Illuminate\Database\Seeder;

class RentalPartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RentalParty::factory()->count(10)->create();
    }
}
