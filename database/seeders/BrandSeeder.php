<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Urbatec',
                'slug' => 'urbatec',
                'logo_path' => '/img/brands_logo/urbatec.png',
                'origin_country' => 'Испания',
                'production_country' => 'Китай',
                'priority' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Paini',
                'slug' => 'paini',
                'logo_path' => '/img/brands_logo/paini.png',
                'origin_country' => 'Италия',
                'production_country' => 'Китай',
                'priority' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Presto',
                'slug' => 'presto',
                'logo_path' => '/img/brands_logo/presto.png',
                'origin_country' => null,
                'production_country' => null,
                'priority' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'AXA',
                'slug' => 'axa',
                'logo_path' => '/img/brands_logo/axa.png',
                'origin_country' => null,
                'production_country' => null,
                'priority' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Sanitana',
                'slug' => 'sanitana',
                'logo_path' => '/img/brands_logo/sanitana.png',
                'origin_country' => null,
                'production_country' => null,
                'priority' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Kerama Marazzi',
                'slug' => 'kerama-marazzi',
                'logo_path' => '/img/brands_logo/kerama.png',
                'origin_country' => null,
                'production_country' => null,
                'priority' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'SDR',
                'slug' => 'sdr',
                'logo_path' => '/img/brands_logo/sdr.png',
                'origin_country' => null,
                'production_country' => null,
                'priority' => 1,
                'is_active' => true,
            ],
        ];
        
        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
