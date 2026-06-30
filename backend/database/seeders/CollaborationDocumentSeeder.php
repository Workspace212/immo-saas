<?php

namespace Database\Seeders;

use App\Models\CollaborationDocument;
use Illuminate\Database\Seeder;

class CollaborationDocumentSeeder extends Seeder
{
    public function run(): void
    {
        CollaborationDocument::factory()->count(10)->create();
    }
}
