<?php

namespace Database\Seeders;

use App\Models\ComplaintDocument;
use Illuminate\Database\Seeder;

class ComplaintDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ComplaintDocument::factory()->count(10)->create();
    }
}
