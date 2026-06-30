<?php

namespace Database\Seeders;

use App\Models\PropertyDocument;
use Illuminate\Database\Seeder;

class PropertyDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyDocument::factory()->count(10)->create();
    }
}
