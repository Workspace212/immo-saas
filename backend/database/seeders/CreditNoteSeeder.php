<?php

namespace Database\Seeders;

use App\Models\CreditNote;
use Illuminate\Database\Seeder;

class CreditNoteSeeder extends Seeder
{
    public function run(): void
    {
        CreditNote::factory()->count(10)->create();
    }
}
