<?php

namespace Database\Seeders;

use App\Models\OwnerDocument;
use Illuminate\Database\Seeder;

class OwnerDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OwnerDocument::factory()->count(10)->create();
    }
}
