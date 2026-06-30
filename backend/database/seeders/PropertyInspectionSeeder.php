<?php

namespace Database\Seeders;

use App\Models\PropertyInspection;
use Illuminate\Database\Seeder;

class PropertyInspectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyInspection::factory()->count(10)->create();
    }
}
