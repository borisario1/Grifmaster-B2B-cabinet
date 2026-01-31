<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\Brand;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Общий прайс-лист (закреплен)
        Resource::create([
            'title' => 'Прайс-лист GRIFMASTER 2025',
            'type' => 'price_list',
            'file_path' => 'resources/price_grifmaster.txt',
            'brand_id' => null,
            'is_active' => true,
            'is_pinned' => true,
            'require_confirmation' => false,
        ]);
        
        Resource::create([
            'title' => 'Общий прайс (веб-версия)',
            'type' => 'price_list',
            'file_path' => '',
            'external_link' => 'https://grifmaster.ru/web-price',
            'brand_id' => null,
            'is_active' => true,
            'is_pinned' => true,
            'require_confirmation' => false,
        ]);
        
        // Прайс Kerama Marazzi с подтверждением
        $keramaBrand = Brand::where('slug', 'kerama-marazzi')->first();
        if ($keramaBrand) {
            Resource::create([
                'title' => 'Прайс-лист Kerama Marazzi',
                'type' => 'price_list',
                'file_path' => 'resources/certificate_kerama_2025.txt',
                'brand_id' => $keramaBrand->id,
                'is_active' => true,
                'is_pinned' => false,
                'require_confirmation' => true,
                'confirmation_text' => '<p><strong>Внимание!</strong> Данный прайс-лист содержит конфиденциальную информацию.</p><p>Используйте его исключительно для внутренних целей.</p>',
                'confirm_btn_text' => 'Принимаю условия и скачиваю',
            ]);
        }
        
        // Каталог Paini
        $painiBrand = Brand::where('slug', 'paini')->first();
        if ($painiBrand) {
            Resource::create([
                'title' => 'Каталог Paini 2025',
                'type' => 'catalog',
                'file_path' => 'resources/catalog_paini_2025.txt',
                'brand_id' => $painiBrand->id,
                'is_active' => true,
                'is_pinned' => false,
                'require_confirmation' => false,
            ]);
        }
        
        // Сертификаты
        Resource::create([
            'title' => 'Сертификат ISO 9001',
            'type' => 'certificate',
            'file_path' => 'resources/certificate_iso_9001.pdf',
            'brand_id' => null,
            'is_active' => true,
            'is_pinned' => false,
            'require_confirmation' => false,
        ]);
        
        // 3D модели
        $urbatecBrand = Brand::where('slug', 'urbatec')->first();
        if ($urbatecBrand) {
            Resource::create([
                'title' => '3D модели Urbatec',
                'type' => '3d_model',
                'file_path' => 'resources/3d_urbatec.zip',
                'brand_id' => $urbatecBrand->id,
                'is_active' => true,
                'is_pinned' => false,
                'require_confirmation' => false,
            ]);
        }
        
        // Дополнительные тестовые данные
        $prestoBrand = Brand::where('slug', 'presto')->first();
        if ($prestoBrand) {
            Resource::create([
                'title' => 'Каталог Presto',
                'type' => 'catalog',
                'file_path' => 'resources/catalog_presto.pdf',
                'brand_id' => $prestoBrand->id,
                'is_active' => true,
                'is_pinned' => false,
                'require_confirmation' => false,
            ]);
        }
        
        $axaBrand = Brand::where('slug', 'axa')->first();
        if ($axaBrand) {
            Resource::create([
                'title' => 'Сертификат AXA',
                'type' => 'certificate',
                'file_path' => 'resources/certificate_axa.pdf',
                'brand_id' => $axaBrand->id,
                'is_active' => true,
                'is_pinned' => false,
                'require_confirmation' => false,
            ]);
        }
    }
}
