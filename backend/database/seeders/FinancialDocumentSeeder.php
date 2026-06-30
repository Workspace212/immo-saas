<?php

namespace Database\Seeders;

use App\Models\FinancialDocument;
use Illuminate\Database\Seeder;

class FinancialDocumentSeeder extends Seeder
{
    public function run(): void
    {
        FinancialDocument::factory()->count(10)->create();
    }
}
