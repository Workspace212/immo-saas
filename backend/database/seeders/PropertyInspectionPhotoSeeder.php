<?php

namespace Database\Seeders;

use App\Models\PropertyInspectionPhoto;
use Illuminate\Database\Seeder;

class PropertyInspectionPhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyInspectionPhoto::factory()->count(10)->create();
    }
}
