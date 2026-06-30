<?php

namespace Database\Seeders;

use App\Models\PropertyAvailability;
use Illuminate\Database\Seeder;

class PropertyAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyAvailability::factory()->count(10)->create();
    }
}
