<?php

namespace Database\Seeders;

use App\Models\CustomFieldValue;
use Illuminate\Database\Seeder;

class CustomFieldValueSeeder extends Seeder
{
    public function run(): void
    {
        CustomFieldValue::factory()->count(10)->create();
    }
}
