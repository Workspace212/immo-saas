<?php

namespace Database\Seeders;

use App\Models\ComplaintType;
use Illuminate\Database\Seeder;

class ComplaintTypeSeeder extends Seeder
{
    public function run(): void
    {
        ComplaintType::factory()->count(5)->create();
    }
}
