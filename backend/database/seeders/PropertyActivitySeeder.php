<?php

namespace Database\Seeders;

use App\Models\PropertyActivity;
use Illuminate\Database\Seeder;

class PropertyActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PropertyActivity::factory()->count(10)->create();
    }
}
