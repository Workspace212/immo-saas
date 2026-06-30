<?php

namespace Database\Seeders;

use App\Models\RentalUnit;
use Illuminate\Database\Seeder;

class RentalUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RentalUnit::factory()->count(10)->create();
    }
}
