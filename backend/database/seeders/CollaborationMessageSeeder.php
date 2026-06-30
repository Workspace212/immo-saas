<?php

namespace Database\Seeders;

use App\Models\CollaborationMessage;
use Illuminate\Database\Seeder;

class CollaborationMessageSeeder extends Seeder
{
    public function run(): void
    {
        CollaborationMessage::factory()->count(10)->create();
    }
}
