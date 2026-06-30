<?php

namespace Database\Seeders;

use App\Models\LoginHistory;
use Illuminate\Database\Seeder;

class LoginHistorySeeder extends Seeder
{
    public function run(): void
    {
        LoginHistory::factory()->count(10)->create();
    }
}
