<?php

namespace Database\Seeders;

use App\Models\ComplaintProvider;
use Illuminate\Database\Seeder;

class ComplaintProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ComplaintProvider::factory()->count(10)->create();
    }
}
