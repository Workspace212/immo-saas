<?php

namespace Database\Seeders;

use App\Models\SearchIndex;
use Illuminate\Database\Seeder;

class SearchIndexSeeder extends Seeder
{
    public function run(): void
    {
        SearchIndex::factory()->count(10)->create();
    }
}
