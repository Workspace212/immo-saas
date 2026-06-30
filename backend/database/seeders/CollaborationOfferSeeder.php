<?php

namespace Database\Seeders;

use App\Models\CollaborationOffer;
use Illuminate\Database\Seeder;

class CollaborationOfferSeeder extends Seeder
{
    public function run(): void
    {
        CollaborationOffer::factory()->count(10)->create();
    }
}
