<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyTypes = [
            'Appartement',
            'Villa',
            'Maison',
            'Terrain',
            'Bureau',
            'Local commercial',
            'Entrepôt',
            'Ferme',
            'Projet immobilier',
            'Autre',
        ];

        foreach ($propertyTypes as $propertyType) {
            PropertyType::updateOrCreate(
                [
                    'agency_id' => null,
                    'slug' => Str::slug($propertyType),
                ],
                [
                    'name' => $propertyType,
                    'is_default' => true,
                    'is_active' => true,
                ],
            );
        }
    }
}
