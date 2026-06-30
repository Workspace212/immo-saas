<?php

namespace Database\Seeders;

use App\Models\SearchSuggestion;
use Illuminate\Database\Seeder;

class SearchSuggestionSeeder extends Seeder
{
    public function run(): void
    {
        SearchSuggestion::factory()->count(10)->create();
    }
}
