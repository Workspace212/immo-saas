<?php

namespace Database\Seeders;

use App\Models\PropertyMedia;
use Illuminate\Database\Seeder;

class PropertyMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyMedia::factory()->count(10)->create();
    }
}
