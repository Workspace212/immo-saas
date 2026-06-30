<?php

namespace Database\Seeders;

use App\Models\PropertyInspectionSignature;
use Illuminate\Database\Seeder;

class PropertyInspectionSignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyInspectionSignature::factory()->count(10)->create();
    }
}
