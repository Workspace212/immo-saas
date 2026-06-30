<?php

namespace Database\Seeders;

use App\Models\ContractDocument;
use Illuminate\Database\Seeder;

class ContractDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContractDocument::factory()->count(10)->create();
    }
}
