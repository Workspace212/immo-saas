<?php

namespace Database\Seeders;

use App\Models\PropertyOwner;
use Illuminate\Database\Seeder;

class PropertyOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyOwner::factory()->count(10)->create();
    }
}
